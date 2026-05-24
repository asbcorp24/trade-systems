<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Store;
use App\Models\StockMovement;
use App\Models\PriceHistory;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    // Страница POS-продажи
    public function create()
    {
        $stores = Store::orderBy('name')->get();

        return view('sales.create', compact('stores'));
    }

    // Поиск товаров по магазину: только в наличии, + цена
    public function searchProducts(Request $request)
    {
        $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'query'    => 'nullable|string',
        ]);

        $storeId = $request->store_id;
        $search  = $request->query('query');

        // Остатки внутри магазина
        $q = StockMovement::select(
            'products.id',
            'products.name',
            'products.barcode',
            DB::raw('SUM(CASE WHEN direction="in" THEN quantity ELSE -quantity END) as qty')
        )
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->where('stock_movements.store_id', $storeId)
            ->groupBy('products.id', 'products.name', 'products.barcode')
            ->having('qty', '>', 0);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }

        $items = $q->orderBy('products.name')->limit(50)->get();

        $results = $items->map(function ($p) {
            $id = $p->id;

            // 1) последняя продажа
            $lastSale = DB::table('sale_items')
                ->where('product_id', $id)
                ->orderByDesc('id')
                ->value('unit_price');

            // 2) последняя закупка (приёмка)
            $lastReceipt = DB::table('goods_receipt_items')
                ->where('product_id', $id)
                ->orderByDesc('id')
                ->value('unit_price');

            // 3) последняя цена перемещения
            $lastTransfer = DB::table('stock_transfer_items')
                ->where('product_id', $id)
                ->orderByDesc('id')
                ->value('unit_price');

            // 4) последнее входящее движение
            $lastMovement = DB::table('stock_movements')
                ->where('product_id', $id)
                ->where('direction', 'in')
                ->orderByDesc('id')
                ->value('unit_price');

            $lastPrice = $lastSale
                ?? $lastReceipt
                ?? $lastTransfer
                ?? $lastMovement
                ?? 0;

            return [
                'id'         => $p->id,
                'text'       => $p->name . " ({$p->barcode}) [остаток: {$p->qty}]",
                'barcode'    => $p->barcode,
                'qty'        => $p->qty,
                'unit_price' => $lastPrice,
            ];
        });

        return response()->json([
            'results' => $results,
        ]);
    }

    // Проведение продажи
    public function store(Request $request)
    {
        $request->validate([
            'store_id'             => 'required|integer|exists:stores,id',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'payment_type'         => 'required|in:cash,card',
            'discount_percent'     => 'nullable|integer|in:0,10,20,30,40,50,60,70',
            'customer_name'        => 'nullable|string',
            'customer_phone'       => 'nullable|string',
            'comment'              => 'nullable|string',
        ]);

        $user    = auth()->user();
        $storeId = $request->store_id;
        $items   = $request->items;

        return DB::transaction(function () use ($user, $storeId, $items, $request) {

            // проверка остатков по каждому товару в магазине
            foreach ($items as $item) {
                $available = StockMovement::where('product_id', $item['product_id'])
                    ->where('store_id', $storeId)
                    ->sum(DB::raw('CASE WHEN direction="in" THEN quantity ELSE -quantity END'));

                if ($available < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Недостаточно остатка по товару ID ' . $item['product_id'],
                    ], 422);
                }
            }

            $docNumber = 'SL-' . time();
            $discountPercent = (int)$request->input('discount_percent', 0);
            $discountMultiplier = (100 - $discountPercent) / 100;

            $total = 0;
            foreach ($items as $item) {
                $total += $item['quantity'] * $item['unit_price'] * $discountMultiplier;
            }
            $total = round($total, 2);

            $sale = Sale::create([
                'store_id'       => $storeId,
                'user_id'        => $user->id,
                'document_number'=> $docNumber,
                'document_date'  => now(),
                'total_amount'   => $total,
                'discount_percent' => $discountPercent,
                'customer_name'  => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'comment'        => $request->comment,
            ]);

            foreach ($items as $item) {
                $product   = Product::find($item['product_id']);
                $discountedPrice = round($item['unit_price'] * $discountMultiplier, 2);
                $lineTotal = round($item['quantity'] * $discountedPrice, 2);

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $product->id,
                    'barcode'    => $product->barcode,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $discountedPrice,
                    'line_total' => $lineTotal,
                ]);

                // расход со склада магазина
                StockMovement::create([
                    'product_id'    => $product->id,
                    'warehouse_id'  => null,
                    'store_id'      => $storeId,
                    'document_type' => 'sale',
                    'document_id'   => $sale->id,
                    'direction'     => 'out',
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $discountedPrice,
                    'expiry_date'   => null,
                    'batch'         => null,
                ]);

                PriceHistory::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'price_type' => 'sale',
                    'new_price' => $discountedPrice,
                    'source_type' => Sale::class,
                    'source_id' => $sale->id,
                    'discount_percent' => $discountPercent,
                ]);
            }

            $payment = Payment::create([
                'sale_id'      => $sale->id,
                'payment_type' => $request->payment_type,
                'amount'       => $total,
                'paid_at'      => now(),
                'kkm_status'   => 'pending',
            ]);

            // здесь дальше можно позвать KkmServer через Http::post(...)
            // и обновить $payment->kkm_status / kkm_ticket
            Audit::log('sale_created', $sale, 'Продажа ' . $sale->document_number, [
                'discount_percent' => $discountPercent,
                'payment_type' => $request->payment_type,
            ]);

            return response()->json([
                'success' => true,
                'document_number' => $sale->document_number,
                'total' => (float)$sale->total_amount,
                'discount_percent' => $discountPercent,
                'cashier' => auth()->user()->name,
                'cashier_inn' => auth()->user()->inn ?? null,
                'items' => $sale->items()->with('product')->get()->map(fn($i) => [
                    'name' => $i->product->name,
                    'quantity' => (int)$i->quantity,
                    'unit_price' => (float)$i->unit_price,
                    'total' => (float)$i->quantity * (float)$i->unit_price,
                    'barcode' => $i->product->barcode,
                ])
            ]);

        });
    }
    public function index()
    {
        $sales = Sale::with('user', 'store')
            ->orderBy('id', 'desc')
            ->paginate(20);

        // ← добавляем загрузку списка магазинов
        $stores = Store::orderBy('name')->get();

        return view('sales.index', compact('sales', 'stores'));
    }

    public function show($id)
    {
        $sale = Sale::with(['items.product', 'payments'])->findOrFail($id);

        return view('sales.show', compact('sale'));
    }
    public function refund(Request $request, $id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ($sale->is_refunded) {
            return response()->json([
                'success' => false,
                'message' => 'Возврат уже оформлен.'
            ], 422);
        }

        return DB::transaction(function () use ($sale, $request) {

            $refundDoc = Sale::create([
                'store_id'       => $sale->store_id,
                'user_id'        => auth()->id(),
                'document_number'=> 'RF-' . time(),
                'document_date'  => now(),
                'total_amount'   => -1 * $sale->total_amount,
                'customer_name'  => $sale->customer_name,
                'customer_phone' => $sale->customer_phone,
                'comment'        => 'Возврат по документу ' . $sale->document_number,
                'is_refund'      => 1
            ]);

            foreach ($sale->items as $item) {

                SaleItem::create([
                    'sale_id'    => $refundDoc->id,
                    'product_id' => $item->product_id,
                    'barcode'    => $item->barcode,
                    'quantity'   => -1 * $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => -1 * $item->line_total,
                ]);

                // Возврат на остатки магазина
                StockMovement::create([
                    'product_id'    => $item->product_id,
                    'warehouse_id'  => null,
                    'store_id'      => $sale->store_id,
                    'document_type' => 'refund',
                    'document_id'   => $refundDoc->id,
                    'direction'     => 'in',
                    'quantity'      => $item->quantity,
                    'unit_price'    => $item->unit_price,
                    'expiry_date'   => null,
                    'batch'         => null,
                ]);
            }

            // Платёж возврата клиенту
            Payment::create([
                'sale_id'      => $refundDoc->id,
                'payment_type' => $sale->payments->first()->payment_type,
                'amount'       => -1 * $sale->total_amount,
                'paid_at'      => now(),
                'kkm_status'   => 'pending'
            ]);

            // помечаем оригинальную продажу как возвращённую
            $sale->update(['is_refunded' => 1]);
            Audit::log('sale_refunded', $sale, 'Возврат продажи ' . $sale->document_number);

            return response()->json([
                'success' => true,
                'refund_document' => $refundDoc->document_number,
                'amount' => -1 * $sale->total_amount
            ]);
        });
    }

}
