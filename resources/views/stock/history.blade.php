@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>История движений: {{ $product->name }}</h3>

        <table class="table table-bordered mt-3">
            <thead>
            <tr>
                <th>Дата</th>
                <th>Документ</th>
                <th>Направление</th>
                <th>Склад/Магазин</th>
                <th>Кол-во</th>
                <th>Цена</th>
                <th>Партия / срок</th>
            </tr>
            </thead>
            <tbody>
            @foreach($movements as $m)
                <tr>
                    <td>{{ $m->created_at }}</td>
                    <td>{{ $m->document_type }} #{{ $m->document_id }}</td>
                    <td>{{ $m->direction === 'in' ? 'Приход' : 'Расход' }}</td>
                    <td>
                        @if($m->warehouse)
                            🏭 {{ $m->warehouse->name }}
                        @elseif($m->store)
                            🏬 {{ $m->store->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $m->quantity }}</td>
                    <td>{{ $m->unit_price }}</td>
                    <td>
                        {{ $m->batch ?? '' }}
                        @if($m->expiry_date) (годен до {{ $m->expiry_date }}) @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $movements->links() }}
    </div>
@endsection

