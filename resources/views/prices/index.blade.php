@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">История цен</h2>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                <tr>
                    <th>Дата</th>
                    <th>Товар</th>
                    <th>Тип</th>
                    <th>Старая</th>
                    <th>Новая</th>
                    <th>Скидка</th>
                    <th>Пользователь</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $row->product->name ?? $row->product_id }}</td>
                        <td>{{ $row->price_type }}</td>
                        <td>{{ $row->old_price ?? '—' }}</td>
                        <td>{{ $row->new_price }}</td>
                        <td>{{ $row->discount_percent }}%</td>
                        <td>{{ $row->user_id ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
@endsection
