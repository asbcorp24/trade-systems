<?php
namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    // Страница
    public function index()
    {
        return view('warehouses.index');
    }

    // AJAX список
    public function list()
    {
        return Warehouse::orderBy('id', 'desc')->get();
    }

    // AJAX создать
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses',
        ]);

        $wh = Warehouse::create($request->only('name', 'code'));

        return response()->json(['success' => true, 'warehouse' => $wh]);
    }

    // AJAX обновить
    public function update(Request $request, $id)
    {
        $wh = Warehouse::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => "required|string|max:50|unique:warehouses,code,$id",
        ]);

        $wh->update($request->only('name', 'code'));

        return response()->json(['success' => true]);
    }

    // AJAX удалить
    public function delete($id)
    {
        Warehouse::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
