<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductSearchController;

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
Route::middleware(['auth'])->group(function () {

    // Страница продажи
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');

    // Поиск товаров, которые реально есть в магазине
    Route::get('/api/sales/products', [SaleController::class, 'searchProducts'])
        ->name('sales.products.search');

    // Создание продажи (AJAX)
    Route::post('/api/sales', [SaleController::class, 'store'])
        ->name('sales.store');

    // (опционально) история продаж магазина
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Страница магазинов


// Авторизация
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);

Route::get('/notifications', [NotificationController::class, 'user'])->middleware('auth');
// Пользователи — только супер
Route::middleware(['auth', 'superadmin.only'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'delete']);

    // Админ-панель уведомлений
    Route::get('/admin/notifications', [NotificationController::class, 'index'])->middleware('auth');

// Уведомления пользователя


// Пометить все как прочитанные
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->middleware('auth');

// Настройки уведомлений
    Route::get('/notifications/settings', [NotificationController::class, 'settings'])->middleware('auth');
    Route::post('/notifications/settings', [NotificationController::class, 'saveSettings'])->middleware('auth');

});

Route::middleware('auth')->group(function () {
    Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
    Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
    Route::delete('/attributes/{id}', [AttributeController::class, 'delete'])->name('attributes.delete');

    Route::get('/', function () {
        return view('home');
    });
// Страница
    Route::get('/receipts/create', [GoodsReceiptController::class, 'create'])->name('receipts.create');

// API (JSON)
    Route::post('/api/receipts', [GoodsReceiptController::class, 'store']);
    Route::get('/api/products/search', [ProductController::class, 'searchSelect2']);

    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/api/categories', [CategoryController::class, 'list']);
    Route::post('/api/categories', [CategoryController::class, 'store']);
    Route::post('/api/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/api/categories/{id}', [CategoryController::class, 'delete']);


    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');

    Route::get('/api/suppliers', [SupplierController::class, 'list']);
    Route::post('/api/suppliers', [SupplierController::class, 'store']);
    Route::post('/api/suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('/api/suppliers/{id}', [SupplierController::class, 'delete']);

    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');

    Route::get('/api/warehouses', [WarehouseController::class, 'list']);
    Route::post('/api/warehouses', [WarehouseController::class, 'store']);
    Route::post('/api/warehouses/{id}', [WarehouseController::class, 'update']);
    Route::delete('/api/warehouses/{id}', [WarehouseController::class, 'delete']);
// ==== RECEIPTS ====
    Route::post('/api/receipts', [GoodsReceiptController::class, 'store']);

// ==== TRANSFERS ====
    Route::post('/api/transfers', [StockTransferController::class, 'store']);

// ==== STOCK ====
    Route::get('/api/stock', [StockController::class, 'getStock']);


    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/api/products/store', [ProductController::class, 'store'])->name('products.store.ajax');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/api/products', [ProductController::class, 'listAjax'])->name('products.list.ajax');
    Route::get('/api/products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
    Route::post('/api/products/{id}', [ProductController::class, 'update']);
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::post('/api/products/{id}/attributes', [ProductController::class, 'updateAttributes']);
    Route::post('/api/products/{id}/images', [ProductController::class, 'uploadImage']);
    Route::delete('/api/product-images/{id}', [ProductController::class, 'deleteImage']);
    Route::post('/api/products/{id}/attributes', [ProductController::class, 'addAttribute']);
    Route::post('/api/product-attributes/{id}', [ProductController::class, 'updateAttribute']);
    Route::delete('/api/product-attributes/{id}', [ProductController::class, 'deleteAttribute']);
    Route::get('/api/products/{product}/last-price', [ProductController::class, 'lastPurchasePrice']);


    Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');

// AJAX API для магазинов
    Route::get('/api/stores', [StoreController::class, 'list'])->name('stores.list');
    Route::post('/api/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::post('/api/stores/{id}', [StoreController::class, 'update'])->name('stores.update');
    Route::delete('/api/stores/{id}', [StoreController::class, 'destroy'])->name('stores.destroy');


});

use App\Http\Controllers\StockTransferController;


Route::middleware(['auth'])->group(function () {

    // Страница перемещения
    Route::get('/stock/transfers/create', [StockTransferController::class, 'create'])
        ->name('transfers.create');

    // Сохранение перемещения
    Route::post('/api/stock/transfers', [StockTransferController::class, 'store'])
        ->name('transfers.store');

    // Остатки по локации (для Select2)
    Route::get('/api/stock/by-location', [StockController::class, 'byLocation'])
        ->name('stock.by_location');

    // Последняя цена
    Route::get('/api/stock/last-price', [StockController::class, 'lastPrice'])
        ->name('stock.last_price');

    // История по товару
    Route::get('/stock/history/{product}', [StockController::class, 'history'])
        ->name('stock.history');
    Route::get('/stock', [StockController::class, 'stockPage'])->name('stock.page');
    Route::get('/api/stock/list', [StockController::class, 'stockList'])->name('stock.list');


    // Units
    Route::get('/units', [UnitController::class, 'index'])->middleware('auth');
    Route::get('/api/units/list', [UnitController::class, 'list'])->middleware('auth');
    Route::post('/api/units', [UnitController::class, 'store'])->middleware('auth');
    Route::post('/api/units/{id}', [UnitController::class, 'update'])->middleware('auth');
    Route::delete('/api/units/{id}', [UnitController::class, 'delete'])->middleware('auth');


    Route::get('/transfers/journal', [\App\Http\Controllers\StockTransferController::class, 'journal'])
        ->name('transfers.journal');
    Route::get('/transfers/{id}', [\App\Http\Controllers\StockTransferController::class, 'show'])
        ->name('transfers.show');
    Route::get('/transfers/{id}/print', [StockTransferController::class, 'print'])
        ->name('transfers.print');


    Route::get('/receipts/journal', [\App\Http\Controllers\GoodsReceiptController::class, 'journal'])
        ->name('receipts.journal');

    Route::get('/receipts/{id}', [\App\Http\Controllers\GoodsReceiptController::class, 'show'])
        ->name('receipts.show');

    Route::get('/receipts/{id}/print', [\App\Http\Controllers\GoodsReceiptController::class, 'print'])
        ->name('receipts.print');

    Route::get('/products/{id}/movement', [\App\Http\Controllers\ProductMovementController::class, 'index'])
        ->name('products.movement');
    Route::get('/stock/movements', [\App\Http\Controllers\StockMovementJournalController::class, 'index'])
        ->name('stock.movements');
    Route::post('/telegram/webhook', [TelegramBotController::class, 'handle']);


    Route::post('/receipts/{id}/post', [GoodsReceiptController::class, 'post'])->name('receipts.post');
    Route::post('/receipts/{id}/cancel', [GoodsReceiptController::class, 'cancel'])->name('receipts.cancel');

    Route::post('/transfers/{id}/post', [StockTransferController::class, 'post'])->name('transfers.post');
    Route::post('/transfers/{id}/cancel', [StockTransferController::class, 'cancel'])->name('transfers.cancel');


    Route::get('/analytics', [AnalyticsController::class, 'index'])
        ->name('analytics.index')
        ->middleware('auth');


    // Журнал продаж
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');

// Просмотр
    Route::get('/sales/{id}', [SaleController::class, 'show'])->name('sales.show');

// Возврат
    Route::post('/sales/{id}/refund', [SaleController::class, 'refund'])->name('sales.refund');

// routes/web.php


    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');

    Route::post('/inventory/{id}/items', [InventoryController::class, 'updateItems'])->name('inventory.updateItems');
    Route::post('/inventory/{id}/import-csv', [InventoryController::class, 'importCsv'])->name('inventory.importCsv');
    Route::get('/inventory/{id}/print', [InventoryController::class, 'print'])->name('inventory.print');

    Route::post('/inventory/{id}/apply', [InventoryController::class, 'apply'])->name('inventory.apply');
    Route::post('/inventory/{id}/cancel', [InventoryController::class, 'cancel'])->name('inventory.cancel');



    Route::get('/product-search', [\App\Http\Controllers\ProductSearchController::class, 'index'])
        ->name('product.search');

    Route::get('/product-search/result', [\App\Http\Controllers\ProductSearchController::class, 'search'])
        ->name('product.search.result');


    Route::get('api/products/by-barcode', [ProductSearchController::class, 'findByBarcode']);
    Route::post('/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login'); // или на нужную страницу
    })->name('logout');


});

