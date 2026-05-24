@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4">Добавить товар (Номенклатура)</h2>

        <div class="card">
            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Штрихкод (опционально)</label>
                        <input type="text" id="barcode" class="form-control">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Название товара *</label>
                        <input type="text" id="name" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Единица *</label>
                        <select id="unit" class="form-select">
                            <option value="pcs">Штуки</option>
                            <option value="l">Литры</option>
                            <option value="m">Метры</option>
                            <option value="kg">Килограммы</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Категория</label>
                        <select id="category_id" class="form-select">
                            <option value="">Без категории</option>
                            @foreach(\App\Models\Category::all() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Базовая цена</label>
                        <input type="number" id="base_price" step="0.01" class="form-control">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Описание</label>
                        <textarea id="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Фото товара</label>
                        <input type="file" id="photo" class="form-control">
                        <img id="preview" style="display:none;max-width:100%;margin-top:10px;">
                    </div>

                    <div class="col-12 mt-3">
                        <button class="btn btn-primary" id="saveProduct">💾 Сохранить товар</button>
                    </div>

                </div>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){

            // === превью фото ===
            $('#photo').on('change', function(){
                let r = new FileReader();
                r.onload = e => {
                    $('#preview').attr('src', e.target.result).show();
                };
                r.readAsDataURL(this.files[0]);
            });

            // === AJAX сохранение ===
            $('#saveProduct').click(function(){

                let formData = new FormData();
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                formData.append('barcode', $('#barcode').val());
                formData.append('name', $('#name').val());
                formData.append('unit', $('#unit').val());
                formData.append('category_id', $('#category_id').val());
                formData.append('base_price', $('#base_price').val());
                formData.append('description', $('#description').val());

                if ($('#photo')[0].files.length) {
                    formData.append('photo', $('#photo')[0].files[0]);
                }

                $.ajax({
                    url: "{{ route('products.store.ajax') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,

                    success: function(res){
                        alert("Товар успешно создан!");
                        window.location.href = "/products";
                    },

                    error: function(xhr){
                        let errors = xhr.responseJSON.errors;
                        let msg = "";
                        $.each(errors, function(k, v){ msg += v + "\n"; });
                        alert(msg);
                    }
                });

            });

        });
    </script>
@endpush
