@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Товар в пути</h2>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                <tr>
                    <th>Груз</th>
                    <th>Откуда</th>
                    <th>Куда</th>
                    <th>Товар</th>
                    <th>Отгружено</th>
                    <th>Принято</th>
                    <th>В пути</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $transfer)
                    @foreach($transfer->items as $item)
                        @php $left = (int)$item->quantity - (int)$item->received_quantity; @endphp
                        @if($left <= 0) @continue @endif
                        <tr>
                            <td>{{ $transfer->document_number }}</td>
                            <td>{{ $transfer->fromWarehouse->name ?? $transfer->fromStore->name ?? '-' }}</td>
                            <td>{{ $transfer->toWarehouse->name ?? $transfer->toStore->name ?? '-' }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ (int)$item->quantity }}</td>
                            <td>{{ (int)$item->received_quantity }}</td>
                            <td class="fw-bold">{{ $left }}</td>
                            <td><a class="btn btn-sm btn-success" href="{{ route('transfers.receive', $transfer->id) }}">Принять</a></td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
@endsection
