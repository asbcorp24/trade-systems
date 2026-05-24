@extends('layouts.app')

@section('content')
    <div class="container">

        <h2>📦 Движение товара: {{ $product->name }}</h2>

        <table class="table table-bordered table-hover mt-4">
            <thead class="table-light">
            <tr>
                <th>Дата</th>
                <th>Документ</th>
                <th>Тип</th>
                <th>Склад / Перемещение</th>
                <th>Кол-во</th>
                <th>Остаток</th>
                <th>Пользователь</th>
            </tr>
            </thead>
            <tbody>

            @foreach($rows as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r['date'])->format('d.m.Y H:i') }}</td>

                    <td>
                        <a href="{{ $r['doc_link'] }}">
                            {{ $r['document'] }}
                        </a>
                    </td>

                    <td>{{ $r['type'] }}</td>

                    <td>{{ $r['warehouse'] }}</td>

                    <td class="{{ $r['qty'] > 0 ? 'text-success' : 'text-danger' }}">
                        {{ $r['qty'] }}
                    </td>

                    <td><b>{{ $r['balance'] }}</b></td>

                    <td>{{ $r['user'] }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>

    </div>
@endsection

