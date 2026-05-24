<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Warehouse;
use App\Models\Store;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // ---- Фильтры ----
        $dateFrom = $request->date_from
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $dateTo = $request->date_to
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        $warehouseId = $request->warehouse_id;
        $storeId     = $request->store_id;

        // =====================================================
        // 1. ТОП ТОВАРОВ ПО ДВИЖЕНИЮ (все направления)
        // =====================================================
        $topProductsRaw = DB::table('stock_movements')
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->select(
                'products.name',
                'stock_movements.product_id',
                DB::raw('SUM(stock_movements.quantity) as movement')
            )
            ->whereBetween('stock_movements.created_at', [$dateFrom, $dateTo])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->groupBy('stock_movements.product_id', 'products.name')
            ->orderByDesc('movement')
            ->limit(10)
            ->get();

        $topProducts = [
            'labels' => $topProductsRaw->pluck('name'),
            'data'   => $topProductsRaw->pluck('movement'),
        ];

        // =====================================================
        // 2. ЗАПАСЫ ПО СКЛАДАМ (текущие остатки)
        // =====================================================
        $stockByWarehouseRaw = DB::table('stock_movements')
            ->join('warehouses', 'warehouses.id', '=', 'stock_movements.warehouse_id')
            ->select(
                'warehouses.id',
                'warehouses.name',
                DB::raw('SUM(stock_movements.quantity) as qty')
            )
            ->whereNotNull('stock_movements.warehouse_id')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get();

        $stockByWarehouse = [
            'labels' => $stockByWarehouseRaw->pluck('name'),
            'data'   => $stockByWarehouseRaw->pluck('qty'),
        ];

        // =====================================================
        // 3. ЗАПАСЫ ПО МАГАЗИНАМ (stores)
        // =====================================================
        $stockByStoreRaw = DB::table('stock_movements')
            ->join('stores', 'stores.id', '=', 'stock_movements.store_id')
            ->select(
                'stores.id',
                'stores.name',
                DB::raw('SUM(stock_movements.quantity) as qty')
            )
            ->whereNotNull('stock_movements.store_id')
            ->groupBy('stores.id', 'stores.name')
            ->get();

        $stockByStore = [
            'labels' => $stockByStoreRaw->pluck('name'),
            'data'   => $stockByStoreRaw->pluck('qty'),
        ];

        // =====================================================
        // 4. ИСТОРИЯ ДВИЖЕНИЙ ПО ДНЯМ (общая)
        // =====================================================
        $movementsDaily = DB::table('stock_movements')
            ->select(
                DB::raw('DATE(created_at) as d'),
                DB::raw('SUM(quantity) as qty')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('qty', 'd');

        $history = [
            'labels' => [],
            'qty'    => [],
        ];

        $cursor = $dateFrom->copy();
        while ($cursor <= $dateTo) {
            $d = $cursor->toDateString();
            $history['labels'][] = $d;
            $history['qty'][]    = (float)($movementsDaily[$d] ?? 0);
            $cursor->addDay();
        }

        // =====================================================
        // 5. СРЕДНЯЯ ЗАКУПОЧНАЯ ЦЕНА + СЕБЕСТОИМОСТЬ
        //    (direction = 'in', unit_price не NULL)
        // =====================================================
        $avgPriceRaw = DB::table('stock_movements')
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->select(
                'products.name as product_name',
                'stock_movements.product_id',
                DB::raw('SUM(stock_movements.quantity) as total_qty'),
                DB::raw('SUM(stock_movements.quantity * stock_movements.unit_price) as total_cost')
            )
            ->where('stock_movements.direction', 'in')
            ->whereNotNull('stock_movements.unit_price')
            ->whereBetween('stock_movements.created_at', [$dateFrom, $dateTo])
            ->when($warehouseId, fn($q) => $q->where('stock_movements.warehouse_id', $warehouseId))
            ->when($storeId, fn($q) => $q->where('stock_movements.store_id', $storeId))
            ->groupBy('stock_movements.product_id', 'products.name')
            ->get();

        $avgPriceTable = $avgPriceRaw->map(function ($row) {
            $avg = $row->total_qty > 0 ? $row->total_cost / $row->total_qty : 0;
            return [
                'product'    => $row->product_name,
                'qty'        => (float)$row->total_qty,
                'cost'       => (float)$row->total_cost,
                'avg_price'  => round($avg, 2),
            ];
        })->sortByDesc('cost')->values();

        // =====================================================
        // 6. ТОП ТОВАРОВ ПО РАСХОДУ (direction = 'out')
        // =====================================================
        $topOutRaw = DB::table('stock_movements')
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->select(
                'products.name',
                DB::raw('SUM(ABS(stock_movements.quantity)) as total_out')
            )
            ->where('stock_movements.direction', 'out')
            ->whereBetween('stock_movements.created_at', [$dateFrom, $dateTo])
            ->when($warehouseId, fn($q) => $q->where('stock_movements.warehouse_id', $warehouseId))
            ->when($storeId, fn($q) => $q->where('stock_movements.store_id', $storeId))
            ->groupBy('products.name')
            ->orderByDesc('total_out')
            ->limit(10)
            ->get();

        $topOut = [
            'labels' => $topOutRaw->pluck('name'),
            'data'   => $topOutRaw->pluck('total_out'),
        ];

        // =====================================================
        // 7. FIFO / АНАЛИТИКА ПО ПАРТИЯМ
        //    Берём партии с положительным остатком
        // =====================================================
        $batchRaw = DB::table('stock_movements')
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->select(
                'products.name as product_name',
                'stock_movements.product_id',
                'stock_movements.batch',
                'stock_movements.expiry_date',
                DB::raw('SUM(stock_movements.quantity) as qty'),
                DB::raw('SUM(stock_movements.quantity * stock_movements.unit_price) as total_cost')
            )
            ->whereNotNull('stock_movements.batch')
            ->groupBy(
                'stock_movements.product_id',
                'products.name',
                'stock_movements.batch',
                'stock_movements.expiry_date'
            )
            ->having('qty', '>', 0)
            ->orderBy('stock_movements.expiry_date')
            ->get();

        $batchAnalytics = $batchRaw->map(function ($row) {
            return [
                'product' => $row->product_name,
                'batch'   => $row->batch,
                'qty'     => (float)$row->qty,
                'expiry'  => $row->expiry_date,
                'cost'    => (float)$row->total_cost,
            ];
        });

        // Для графика FIFO берём, например, TOP 20 ближайших партий по сроку
        $fifoSource = $batchAnalytics->sortBy('expiry')->take(20)->values();

        $fifoChart = [
            'labels' => $fifoSource->map(fn($r) => $r['product'].' / '.$r['batch']),
            'data'   => $fifoSource->pluck('qty'),
            'expiry' => $fifoSource->pluck('expiry'),
        ];

        // =====================================================
        // 8. ДАННЫЕ ДЛЯ ФИЛЬТРОВ
        // =====================================================
        $warehouses = Warehouse::orderBy('name')->get();
        $stores     = Store::orderBy('name')->get();

        return view('analytics.index', [
            'dateFrom'         => $dateFrom->toDateString(),
            'dateTo'           => $dateTo->toDateString(),
            'warehouseId'      => $warehouseId,
            'storeId'          => $storeId,
            'warehouses'       => $warehouses,
            'stores'           => $stores,
            'topProducts'      => $topProducts,
            'stockByWarehouse' => $stockByWarehouse,
            'stockByStore'     => $stockByStore,
            'history'          => $history,
            'avgPriceTable'    => $avgPriceTable,
            'topOut'           => $topOut,
            'fifoChart'        => $fifoChart,
            'batchAnalytics'   => $batchAnalytics,
        ]);
    }
}
