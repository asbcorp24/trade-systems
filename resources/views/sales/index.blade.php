@extends('layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-3">🧾 Журнал продаж</h2>

        <div class="card mb-3">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Магазин</label>
                        <select id="store" class="form-select">
                            <option value="">Все</option>
                            @foreach($stores as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Дата</label>
                        <input type="date" id="date" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Номер документа</label>
                        <input type="text" id="number" class="form-control">
                    </div>

                    <div class="col-md-3 d-grid">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary" onclick="filterSales()">🔍 Найти</button>
                    </div>
                </div>

            </div>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>Документ</th>
                <th>Дата</th>
                <th>Магазин</th>
                <th>Сумма</th>
                <th>Кассир</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($sales as $s)
                <tr>
                    <td>{{ $s->document_number }}</td>
                    <td>{{ $s->document_date }}</td>
                    <td>{{ $s->store->name }}</td>
                    <td>{{ number_format($s->total_amount,2) }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td>
                        <a href="/sales/{{ $s->id }}" class="btn btn-sm btn-secondary">Открыть</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $sales->links() }}

    </div>
@endsection
