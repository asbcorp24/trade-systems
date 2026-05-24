@extends('layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">📥 Журнал приёмки товара</h2>

        {{-- Фильтры --}}
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Склад</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Все</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id')==$w->id?'selected':'' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Пользователь</label>
                <select name="user_id" class="form-select">
                    <option value="">Все</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id')==$u->id?'selected':'' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Дата от</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Дата до</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Поиск по номеру</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}">
            </div>

            <div class="col-md-12">
                <button class="btn btn-primary">Фильтровать</button>
                <a href="{{ route('receipts.journal') }}" class="btn btn-secondary">Сброс</a>
            </div>
        </form>

        {{-- Таблица --}}
        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Номер</th>
                <th>Дата</th>
                <th>Склад</th>
                <th>Поставщик</th>
                <th>Пользователь</th>
                <th>Комментарий</th>
                <th>Сумма</th>
            </tr>
            </thead>
            <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ $r->id }}</td>

                    <td>
                        <a href="{{ route('receipts.show', $r->id) }}">
                            {{ $r->document_number }}
                        </a>
                    </td>

                    <td>{{ \Carbon\Carbon::parse($r->document_date)->format('d.m.Y H:i') }}</td>

                    <td>{{ $r->warehouse->name }}</td>

                    <td>{{ $r->supplier_name ?? '—' }}</td>

                    <td>{{ $r->user->name ?? '—' }}</td>

                    <td>{{ $r->comment }}</td>

                    <td>
                        {{ number_format(
                            $r->items->sum(fn($i)=>$i->quantity * $i->unit_price),
                            2, ',', ' '
                        ) }} ₽
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div>
            {{ $rows->links() }}
        </div>

    </div>
@endsection
