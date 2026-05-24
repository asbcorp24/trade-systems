@extends('layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">🔍 Поиск товара</h2>

        {{-- Поиск --}}
        <form action="{{ route('product.search.result') }}" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="query" class="form-control"
                       placeholder="Введите штрих-код или название товара"
                       value="{{ $query ?? '' }}" required id="product_barcode">
                <button class="btn btn-outline-primary"
                        onclick="BarcodeScanner.open('#product_barcode')">
                    📷
                </button>
                <button class="btn btn-primary">Поиск</button>
            </div>
        </form>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif


        {{-- Найденный товар --}}
        @isset($product)
            <div class="card mb-4">
                <div class="card-body">
                    <h4>{{ $product->name }}</h4>
                    <p>Штрих-код: <strong>{{ $product->barcode }}</strong></p>
                </div>
            </div>

            <h4 class="mt-4">📦 Остатки по складам</h4>

            <table class="table table-bordered mt-3">
                <thead>
                <tr>
                    <th>Склад</th>
                    <th>Кол-во</th>
                    <th colspan="3" class="text-center">Действия</th>
                </tr>
                </thead>

                <tbody>
                @forelse($stock as $row)
                    <tr>
                        <td>{{ $row['warehouse'] }}</td>
                        <td>{{ number_format($row['qty'], 3) }}</td>

                        {{-- Движение --}}
                        <td width="150">
                            <a href="/stock-movements?product_id={{ $product->id }}"
                               class="btn btn-outline-primary btn-sm w-100">
                                Движение
                            </a>
                        </td>

                        {{-- Перемещение --}}
                        <td width="150">
                            <a href="/stock/transfers/create?product_id={{ $product->id }}&from={{ $row['warehouse_id'] }}"
                               class="btn btn-outline-warning btn-sm w-100">
                                Перемещение
                            </a>
                        </td>

                        {{-- Списание --}}
                        <td width="150">
                            <a href="/writeoff/create?product_id={{ $product->id }}&warehouse_id={{ $row['warehouse_id'] }}"
                               class="btn btn-outline-danger btn-sm w-100">
                                Списание
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Остатков нет</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        @endisset

    </div>
@endsection
