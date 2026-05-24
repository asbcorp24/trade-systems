@extends('layouts.app')

@section('content')
    <div class="container">

        <h2>Настройки уведомлений</h2>

        @if(session('success'))
            <div class="alert alert-success">Настройки обновлены</div>
        @endif

        <form action="/notifications/settings" method="POST">
            @csrf

            <label class="form-label">Получать уведомления в Telegram:</label>
            <select name="telegram_subscribed" class="form-select" style="width:200px;">
                <option value="1" @if($user->telegram_subscribed) selected @endif>Да</option>
                <option value="0" @if(!$user->telegram_subscribed) selected @endif>Нет</option>
            </select>

            <div class="mt-3">
                <button class="btn btn-primary">Сохранить</button>
            </div>

            @if(!$user->telegram_chat_id)
                <p class="mt-3 text-muted">
                    Чтобы активировать Telegram-уведомления, напишите боту:
                    <br>
                    👉 <b>@ТвойБот</b>
                    <br><br>
                    И введите:<br>
                    <code>/start</code><br>
                    Затем: <code>логин пароль</code>
                </p>
            @endif
        </form>

    </div>
@endsection
