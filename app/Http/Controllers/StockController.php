<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
class StockController extends Controller
{
    /**
     * API: остатки по складу / магазину для Select2
     *
     * GET /api/stock/by-location?location_type=warehouse&location_id=1&query=сахар
     */
    public function byLocation(Request $request)
    {
        $request->validate([
            'location_type' => 'required|in:warehouse,store',
            'location_id'   => 'required|integer',
            'query'         => 'nullable|string',
        ]);

        $locationType = $request->location_type;
        $locationId   = $request->location_id;
        $search = $request->query('query');


        $query = StockMovement::query()
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->select(
                'stock_movements.product_id',
                'products.name',
                'products.barcode',
                'products.unit',
                DB::raw('SUM(CASE WHEN direction = "in" THEN quantity ELSE -quantity END) AS qty')
            );

        if ($locationType === 'warehouse') {
            $query->where('stock_movements.warehouse_id', $locationId);
        } else {
            $query->where('stock_movements.store_id', $locationId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }

        $query->groupBy(
            'stock_movements.product_id',
            'products.name',
            'products.barcode',
            'products.unit'
        )->having('qty', '>', 0);

        $rows = $query->get();

        // Для автоподстановки цены: ищем последнюю приходную цену
        $results = [];

        foreach ($rows as $row) {
            $lastPriceQuery = StockMovement::where('product_id', $row->product_id)
                ->where('direction', 'in');

            if ($locationType === 'warehouse') {
                $lastPriceQuery->where('warehouse_id', $locationId);
            } else {
                $lastPriceQuery->where('store_id', $locationId);
            }

            $lastPrice = $lastPriceQuery
                ->orderBy('created_at', 'desc')
                ->value('unit_price');

            $results[] = [
                'id'        => $row->product_id,
                'text'      => "{$row->name} (ост. ".(int)$row->qty." {$row->unit})",
                'qty_left'  => (int)$row->qty,
                'barcode'   => $row->barcode,
                'unit'      => $row->unit,
                'last_price'=> $lastPrice ? (float)$lastPrice : null,
            ];
        }

        return response()->json([
            'results' => $results
        ]);
    }

    /**
     * API: последняя закупочная цена по товару и локации
     *
     * GET /api/stock/last-price?product_id=1&location_type=warehouse&location_id=1
     */
    public function lastPrice(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|integer|exists:products,id',
            'location_type' => 'required|in:warehouse,store',
            'location_id'   => 'required|integer',
        ]);

        $q = StockMovement::where('product_id', $request->product_id)
            ->where('direction', 'in');

        if ($request->location_type === 'warehouse') {
            $q->where('warehouse_id', $request->location_id);
        } else {
            $q->where('store_id', $request->location_id);
        }

        $price = $q->orderBy('created_at', 'desc')->value('unit_price');

        return response()->json([
            'success' => true,
            'price'   => $price ? (float)$price : null,
        ]);
    }

    /**
     * История движений по товару (простая страница)
     * /stock/history/{product}
     */
    public function history(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $movements = StockMovement::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('stock.history', compact('product', 'movements'));
    }
    public function stockPage()
    {
        $warehouses = \App\Models\Warehouse::orderBy('name')->get();
        $stores     = \App\Models\Store::orderBy('name')->get();

        return view('stock.index', compact('warehouses', 'stores'));
    }
    public function stocklist(Request $request)
    {
        $locationType = $request->location_type; // warehouse or store
        $locationId   = $request->location_id;

        $query = StockMovement::select(
            'products.id as product_id',
            'products.barcode as barcode',
            'products.name',
            DB::raw('SUM(CASE WHEN stock_movements.direction = "in" THEN quantity ELSE -quantity END) as qty'),
            'products.unit',
            'products.min_stock',
            'products.max_stock',
            DB::raw('MAX(stock_movements.created_at) as last_move')
        )
            ->join('products', 'products.id', '=', 'stock_movements.product_id');

        if ($locationType === 'warehouse') {
            $query->where('warehouse_id', $locationId);
        }

        if ($locationType === 'store') {
            $query->where('store_id', $locationId);
        }

        // поиск товаров
        if ($request->search) {
            $query->where('products.barcode', 'LIKE', "%{$request->search}%");
        }

        $query->groupBy(
            'products.id',
            'products.name',
            'products.unit',
            'products.min_stock',
            'products.max_stock'
        );

        $items = $query->orderBy('products.name')->get();

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }



    public function exportExcel(Request $request)
    {
        $data = $this->getStockData($request);

        return Excel::download(new \App\Exports\StockExport($data), 'stock.xlsx');
    }


}
