@extends('layouts.app')

@section('content')
    <div class="container">

        <h2>📄 Перемещение {{ $transfer->document_number }}</h2>
        <p class="text-muted">{{ $transfer->document_date->format('d.m.Y H:i') }}</p>

        <div class="card mb-4">
            <div class="card-body">

                <h5>Информация</h5>
                <p><b>Откуда:</b>
                    @if($transfer->fromWarehouse)
                        🏭 {{ $transfer->fromWarehouse->name }}
                    @else
                        🏬 {{ $transfer->fromStore->name }}
                    @endif
                </p>

                <p><b>Куда:</b>
                    @if($transfer->toWarehouse)
                        🏭 {{ $transfer->toWarehouse->name }}
                    @else
                        🏬 {{ $transfer->toStore->name }}
                    @endif
                </p>

                <p><b>Пользователь:</b> {{ $transfer->user->name ?? '—' }}</p>

                @if($transfer->comment)
                    <p><b>Комментарий:</b> {{ $transfer->comment }}</p>
                @endif

                <a href="{{ route('transfers.print', $transfer->id) }}" class="btn btn-primary" target="_blank">
                    🖨 Печать накладной
                </a>

            </div>
        </div>

        <h4>Состав перемещения</h4>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Товар</th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th>Партия</th>
                    <th>Годен до</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transfer->items as $i => $item)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit_price }}</td>
                        <td>{{ $item->batch }}</td>
                        <td>{{ $item->expiry_date }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
