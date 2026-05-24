@extends('layouts.app')

@section('content')
    <div class="container">

        <h2>Ваши уведомления</h2>

        <ul class="list-group">
            @foreach($notifications as $n)
                <li class="list-group-item">
                    <b>{{ $n->message }}</b>
                    <div class="text-muted small">{{ $n->created_at->format('d.m.Y H:i') }}</div>
                </li>
            @endforeach
        </ul>

    </div>
@endsection
