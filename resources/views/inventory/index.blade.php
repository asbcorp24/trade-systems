@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>📋 Инвентаризации</h2>

        <div class="mb-3">
            <a href="{{ route('inventory.create') }}" class="btn btn-primary">+ Новая инвентаризация</a>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="warehouse_id" class="form-select">
                    <option value="">Все склады</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Все статусы</option>
                    <option value="draft" @selected(request('status')=='draft')>Черновик</option>
                    <option value="applied" @selected(request('status')=='applied')>Проведена</option>
                    <option value="cancelled" @selected(request('status')=='cancelled')>Отменена</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-secondary">Фильтр</button>
            </div>
        </form>

        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>ID</th>
                <th>Номер</th>
                <th>Дата</th>
                <th>Склад</th>
                <th>Пользователь</th>
                <th>Статус</th>
                <th>Комментарий</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $inv)
                <tr>
                    <td>{{ $inv->id }}</td>
                    <td>{{ $inv->document_number }}</td>
                    <td>{{ $inv->document_date->format('d.m.Y H:i') }}</td>
                    <td>{{ $inv->warehouse->name ?? '—' }}</td>
                    <td>{{ $inv->user->name ?? '—' }}</td>
                    <td>
                        @if($inv->isDraft())
                            <span class="badge bg-secondary">Черновик</span>
                        @elseif($inv->isApplied())
                            <span class="badge bg-success">Проведена</span>
                        @else
                            <span class="badge bg-danger">Отменена</span>
                        @endif
                    </td>
                    <td>{{ $inv->comment }}</td>
                    <td>
                        <a href="{{ route('inventory.show', $inv->id) }}" class="btn btn-sm btn-outline-primary">Открыть</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Инвентаризаций пока нет</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $rows->links() }}
    </div>
@endsection
