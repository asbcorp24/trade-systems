<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockMovement;

class ProductSearchController extends Controller
{
    public function index()
    {
        return view('products.search');
    }

    public function search(Request $request)
    {
        $query = trim($request->get('query'));

        if (!$query) {
            return back()->with('error', 'Введите штрих-код или название товара.');
        }

        // Ищем товар по штрих-коду или имени
        $product = Product::where('barcode', $query)
            ->orWhere('name', 'LIKE', "%$query%")
            ->first();

        if (!$product) {
            return back()->with('error', 'Товар не найден.');
        }

        // Получаем остатки по складам
        $warehouses = Warehouse::all();

        $stock = [];

        foreach ($warehouses as $wh) {

            $in = StockMovement::where('product_id', $product->id)
                ->where('warehouse_id', $wh->id)
                ->where('direction', 'in')
                ->sum('quantity');

            $out = StockMovement::where('product_id', $product->id)
                ->where('warehouse_id', $wh->id)
                ->where('direction', 'out')
                ->sum('quantity');

            $qty = $in - $out;

            if ($qty != 0) {
                $stock[] = [
                    'warehouse_id' => $wh->id,
                    'warehouse'    => $wh->name,
                    'qty'          => $qty,
                ];
            }
        }

        return view('products.search', [
            'query'   => $query,
            'product' => $product,
            'stock'   => $stock
        ]);
    }
    public function findByBarcode(Request $request)
    {
        $barcode = $request->get('barcode');
        $locationType = $request->get('location_type'); // warehouse / store
        $locationId   = $request->get('location_id');

        if (!$barcode || !$locationType || !$locationId) {
            return response()->json(['results' => []]);
        }

        $product = Product::where('barcode', $barcode)->first();

        if (!$product) {
            return response()->json(['results' => []]);
        }

        // Остаток
        $movement = \App\Models\StockMovement::where('product_id', $product->id);

        if ($locationType === 'warehouse') {
            $movement->where('warehouse_id', $locationId);
        } else {
            $movement->where('store_id', $locationId);
        }

        $qty = $movement->selectRaw(
            'COALESCE(SUM(CASE WHEN direction="in" THEN quantity ELSE -quantity END), 0) AS qty'
        )->value('qty');

        // Последняя цена
        $lastPrice = \App\Models\StockMovement::where('product_id', $product->id)
            ->orderBy('id', 'desc')
            ->value('unit_price');

        return response()->json([
            'results' => [
                [
                    'id'        => $product->id,
                    'text'      => $product->name,
                    'barcode'   => $product->barcode,
                    'qty_left'  => (int)($qty ?? 0),
                    'unit'      => $product->unitRef->name ?? '',
                    'last_price'=> $lastPrice ?? $product->base_price ?? 0
                ]
            ]
        ]);
    }


}
