<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\GoodsReceiptItem;
use App\Models\StockTransferItem;

class ProductMovementController extends Controller
{
    public function index($id, Request $request)
    {
        $product = Product::findOrFail($id);

        $rows = collect();

        //
        // 1. Приходы товара (приёмка)
        //
        $receipts = GoodsReceiptItem::with(['receipt.warehouse', 'receipt.user'])
            ->where('product_id', $id)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->receipt->document_date,
                    'document' => 'Приёмка № ' . $item->receipt->document_number,
                    'doc_link' => route('receipts.show', $item->receipt->id),
                    'type' => 'Приход',
                    'warehouse' => $item->receipt->warehouse->name,
                    'qty' => +$item->quantity,
                    'user' => $item->receipt->user->name ?? '—',
                ];
            });

        //
        // 2. Перемещения
        //
        $transfers = StockTransferItem::with(['transfer.fromWarehouse', 'transfer.toWarehouse', 'transfer.user'])
            ->where('product_id', $id)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->transfer->document_date,
                    'document' => 'Перемещение № ' . $item->transfer->document_number,
                    'doc_link' => route('transfers.show', $item->transfer->id),
                    'type' => 'Перемещение',
                    'warehouse' => ($item->transfer->fromWarehouse->name ?? $item->transfer->fromStore->name)
                        . ' → ' .
                        ($item->transfer->toWarehouse->name ?? $item->transfer->toStore->name),
                    'qty' => $item->quantity, // может быть минус если из склада?
                    'user' => $item->transfer->user->name ?? '—',
                ];
            });

        //
        // Объединяем
        //
        $rows = $receipts
            ->merge($transfers)
            ->sortBy('date')
            ->values();

        //
        // Расчёт остатка по движению
        //
        $balance = 0;
        foreach ($rows as &$r) {
            $balance += $r['qty'];
            $r['balance'] = $balance;
        }

        return view('products.movement', compact('product', 'rows'));
    }
}
