<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Страница
    public function index()
    {
        return view('categories.index');
    }

    // AJAX: список категорий с деревом
    public function list()
    {
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();
        return response()->json($categories);
    }

    // AJAX: создать категорию
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $cat = Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id
        ]);

        return response()->json(['success' => true, 'category' => $cat]);
    }

    // AJAX: обновить категорию
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $cat = Category::findOrFail($id);

        // запрещаем выбрать саму себя родителем
        if ($request->parent_id == $id) {
            return response()->json(['error' => 'Категория не может быть родителем самой себе'], 422);
        }

        $cat->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id
        ]);

        return response()->json(['success' => true]);
    }

    // AJAX: удалить
    public function delete($id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
