<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Store;
use App\Models\Product;
use App\Models\TransferDiscrepancy;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferController extends Controller
{
    public function create(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $stores     = Store::orderBy('name')->get();
        $presetFromSearch  = false;
        $presetProductName = null;
        $presetQtyLeft     = null;

        // Новый код — авто-подстановка товара
        $presetProductId = $request->get('product_id');
        $presetFromId    = $request->get('from');

        if ($presetProductId) {
            $presetFromSearch  = true;
            $product           = Product::find($presetProductId);
            if ($product) {
                $presetProductName = $product->name;
                // Рассчитываем остаток на складе (warehouse_id = $presetFromId) через StockMovement
                $in  = StockMovement::where('product_id', $presetProductId)
                    ->where('warehouse_id', $presetFromId)
                    ->where('direction', 'in')
                    ->sum('quantity');
                $out = StockMovement::where('product_id', $presetProductId)
                    ->where('warehouse_id', $presetFromId)
                    ->where('direction', 'out')
                    ->sum('quantity');
                $presetQtyLeft = $in - $out;
            }
        }
        return view('transfers.create', compact(
            'warehouses',
            'stores',
            'presetFromSearch',
            'presetProductId',
            'presetProductName',
            'presetQtyLeft',
            'presetFromId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_type' => 'required|in:warehouse,store',
            'to_type'   => 'required|in:warehouse,store',
            'from_id'   => 'required|integer',
            'to_id'     => 'required|integer',
            'items'     => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

// Дополнительная кастомная проверка
        if ($request->from_type === $request->to_type && $request->from_id == $request->to_id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя перемещать из той же локации.',
            ], 422);
        }

        $items = $request->items;

        // Проверка остатков перед записью
        foreach ($items as $i => $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Строка №".($i+1).": не указан товар или количество"
                ], 422);
            }

            $productId = $item['product_id'];
            $qty       = (int)$item['quantity'];

            $stockQuery = StockMovement::where('product_id', $productId);

            if ($request->from_type === 'warehouse') {
                $stockQuery->where('warehouse_id', $request->from_id);
            } else {
                $stockQuery->where('store_id', $request->from_id);
            }

            $stock = $stockQuery->selectRaw(
                'COALESCE(SUM(CASE WHEN direction="in" THEN quantity ELSE -quantity END), 0) AS qty'
            )->value('qty');

            if ($qty > $stock) {
                $productName = Product::find($productId)->name ?? 'Товар '.$productId;

                return response()->json([
                    'success' => false,
                    'message' => "Недостаточно остатка для товара '{$productName}'. На локации: {$stock}, пытаетесь списать: {$qty}",
                ], 422);
            }
        }

        // Если всё ок — проводим документ
        DB::transaction(function () use ($request, $items) {

            $transfer = StockTransfer::create([
                'from_warehouse_id' => $request->from_type == 'warehouse' ? $request->from_id : null,
                'from_store_id'     => $request->from_type == 'store' ? $request->from_id : null,
                'to_warehouse_id'   => $request->to_type == 'warehouse' ? $request->to_id : null,
                'to_store_id'       => $request->to_type == 'store' ? $request->to_id : null,
                'user_id'           => auth()->id(),
                'document_number'   => 'TR-' . time(),
                'document_date'     => now(),
                'status'            => 'shipped',
                'comment'           => $request->comment,
            ]);
            Audit::log('transfer_shipped', $transfer, 'Создан груз ' . $transfer->document_number);

            foreach ($items as $item) {

                $qty   = (int)$item['quantity'];
                $price = isset($item['unit_price']) && $item['unit_price'] !== ''
                    ? (float)$item['unit_price']
                    : null;

                $line = StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $qty,
                    'unit_price'        => $price,
                    'expiry_date'       => $item['expiry_date'] ?? null,
                    'batch'             => $item['batch'] ?? null,
                ]);

                if ($price !== null) {
                    \App\Models\PriceHistory::create([
                        'product_id' => $item['product_id'],
                        'user_id' => auth()->id(),
                        'price_type' => 'transfer',
                        'new_price' => $price,
                        'source_type' => StockTransfer::class,
                        'source_id' => $transfer->id,
                    ]);
                }

                // OUT (списание)
                StockMovement::create([
                    'product_id'    => $item['product_id'],
                    'warehouse_id'  => $transfer->from_warehouse_id,
                    'store_id'      => $transfer->from_store_id,
                    'document_type' => 'transfer',
                    'document_id'   => $transfer->id,
                    'direction'     => 'out',
                    'quantity'      => $qty,
                    'unit_price'    => $price,
                    'expiry_date'   => $item['expiry_date'] ?? null,
                    'batch'         => $item['batch'] ?? null,
                ]);

                // Приход в точку назначения создается отдельно при приемке груза.
            }
        });

        return response()->json(['success' => true]);
    }

    public function receive($id)
    {
        $transfer = StockTransfer::with([
            'fromWarehouse',
            'fromStore',
            'toWarehouse',
            'toStore',
            'items.product',
        ])->findOrFail($id);

        return view('transfers.receive', compact('transfer'));
    }

    public function accept(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $transfer = StockTransfer::with(['items.product', 'discrepancies'])->findOrFail($id);

        if ($transfer->status === 'received') {
            return response()->json([
                'success' => false,
                'message' => 'Груз уже принят.',
            ], 422);
        }

        DB::transaction(function () use ($request, $transfer) {
            $itemsByProduct = $transfer->items->keyBy('product_id');
            $acceptedProductIds = [];

            foreach ($request->items as $row) {
                $item = $itemsByProduct->get((int)$row['product_id']);
                $acceptedProductIds[] = (int)$row['product_id'];
                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => 'Товар не входит в этот груз.',
                    ]);
                }

                $qty = (int)$row['quantity'];
                $remaining = (int)$item->quantity - (int)$item->received_quantity;

                if ($qty > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => "Принято больше, чем отгружено по товару {$item->product->name}.",
                    ]);
                }

                $item->received_quantity = (int)$item->received_quantity + $qty;
                $item->save();

                StockMovement::create([
                    'product_id'    => $item->product_id,
                    'warehouse_id'  => $transfer->to_warehouse_id,
                    'store_id'      => $transfer->to_store_id,
                    'document_type' => 'transfer_receive',
                    'document_id'   => $transfer->id,
                    'direction'     => 'in',
                    'quantity'      => $qty,
                    'unit_price'    => $item->unit_price,
                    'expiry_date'   => $item->expiry_date,
                    'batch'         => $item->batch,
                ]);
            }

            $transfer->refresh()->load('items');
            foreach ($transfer->items as $item) {
                $shortage = max(0, (int)$item->quantity - (int)$item->received_quantity);
                $surplus = max(0, (int)$item->received_quantity - (int)$item->quantity);

                if ($shortage > 0 || $surplus > 0) {
                    TransferDiscrepancy::updateOrCreate(
                        [
                            'stock_transfer_id' => $transfer->id,
                            'product_id' => $item->product_id,
                        ],
                        [
                            'shipped_quantity' => (int)$item->quantity,
                            'received_quantity' => (int)$item->received_quantity,
                            'shortage_quantity' => $shortage,
                            'surplus_quantity' => $surplus,
                            'user_id' => auth()->id(),
                        ]
                    );
                } else {
                    TransferDiscrepancy::where('stock_transfer_id', $transfer->id)
                        ->where('product_id', $item->product_id)
                        ->delete();
                }
            }

            $allReceived = $transfer->items->every(function ($item) {
                return (int)$item->received_quantity >= (int)$item->quantity;
            });

            $transfer->update([
                'status' => $allReceived ? 'received' : 'partially_received',
                'received_at' => $allReceived ? now() : null,
                'received_user_id' => auth()->id(),
            ]);

            Audit::log('transfer_received', $transfer, 'Принят груз ' . $transfer->document_number);
        });

        return response()->json(['success' => true]);
    }
    public function journal(Request $request)
    {
        $query = \App\Models\StockTransfer::query()
            ->with(['fromWarehouse', 'fromStore', 'toWarehouse', 'toStore', 'user'])
            ->orderBy('id', 'desc');

        // Фильтр: тип исходной локации
        if ($request->from_type) {
            if ($request->from_type === 'warehouse') {
                $query->whereNotNull('from_warehouse_id');
            } else {
                $query->whereNotNull('from_store_id');
            }
        }
// Фильтр: дата от
        if ($request->date_from) {
            $query->whereDate('document_date', '>=', $request->date_from);
        }

// Фильтр: дата до
        if ($request->date_to) {
            $query->whereDate('document_date', '<=', $request->date_to);
        }
        // Фильтр: тип конечной локации
        if ($request->to_type) {
            if ($request->to_type === 'warehouse') {
                $query->whereNotNull('to_warehouse_id');
            } else {
                $query->whereNotNull('to_store_id');
            }
        }

        // Фильтр по пользователю
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Поиск по номеру документа
        if ($request->search) {
            $query->where('document_number', 'like', "%{$request->search}%");
        }

        $rows = $query->paginate(30)->withQueryString();

        $users = \App\Models\User::orderBy('name')->get();

        return view('transfers.journal', compact('rows', 'users'));
    }
    public function show($id)
    {
        $transfer = StockTransfer::with([
            'fromWarehouse',
            'fromStore',
            'toWarehouse',
            'toStore',
            'user',
            'items.product',
            'discrepancies.product',
        ])->findOrFail($id);

        return view('transfers.show', compact('transfer'));
    }
    public function print($id)
    {
        $transfer = StockTransfer::with(['items.product','fromWarehouse','toWarehouse','fromStore','toStore','user'])
            ->findOrFail($id);

        return view('transfers.print', compact('transfer'));
    }
}
