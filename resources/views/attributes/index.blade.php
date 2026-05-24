@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4">Справочник параметров товара</h2>

        <div class="card mb-4">
            <div class="card-body">

                <form method="POST" action="{{ route('attributes.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="Название параметра (Например: Вес)" required>
                    </div>

                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="string">Строка</option>
                            <option value="number">Число</option>
                            <option value="color">Цвет</option>
                            <option value="date">Дата</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary w-100">Добавить</button>
                    </div>
                </form>


            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Название параметра</th>
                        <th width="100"></th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($attributes as $attr)
                        <tr>
                          <td>{{ $attr->name }}</td>
                            <td>{{ ucfirst($attr->type) }}</td>
                            <td>
                                <form method="POST" action="{{ route('attributes.delete', $attr->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>

                </table>

            </div>
        </div>

    </div>
@endsection
