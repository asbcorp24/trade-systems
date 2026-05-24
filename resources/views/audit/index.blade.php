@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Журнал действий</h2>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                <tr>
                    <th>Дата</th>
                    <th>Событие</th>
                    <th>Описание</th>
                    <th>Пользователь</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $log->event }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->user_id ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
@endsection
