<?php
namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        return view('units.index', [
            'units' => Unit::orderBy('name')->get()
        ]);
    }

    public function list()
    {
        return response()->json(Unit::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:units',
            'name' => 'required|string',
        ]);

        $unit = Unit::create($request->only('code','name'));

        return response()->json([
            'success' => true,
            'unit' => $unit
        ]);
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
            'code' => 'required|string|unique:units,code,' . $id,
            'name' => 'required|string',
        ]);

        $unit->update($request->only('code','name'));

        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        Unit::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
