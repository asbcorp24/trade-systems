
@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4">🏭 Склады</h2>

        <button class="btn btn-primary mb-3" onclick="openAddWarehouse()">
            ➕ Добавить склад
        </button>

        <table class="table table-bordered" id="warehouseTable">
            <thead>
            <tr>
                <th>Название</th>
                <th>Код</th>
                <th width="150">Действия</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

    @include('warehouses.modal')

@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            loadWarehouses();
        });

        // Загрузка складов
        function loadWarehouses() {
            $.get("/api/warehouses", function(res){
                let html = "";
                res.forEach(w => {
                    html += `
                <tr data-id="${w.id}">
                    <td>${w.name}</td>
                    <td>${w.code}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="openEditWarehouse(${w.id}, '${w.name}', '${w.code}')">✏</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteWarehouse(${w.id})">🗑</button>
                    </td>
                </tr>
            `;
                });
                $("#warehouseTable tbody").html(html);
            });
        }

        // Добавление
        function openAddWarehouse() {
            $("#whId").val("");
            $("#whName").val("");
            $("#whCode").val("");
            $("#modalTitle").text("Добавить склад");
            $("#warehouseModal").modal("show");
        }

        // Редактирование
        function openEditWarehouse(id, name, code) {
            $("#whId").val(id);
            $("#whName").val(name);
            $("#whCode").val(code);
            $("#modalTitle").text("Редактировать склад");
            $("#warehouseModal").modal("show");
        }

        // Сохранение
        $("#saveWarehouse").click(function(){
            let id = $("#whId").val();
            let name = $("#whName").val();
            let code = $("#whCode").val();

            if (!name || !code) return alert("Заполните все поля!");

            let url = id ? `/api/warehouses/${id}` : "/api/warehouses";

            $.post(url, {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name, code
            }, function(){
                $("#warehouseModal").modal("hide");
                loadWarehouses();
            });
        });

        // Удаление
        function deleteWarehouse(id) {
            if (!confirm("Удалить склад?")) return;

            $.ajax({
                url: `/api/warehouses/${id}`,
                method: "DELETE",
                data: {_token: $('meta[name="csrf-token"]').attr('content')},
                success: function(){
                    loadWarehouses();
                }
            });
        }
    </script>
@endpush
