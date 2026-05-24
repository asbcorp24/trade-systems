@extends('layouts.app')

@section('content')
    <div class="container">

        <h2>📄 Приёмка товара № {{ $receipt->document_number }}</h2>
        <p class="text-muted">
            {{ \Carbon\Carbon::parse($receipt->document_date)->format('d.m.Y H:i') }}
        </p>

        <div class="card mb-4">
            <div class="card-body">

                <h5>Информация о документе</h5>

                <p><b>Склад:</b> {{ $receipt->warehouse->name }}</p>

                <p><b>Поставщик:</b>
                    {{ $receipt->supplier_name ?? '—' }}
                </p>

                <p><b>Пользователь:</b>
                    {{ $receipt->user->name ?? '—' }}
                </p>

                @if($receipt->comment)
                    <p><b>Комментарий:</b> {{ $receipt->comment }}</p>
                @endif

                <a href="{{ route('receipts.print', $receipt->id) }}"
                   class="btn btn-primary" target="_blank">
                    🖨 Печать накладной
                </a>

            </div>
        </div>

        <h4>Состав документа</h4>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Товар</th>
                    <th>Кол-во</th>
                    <th>Цена</th>
                    <th>Сумма</th>
                    <th>Состояние</th>
                    <th>Партия</th>
                    <th>Годен до</th>
                </tr>
                </thead>
                <tbody>
                @foreach($receipt->items as $i => $item)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2, ',', ' ') }} ₽</td>
                        <td>{{ number_format($item->unit_price * $item->quantity, 2, ',', ' ') }} ₽</td>
                        <td>{{ $item->is_used ? 'б/у' : 'Новый' }}</td>
                        <td>{{ $item->batch }}</td>
                        <td>{{ $item->expiry_date }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
