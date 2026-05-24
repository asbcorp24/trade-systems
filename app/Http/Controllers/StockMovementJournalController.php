<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoodsReceiptItem;
use App\Models\StockTransferItem;
use App\Models\Product;

class StockMovementJournalController extends Controller
{
    public function index(Request $request)
    {
        $rows = collect();

        //
        // 1. Приёмка — приход
        //
        $receipts = GoodsReceiptItem::with(['product', 'receipt.warehouse', 'receipt.user'])
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->receipt->document_date,
                    'product' => $item->product->name,
                    'product_id' => $item->product->id,
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
        $transfers = StockTransferItem::with(['product', 'transfer.fromWarehouse', 'transfer.toWarehouse', 'transfer.user'])
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->transfer->document_date,
                    'product' => $item->product->name,
                    'product_id' => $item->product->id,
                    'document' => 'Перемещение № ' . $item->transfer->document_number,
                    'doc_link' => route('transfers.show', $item->transfer->id),
                    'type' => 'Перемещение',
                    'warehouse' =>
                        ($item->transfer->fromWarehouse->name ?? $item->transfer->fromStore->name)
                        . ' → ' .
                        ($item->transfer->toWarehouse->name ?? $item->transfer->toStore->name),
                    'qty' => $item->quantity,
                    'user' => $item->transfer->user->name ?? '—',
                ];
            });

        //
        // Объединение
        //
        $rows = $receipts->merge($transfers)
            ->sortByDesc('date')
            ->values();

        $products = Product::orderBy('name')->get();

        return view('stock.movements', compact('rows', 'products'));
    }
}
