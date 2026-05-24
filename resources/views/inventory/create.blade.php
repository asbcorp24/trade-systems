@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>🧾 Новая инвентаризация</h2>

        <form action="{{ route('inventory.store') }}" method="POST" class="mt-3">
            @csrf

            <div class="mb-3">
                <label class="form-label">Склад</label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">-- выберите склад --</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Комментарий</label>
                <input type="text" name="comment" class="form-control" value="{{ old('comment') }}">
            </div>

            <button class="btn btn-primary">Создать и заполнить по текущим остаткам</button>
            <a href="{{ route('inventory.index') }}" class="btn btn-link">Отмена</a>
        </form>
    </div>
@endsection
