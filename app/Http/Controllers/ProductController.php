<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\Attribute;
use Illuminate\Support\Facades\Storage;
use App\Models\GoodsReceiptItem;
use App\Models\PriceHistory;
use App\Support\Audit;

class ProductController extends Controller
{
    public function generateBarcode()
    {
        do {
            $base = '20' . str_pad((string)random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
            $barcode = $base . $this->ean13Checksum($base);
        } while (Product::where('barcode', $barcode)->exists());

        return response()->json([
            'success' => true,
            'barcode' => $barcode,
        ]);
    }

    private function ean13Checksum(string $base): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$base[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return (10 - ($sum % 10)) % 10;
    }

    public function lastPurchasePrice($id)
    {
        $product = Product::findOrFail($id);
        $item = GoodsReceiptItem::where('product_id', $id)
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'success' => true,
            'price'   => $item?->unit_price ?? $product->base_price,
        ]);
    }

    public function edit($id)
    {
        $product = Product::with(['attributes.attribute', 'images'])->findOrFail($id);
        $attributes = Attribute::orderBy('name')->get();

        return view('products.edit', compact('product', 'attributes'));
    }
    public function index()
    {
        return view('products.index');
    }
    public function updateAttributes(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->attributes()->delete(); // перезаписываем всё

        foreach ($request->attributes as $attrId => $value) {
            if ($value != "") {
                ProductAttribute::create([
                    'product_id' => $product->id,
                    'attribute_id' => $attrId,
                    'value' => $value
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
    public function deleteImage($id)
{
    $img = ProductImage::findOrFail($id);
    Storage::disk('public')->delete($img->path);
    $img->delete();

    return response()->json(['success' => true]);
}

    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|max:4096'
        ]);

        $path = $request->file('image')->store('products/gallery', 'public');

        $img = ProductImage::create([
            'product_id' => $id,
            'path'       => $path
        ]);

        return response()->json([
            'success' => true,
            'image' => $img
        ]);
    }

    public function listAjax(Request $request)
    {
        $query = Product::query();

        // Поиск
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }

        // Категория
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $products
        ]);
    }


    // === AJAX: поиск по штрихкоду ===
    public function findByBarcode($barcode)
    {
        $product = Product::where('barcode', $barcode)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден',
            ]);
        }

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    // === Страница создания товара ===
    public function create()
    {
        return view('products.create');
    }

    // === AJAX: создание товара ===
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barcode'     => 'nullable|string|max:13|unique:products',
            'name'        => 'required|string|max:255',
            'unit'        => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'base_price'  => 'nullable|numeric',
            'description' => 'nullable|string',

            // >>> ДОБАВИЛ ДЛЯ ФОТО <<<
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // >>> СОХРАНЕНИЕ ФОТО <<<
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('products', 'public');
            $data['photo_path'] = $path;
        }

        $product = Product::create($data);
        Audit::log('product_created', $product, 'Создан товар ' . $product->name);
        if (!empty($data['base_price'])) {
            PriceHistory::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'price_type' => 'base',
                'new_price' => $data['base_price'],
                'source_type' => Product::class,
                'source_id' => $product->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    // === AJAX: обновление товара ===
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'barcode'     => 'nullable|string|max:13|unique:products,barcode,' . $id,
            'name'        => 'required|string|max:255',
            'unit'        => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'base_price'  => 'nullable|numeric',
            'description' => 'nullable|string',

            // >>> ДОБАВИЛ ДЛЯ ЗАМЕНЫ ФОТО <<<
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // >>> ЗАМЕНА ФОТО <<<
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('products', 'public');
            $data['photo_path'] = $path;
        }

        $oldPrice = $product->base_price;
        $product->update($data);
        Audit::log('product_updated', $product, 'Изменен товар ' . $product->name);
        if (array_key_exists('base_price', $data) && (string)$oldPrice !== (string)$product->base_price) {
            PriceHistory::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'price_type' => 'base',
                'old_price' => $oldPrice,
                'new_price' => $product->base_price,
                'source_type' => Product::class,
                'source_id' => $product->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    // Добавить атрибут товару
    public function addAttribute(Request $request, $productId)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => 'required'
        ]);

        return ProductAttribute::create([
            'product_id' => $productId,
            'attribute_id' => $request->attribute_id,
            'value' => $request->value,
        ]);
    }

// Обновить значение атрибута
    public function updateAttribute(Request $request, $id)
    {
        $pa = ProductAttribute::findOrFail($id);
        $pa->update(['value' => $request->value]);

        return $pa;
    }

// Удалить атрибут
    public function deleteAttribute($id)
    {
        ProductAttribute::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
    public function searchSelect2(Request $request)
    {
        $query = $request->get('query');

        $products = Product::where('name', 'like', "%$query%")
            ->orWhere('barcode', 'like', "%$query%")
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'text' => $p->name . " (" . $p->barcode . ")",
                    'name' => $p->name,
                    'barcode' => $p->barcode,
                ];
            })
        ]);
    }


}
