@extends('layouts.app')

@section('content')
    <div class="container">

        <h2>📏 Единицы измерения</h2>

        <button class="btn btn-primary my-3" onclick="openAddModal()">➕ Добавить</button>

        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>Код</th>
                <th>Название</th>
                <th width="150">Действия</th>
            </tr>
            </thead>
            <tbody id="unitsTable">
            @foreach($units as $unit)
                <tr data-id="{{ $unit->id }}">
                    <td>{{ $unit->code }}</td>
                    <td>{{ $unit->name }}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="openEditModal({{ $unit->id }}, '{{ $unit->code }}', '{{ $unit->name }}')">✏</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteUnit({{ $unit->id }})">🗑</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="unitModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Единица измерения</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="unit_id">

                    <label>Код</label>
                    <input class="form-control" id="unit_code">

                    <label class="mt-2">Название</label>
                    <input class="form-control" id="unit_name">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button class="btn btn-success" onclick="saveUnit()">Сохранить</button>
                </div>

            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script>
        function openAddModal() {
            $('#unit_id').val('');
            $('#unit_code').val('');
            $('#unit_name').val('');
            new bootstrap.Modal('#unitModal').show();
        }

        function openEditModal(id, code, name) {
            $('#unit_id').val(id);
            $('#unit_code').val(code);
            $('#unit_name').val(name);
            new bootstrap.Modal('#unitModal').show();
        }

        function saveUnit() {
            let id = $('#unit_id').val();

            let data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                code: $('#unit_code').val(),
                name: $('#unit_name').val(),
            };

            let url = id ? `/api/units/${id}` : '/api/units';

            $.post(url, data, function(res){
                if (res.success) location.reload();
            });
        }

        function deleteUnit(id) {
            if (!confirm("Удалить ед. изм.?")) return;

            $.ajax({
                url: `/api/units/${id}`,
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: res => res.success ? location.reload() : null
            });
        }
    </script>
@endpush
