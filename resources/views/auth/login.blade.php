@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 400px;">
        <h3 class="mb-3">Вход</h3>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="/login">
            @csrf

            <label class="form-label">Логин</label>
            <input class="form-control" name="login" required>

            <label class="form-label mt-3">Пароль</label>
            <input class="form-control" name="password" type="password" required>

            <button class="btn btn-primary w-100 mt-3">Войти</button>
        </form>
    </div>
@endsection
