<?php
namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index()
    {
        return view('attributes.index', [
            'attributes' => Attribute::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        Attribute::create(['name' => $request->name]);

        return redirect()->back()->with('success', 'Параметр добавлен');
    }

    public function delete($id)
    {
        Attribute::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Параметр удалён');
    }
}
