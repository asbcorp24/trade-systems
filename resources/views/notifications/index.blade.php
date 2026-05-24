@extends('layouts.app')

@section('content')
    <div class="container">

        <h2>Все уведомления</h2>

        <form method="POST" action="/notifications/read-all">
            @csrf
            <button class="btn btn-primary mb-3">Отметить все как прочитанные</button>
        </form>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Дата</th>
                <th>Сообщение</th>
                <th>Тип</th>
                <th>Товар</th>
                <th>Склад</th>
                <th>Партия</th>
            </tr>
            </thead>
            <tbody>
            @foreach($notifications as $n)
                <tr>
                    <td>{{ $n->created_at }}</td>
                    <td>{{ $n->message }}</td>
                    <td>{{ $n->type }}</td>
                    <td>{{ $n->product->name ?? '—' }}</td>
                    <td>{{ $n->warehouse->name ?? '—' }}</td>
                    <td>{{ $n->batch ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $notifications->links() }}

    </div>
@endsection
