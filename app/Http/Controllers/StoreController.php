<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    // Страница со списком магазинов
    public function index()
    {
        return view('stores.index');
    }

    // AJAX: список магазинов
    public function list()
    {
        $stores = Store::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $stores,
        ]);
    }

    // AJAX: создание магазина
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:50|unique:stores,code',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors(),
            ], 422);
        }

        $data = $v->validated();
        $data['is_active'] = $request->boolean('is_active');

        $store = Store::create($data);

        return response()->json([
            'success' => true,
            'store'   => $store,
        ]);
    }

    // AJAX: обновление магазина
    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:50|unique:stores,code,' . $store->id,
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors(),
            ], 422);
        }

        $data = $v->validated();
        $data['is_active'] = $request->boolean('is_active');

        $store->update($data);

        return response()->json([
            'success' => true,
            'store'   => $store,
        ]);
    }

    // AJAX: удаление магазина
    public function destroy($id)
    {
        $store = Store::findOrFail($id);
        $store->delete();

        return response()->json(['success' => true]);
    }
}
