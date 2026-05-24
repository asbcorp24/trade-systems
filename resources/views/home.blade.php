@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4">Добро пожаловать в систему Склад-Торговля</h2>

        <!-- Первая строка — основные операции -->
        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <a href="/receipts/create" class="text-decoration-none">
                    <div class="card shadow-sm border-primary">
                        <div class="card-body text-center p-4">
                            <h4 class="text-primary">📦 Приёмка товара</h4>
                            <p class="text-muted small">Сканирование штрихкодов, создание приходных документов</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="/stock/transfers/create" class="text-decoration-none">
                    <div class="card shadow-sm border-success">
                        <div class="card-body text-center p-4">
                            <h4 class="text-success">🔄 Перемещение</h4>
                            <p class="text-muted small">Передача товаров между складами / магазинами</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="/stock" class="text-decoration-none">
                    <div class="card shadow-sm border-warning">
                        <div class="card-body text-center p-4">
                            <h4 class="text-warning">📊 Остатки</h4>
                            <p class="text-muted small">Текущие остатки, партии, сроки годности</p>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <!-- Вторая строка — номенклатура и каталоги -->
        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <a href="/products" class="text-decoration-none">
                    <div class="card shadow-sm border-info">
                        <div class="card-body text-center p-4">
                            <h4 class="text-info">🏷️ Товары</h4>
                            <p class="text-muted small">Справочник товаров, цены, характеристики</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="/products/create" class="text-decoration-none">
                    <div class="card shadow-sm border-secondary">
                        <div class="card-body text-center p-4">
                            <h4 class="text-secondary">➕ Добавить товар</h4>
                            <p class="text-muted small">Создание карточки товара, добавление штрихкода</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="/categories" class="text-decoration-none">
                    <div class="card shadow-sm border-dark">
                        <div class="card-body text-center p-4">
                            <h4 class="text-dark">📁 Категории</h4>
                            <p class="text-muted small">Группы товаров, дерево категорий</p>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <!-- Статистика -->
        <div class="row mt-5">
            <h4 class="mb-3">Быстрая статистика</h4>

            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-6">{{ \App\Models\Product::count() }}</div>
                        <div class="text-muted">Товаров</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-6">{{ \App\Models\Warehouse::count() }}</div>
                        <div class="text-muted">Складов</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-6">{{ \App\Models\GoodsReceipt::count() }}</div>
                        <div class="text-muted">Приходов</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-6">{{ \App\Models\StockTransfer::count() }}</div>
                        <div class="text-muted">Перемещений</div>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
