@extends('layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">📚 Журнал движений товаров</h2>

        {{-- Фильтры --}}
        <form class="row g-3 mb-4" method="GET">
            <div class="col-md-4">
                <label class="form-label">Товар</label>
                <select name="product_id" class="form-select">
                    <option value="">Все</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id')==$p->id ? 'selected':'' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Дата от</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Дата до</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-12">
                <button class="btn btn-primary">Фильтровать</button>
                <a href="{{ route('stock.movements') }}" class="btn btn-secondary">Сброс</a>
            </div>
        </form>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr>
                <th>Дата</th>
                <th>Товар</th>
                <th>Документ</th>
                <th>Тип</th>
                <th>Склад / Направление</th>
                <th>Кол-во</th>
                <th>Пользователь</th>
            </tr>
            </thead>
            <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r['date'])->format('d.m.Y H:i') }}</td>

                    <td>
                        <a href="{{ route('products.movement',$r['product_id']) }}">
                            {{ $r['product'] }}
                        </a>
                    </td>

                    <td><a href="{{ $r['doc_link'] }}">{{ $r['document'] }}</a></td>
                    <td>{{ $r['type'] }}</td>
                    <td>{{ $r['warehouse'] }}</td>

                    <td class="{{ $r['qty']>0?'text-success':'text-danger' }}">
                        {{ $r['qty'] }}
                    </td>

                    <td>{{ $r['user'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
@endsection
