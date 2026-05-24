<?php

// app/Http/Controllers/InventoryController.php
namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // Журнал
    public function index(Request $request)
    {
        $query = Inventory::with(['warehouse','user'])->orderByDesc('document_date');

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $rows = $query->paginate(30)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('inventory.index', compact('rows','warehouses'));
    }

    // Форма создания
    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('inventory.create', compact('warehouses'));
    }

    // Создать документ и сразу заполнить ожидаемые остатки
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'comment' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // простой авто-номер, можно заменить на свой генератор
            $number = 'ИНВ-'.str_pad((Inventory::max('id') + 1), 6, '0', STR_PAD_LEFT);

            $inventory = Inventory::create([
                'document_number' => $number,
                'document_date' => now(),
                'warehouse_id' => $request->warehouse_id,
                'user_id' => Auth::id(),
                'status' => 'draft',
                'comment' => $request->comment,
            ]);

            // подтягиваем текущие остатки со склада
            $balances = DB::table('stock_movements')
                ->select(
                    'product_id',
                    DB::raw('SUM(quantity) as expected_qty'),
                    DB::raw('MAX(unit_price) as unit_price') // условно берём последнюю цену
                )
                ->where('warehouse_id', $inventory->warehouse_id)
                ->groupBy('product_id')
                ->having('expected_qty', '!=', 0)
                ->get();

            foreach ($balances as $row) {
                InventoryItem::create([
                    'inventory_id' => $inventory->id,
                    'product_id' => $row->product_id,
                    'expected_qty' => $row->expected_qty,
                    'actual_qty' => $row->expected_qty, // по умолчанию = ожидаемому
                    'diff_qty' => 0,
                    'unit_price' => $row->unit_price,
                    'diff_value' => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('inventory.show', $inventory->id)
                ->with('success', 'Инвентаризация создана. Проверьте фактическое количество и проведите документ.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Ошибка при создании инвентаризации: '.$e->getMessage());
        }
    }

    // Просмотр / редактирование (только черновик)
    public function show($id)
    {
        $inventory = Inventory::with(['warehouse','user','items.product'])->findOrFail($id);
        return view('inventory.show', compact('inventory'));
    }

    // Обновление фактических количеств из формы
    public function updateItems(Request $request, $id)
    {
        $inventory = Inventory::with('items')->findOrFail($id);

        if (!$inventory->isDraft()) {
            return back()->withErrors('Редактировать можно только черновик инвентаризации.');
        }

        $data = $request->input('items', []); // items[item_id][actual_qty]

        DB::beginTransaction();

        try {
            foreach ($data as $itemId => $row) {
                /** @var InventoryItem $item */
                $item = $inventory->items->firstWhere('id', $itemId);
                if (!$item) continue;

                $actual = isset($row['actual_qty']) ? (float)$row['actual_qty'] : 0;
                $diff   = $actual - (float)$item->expected_qty;
                $diffVal = $item->unit_price ? $diff * (float)$item->unit_price : 0;

                $item->actual_qty = $actual;
                $item->diff_qty   = $diff;
                $item->diff_value = $diffVal;
                $item->save();
            }

            DB::commit();
            return back()->with('success', 'Фактические количества обновлены.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Ошибка при сохранении: '.$e->getMessage());
        }
    }

    // Загрузка результатов из CSV (product_id;actual_qty)
    public function importCsv(Request $request, $id)
    {
        $inventory = Inventory::with('items')->findOrFail($id);

        if (!$inventory->isDraft()) {
            return back()->withErrors('Загружать результаты можно только в черновик.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('file')->getRealPath();
        $rows = array_map('str_getcsv', file($path, FILE_SKIP_EMPTY_LINES));

        DB::beginTransaction();

        try {
            foreach ($rows as $r) {
                // допустим формат: product_id;actual_qty
                if (count($r) < 2) continue;

                $productId = (int)trim($r[0]);
                $actual    = (float)str_replace(',', '.', $r[1]);

                $item = $inventory->items->firstWhere('product_id', $productId);
                if (!$item) continue;

                $diff   = $actual - (float)$item->expected_qty;
                $diffVal = $item->unit_price ? $diff * (float)$item->unit_price : 0;

                $item->actual_qty = $actual;
                $item->diff_qty   = $diff;
                $item->diff_value = $diffVal;
                $item->save();
            }

            DB::commit();
            return back()->with('success', 'Результаты инвентаризации загружены из файла.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Ошибка при импорте: '.$e->getMessage());
        }
    }

    // Печать листа (просто HTML под печать)
    public function print($id)
    {
        $inventory = Inventory::with(['warehouse','user','items.product'])->findOrFail($id);
        return view('inventory.print', compact('inventory'));
    }

    // Провести инвентаризацию
    public function apply($id)
    {
        $inventory = Inventory::with('items')->findOrFail($id);

        if (!$inventory->isDraft()) {
            return back()->withErrors('Провести можно только черновик.');
        }

        DB::beginTransaction();

        try {
            foreach ($inventory->items as $item) {
                if ((float)$item->diff_qty == 0) {
                    continue;
                }

                $direction = $item->diff_qty > 0 ? 'in' : 'out';
                $qty       = abs((float)$item->diff_qty);

                DB::table('stock_movements')->insert([
                    'product_id'    => $item->product_id,
                    'warehouse_id'  => $inventory->warehouse_id,
                    'store_id'      => null,
                    'document_type' => 'inventory',
                    'document_id'   => $inventory->id,
                    'direction'     => $direction,
                    'quantity'      => $qty,
                    'unit_price'    => $item->unit_price,
                    'expiry_date'   => null,
                    'batch'         => null,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $inventory->status = 'applied';
            $inventory->save();

            DB::commit();

            return redirect()->route('inventory.show', $inventory->id)
                ->with('success', 'Инвентаризация проведена. Движения по складу сформированы.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Ошибка при проведении: '.$e->getMessage());
        }
    }

    // Отмена
    public function cancel($id)
    {
        $inventory = Inventory::findOrFail($id);

        if ($inventory->isCancelled()) {
            return back()->withErrors('Документ уже отменён.');
        }

        if ($inventory->isApplied()) {
            // в простом варианте не трогаем сформированные движения,
            // можно добавить логику обратных движений
        }

        $inventory->status = 'cancelled';
        $inventory->save();

        return back()->with('success', 'Инвентаризация отменена.');
    }
}
