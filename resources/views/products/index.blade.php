@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Товары (Номенклатура)</h2>
            <a href="/products/create" class="btn btn-primary">➕ Добавить товар</a>
        </div>

        <!-- Фильтры -->
        <div class="card mb-3">
            <div class="card-body">

                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" id="search" class="form-control" placeholder="Поиск по названию или штрихкоду">
                    </div>

                    <div class="col-md-4">
                        <select id="filter_category" class="form-select">
                            <option value="">Все категории</option>
                            @foreach(\App\Models\Category::all() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-secondary w-100" id="resetFilters">Сбросить</button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Таблица -->
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered align-middle" id="productsTable">
                    <thead class="table-light">
                    <tr>
                        <th width="80">Фото</th>
                        <th>Название</th>
                        <th>Штрихкод</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th width="150">Действия</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <nav>
                    <ul class="pagination" id="pagination"></ul>
                </nav>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){

            function loadProducts(page = 1){

                $.get("{{ route('products.list.ajax') }}", {
                    search: $("#search").val(),
                    category_id: $("#filter_category").val(),
                    page: page
                }, function(res){

                    if(!res.success) return;

                    let tbody = $("#productsTable tbody");
                    tbody.empty();

                    res.data.data.forEach(p => {

                        let img = p.photo_path
                            ? `<img src="/storage/${p.photo_path}" width="60" class="rounded">`
                            : `<span class="text-muted">нет</span>`;

                        let category = p.category ? p.category.name : '-';

                        tbody.append(`
                    <tr>
                        <td>${img}</td>
                        <td>${p.name}</td>
                        <td>${p.barcode ?? '-'}</td>
                        <td>${category}</td>
                        <td>${p.base_price ?? '0.00'}</td>
                        <td>
                            <a href="/products/${p.id}/edit" class="btn btn-sm btn-warning">✏️</a>
                            <a href="/products/${p.id}" class="btn btn-sm btn-info">👁</a>
                        </td>

                    </tr>
                `);
                    });

                    // PAGINATION
                    let pagination = $("#pagination");
                    pagination.empty();

                    let current = res.data.current_page;
                    let last    = res.data.last_page;

                    for(let i = 1; i <= last; i++){
                        let active = i === current ? 'active' : '';
                        pagination.append(`
                    <li class="page-item ${active}">
                        <a class="page-link" href="#" onclick="loadProducts(${i});return false;">${i}</a>
                    </li>
                `);
                    }

                });
            }

            // Events
            $("#search").on("input", function(){
                loadProducts();
            });

            $("#filter_category").change(function(){
                loadProducts();
            });

            $("#resetFilters").click(function(){
                $("#search").val('');
                $("#filter_category").val('');
                loadProducts();
            });

            // Initial load
            loadProducts();
        });
    </script>
@endpush
