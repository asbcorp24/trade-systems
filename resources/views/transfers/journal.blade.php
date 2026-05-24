@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">📦 Журнал перемещений</h2>

        {{-- Фильтры --}}
        <form method="GET" class="row g-3 mb-4">

            <div class="col-md-3">
                <label class="form-label">Откуда</label>
                <select name="from_type" class="form-select">
                    <option value="">Все</option>
                    <option value="warehouse" {{ request('from_type')=='warehouse'?'selected':'' }}>Склад</option>
                    <option value="store" {{ request('from_type')=='store'?'selected':'' }}>Магазин</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Куда</label>
                <select name="to_type" class="form-select">
                    <option value="">Все</option>
                    <option value="warehouse" {{ request('to_type')=='warehouse'?'selected':'' }}>Склад</option>
                    <option value="store" {{ request('to_type')=='store'?'selected':'' }}>Магазин</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Пользователь</label>
                <select name="user_id" class="form-select">
                    <option value="">Все</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id')==$u->id ? 'selected':'' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Поиск по номеру</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}">
            </div>

            {{-- 🔥 Новые фильтры по датам --}}
            <div class="col-md-3">
                <label class="form-label">Дата от</label>
                <input type="date" name="date_from" class="form-control"
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Дата до</label>
                <input type="date" name="date_to" class="form-control"
                       value="{{ request('date_to') }}">
            </div>

            <div class="col-md-12">
                <button class="btn btn-primary">Фильтровать</button>
                <a href="{{ route('transfers.journal') }}" class="btn btn-secondary">Сброс</a>
            </div>
        </form>

        {{-- Таблица --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Номер</th>
                    <th>Дата</th>
                    <th>Откуда</th>
                    <th>Куда</th>
                    <th>Создал</th>
                    <th>Комментарий</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td><a href="{{ route('transfers.show', $row->id) }}">{{ $row->document_number }}</a></td>
                        <td>{{ $row->document_date }}</td>

                        <td>
                            @if($row->fromWarehouse)
                                🏭 Склад: {{ $row->fromWarehouse->name }}
                            @else
                                🏬 Магазин: {{ $row->fromStore->name }}
                            @endif
                        </td>

                        <td>
                            @if($row->toWarehouse)
                                🏭 Склад: {{ $row->toWarehouse->name }}
                            @else
                                🏬 Магазин: {{ $row->toStore->name }}
                            @endif
                        </td>

                        <td>{{ $row->user->name ?? '—' }}</td>
                        <td>{{ $row->comment }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        <div class="mt-3">
            {{ $rows->links() }}
        </div>

    </div>
@endsection
