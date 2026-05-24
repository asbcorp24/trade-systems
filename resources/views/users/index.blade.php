@extends('layouts.app')

@section('content')
    <div class="container">

        <h3 class="mb-3">Пользователи</h3>

        <button class="btn btn-success mb-3" onclick="openCreate()">➕ Новый пользователь</button>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Логин</th>
                <th>Имя</th>
                <th>Роль</th>
                <th width="140"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $u)
                <tr id="u_{{ $u->id }}">
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->login }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->role }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="edit({{ $u->id }})">✏</button>
                        <button class="btn btn-sm btn-danger" onclick="del({{ $u->id }})">🗑</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>


    {{-- MODAL --}}
    <div class="modal fade" id="userModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header"><h5>Пользователь</h5></div>

                <div class="modal-body">
                    <input type="hidden" id="user_id">

                    <label>Имя</label>
                    <input class="form-control" id="name">

                    <label class="mt-2">Логин</label>
                    <input class="form-control" id="login">

                    <label class="mt-2">Пароль (оставьте пустым — без изменений)</label>
                    <input type="password" class="form-control" id="password">

                    <label class="mt-2">Роль</label>
                    <select id="role" class="form-select">
                        <option value="seller">Продавец</option>
                        <option value="storekeeper">Кладовщик</option>
                        <option value="admin">Админ</option>
                        <option value="superadmin">Суперадмин</option>
                    </select>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button class="btn btn-success" onclick="saveUser()">Сохранить</button>
                </div>

            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script>
        function openCreate() {
            $("#user_id").val('');
            $("#name, #login, #password").val('');
            $("#role").val('user');
            $("#userModal").modal('show');
        }

        function edit(id) {
            let row = $("#u_" + id).children();

            $("#user_id").val(id);
            $("#login").val(row.eq(1).text());
            $("#name").val(row.eq(2).text());
            $("#role").val(row.eq(3).text());
            $("#password").val('');

            $("#userModal").modal('show');
        }

        function saveUser() {
            let id = $("#user_id").val();

            $.post("/users" + (id ? "/" + id : ""), {
                _token: $('meta[name="csrf-token"]').val(),
                name: $("#name").val(),
                login: $("#login").val(),
                password: $("#password").val(),
                role: $("#role").val(),
            }, function(res){
                if (res.success) location.reload();
            });
        }

        function del(id) {
            if (!confirm("Удалить пользователя?")) return;

            $.ajax({
                url: "/users/" + id,
                method: "DELETE",
                data: {_token: $('meta[name="csrf-token"]').val()},
                success: res => location.reload()
            });
        }
    </script>
@endpush
