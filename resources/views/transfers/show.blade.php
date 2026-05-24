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
                <p><b>Статус:</b>
                    @if($transfer->status === 'received')
                        <span class="badge bg-success">Принят</span>
                    @elseif($transfer->status === 'partially_received')
                        <span class="badge bg-warning text-dark">Частично принят</span>
                    @else
                        <span class="badge bg-info text-dark">В пути</span>
                    @endif
                </p>

                @if($transfer->comment)
                    <p><b>Комментарий:</b> {{ $transfer->comment }}</p>
                @endif

                <a href="{{ route('transfers.print', $transfer->id) }}" class="btn btn-primary" target="_blank">
                    🖨 Печать накладной
                </a>
                @if($transfer->status !== 'received')
                    <a href="{{ route('transfers.receive', $transfer->id) }}" class="btn btn-success">
                        Принять груз
                    </a>
                @endif

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
                    <th>Принято</th>
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
                        <td>{{ (int)$item->received_quantity }}</td>
                        <td>{{ $item->unit_price }}</td>
                        <td>{{ $item->batch }}</td>
                        <td>{{ $item->expiry_date }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($transfer->discrepancies->count())
            <h4>Акт расхождений</h4>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                    <tr>
                        <th>Товар</th>
                        <th>Отгружено</th>
                        <th>Принято</th>
                        <th>Недостача</th>
                        <th>Излишек</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($transfer->discrepancies as $d)
                        <tr>
                            <td>{{ $d->product->name }}</td>
                            <td>{{ $d->shipped_quantity }}</td>
                            <td>{{ $d->received_quantity }}</td>
                            <td class="text-danger">{{ $d->shortage_quantity }}</td>
                            <td class="text-warning">{{ $d->surplus_quantity }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
@endsection
