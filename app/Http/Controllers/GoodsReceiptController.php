<?php
namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\PriceHistory;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    public function create()
    {
        return view('receipts.create');
    }

    // Создание документа
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Документ
            $receipt = GoodsReceipt::create([
                'warehouse_id' => $request->warehouse_id,
                'user_id' => auth()->id(),
                'document_number' => 'GR-' . time(),
                'document_date' => now(),
                'supplier_name' => $request->supplier_name,
                'comment' => $request->comment,
            ]);

            // Строки документа + накопление
            foreach ($request->items as $row) {

                $item = GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_id' => $row['product_id'],
                    'barcode' => $row['barcode'],
                    'quantity' => (int)$row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'is_used' => !empty($row['is_used']),
                    'expiry_date' => $row['expiry_date'] ?? null,
                    'batch' => $row['batch'] ?? null,
                ]);

                // Движение
                StockMovement::create([
                    'product_id' => $row['product_id'],
                    'warehouse_id' => $request->warehouse_id,
                    'document_type' => 'goods_receipt',
                    'document_id' => $receipt->id,
                    'direction' => 'in',
                    'quantity' => (int)$row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'is_used' => !empty($row['is_used']),
                    'expiry_date' => $row['expiry_date'] ?? null,
                    'batch' => $row['batch'] ?? null,
                ]);

                PriceHistory::create([
                    'product_id' => $row['product_id'],
                    'user_id' => auth()->id(),
                    'price_type' => 'purchase',
                    'new_price' => $row['unit_price'],
                    'source_type' => GoodsReceipt::class,
                    'source_id' => $receipt->id,
                ]);
            }

            DB::commit();
            Audit::log('goods_receipt_created', $receipt, 'Создана приемка ' . $receipt->document_number);
            return response()->json(['success' => true, 'receipt_id' => $receipt->id]);
        }
        catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function journal(Request $request)
    {
        $query = GoodsReceipt::with(['warehouse', 'user'])
            ->orderBy('document_date', 'desc');

        // Фильтр по складу
        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Фильтр по пользователю
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Поиск по номеру документа
        if ($request->search) {
            $query->where('document_number', 'like', "%{$request->search}%");
        }

        // Фильтр по датам
        if ($request->date_from) {
            $query->whereDate('document_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('document_date', '<=', $request->date_to);
        }

        $rows = $query->paginate(30)->withQueryString();

        $warehouses = \App\Models\Warehouse::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('receipts.journal', compact('rows', 'warehouses', 'users'));
    }
    public function show($id)
    {
        $receipt = GoodsReceipt::with([
            'warehouse',
            'user',
            'items.product'
        ])->findOrFail($id);

        return view('receipts.show', compact('receipt'));
    }
    public function print($id)
    {
        $receipt = GoodsReceipt::with(['warehouse','user','items.product'])->findOrFail($id);

        return view('receipts.print', compact('receipt'));
    }

}
