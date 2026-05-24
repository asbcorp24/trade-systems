<?php
namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Страница
    public function index()
    {
        return view('suppliers.index');
    }

    // AJAX список
    public function list()
    {
        return Supplier::orderBy('id', 'desc')->get();
    }

    // AJAX создание
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $supplier = Supplier::create($request->all());

        return response()->json(['success' => true, 'supplier' => $supplier]);
    }

    // AJAX обновление
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $supplier->update($request->all());

        return response()->json(['success' => true]);
    }

    // AJAX удаление
    public function delete($id)
    {
        Supplier::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
