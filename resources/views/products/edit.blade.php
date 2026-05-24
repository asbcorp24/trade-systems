@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4">Редактирование товара: {{ $product->name }}</h2>

        <!-- ТАБЫ -->
        <ul class="nav nav-tabs mb-3" id="productTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#main">Основное</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#attributes">Параметры товара</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#gallery">Галерея</a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ================== ОСНОВНОЕ ================== --}}
            <div class="tab-pane fade show active" id="main">
                <div class="card mb-4">
                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label">Штрихкод</label>
                                <div class="input-group">
                                    <input type="text" id="barcode" class="form-control" value="{{ $product->barcode }}">
                                    <button class="btn btn-outline-primary" type="button" id="generateBarcode">Сгенерировать</button>
                                    <button class="btn btn-outline-secondary" type="button" id="printBarcode">Печать</button>
                                    <button class="btn btn-outline-success" type="button" id="printPriceTag">Ценник</button>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Название *</label>
                                <input type="text" id="name" class="form-control" value="{{ $product->name }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Единица *</label>
                                <select id="unit" class="form-select">
                                    <option value="pcs" {{ $product->unit == 'pcs' ? 'selected' : '' }}>Штуки</option>
                                    <option value="l"   {{ $product->unit == 'l' ? 'selected' : '' }}>Литры</option>
                                    <option value="m"   {{ $product->unit == 'm' ? 'selected' : '' }}>Метры</option>
                                    <option value="kg"  {{ $product->unit == 'kg' ? 'selected' : '' }}>Килограммы</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Категория</label>
                                <select id="category_id" class="form-select">
                                    <option value="">Без категории</option>
                                    @foreach(\App\Models\Category::all() as $cat)
                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Цена</label>
                                <input type="number" step="0.01" id="base_price" class="form-control" value="{{ $product->base_price }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Описание</label>
                                <textarea id="description" class="form-control" rows="4">{{ $product->description }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Основное фото</label>
                                <input type="file" id="photo" class="form-control">

                                @if($product->photo_path)
                                    <img src="/storage/{{ $product->photo_path }}" id="preview" class="mt-3 rounded" width="200">
                                @else
                                    <img id="preview" style="display:none;" width="200">
                                @endif
                            </div>

                            <div class="col-12 mt-4">
                                <button class="btn btn-success" id="saveProduct">💾 Сохранить основное</button>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            {{-- ============= ПАРАМЕТРЫ ТОВАРА ============= --}}
            <div class="tab-pane fade" id="attributes">

                <div class="card mb-4">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Параметры товара</h5>

                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttributeModal">
                                ➕ Добавить параметр
                            </button>
                        </div>

                        <table class="table table-bordered" id="productAttributesTable">
                            <thead>
                            <tr>
                                <th>Параметр</th>
                                <th>Значение</th>
                                <th>Тип</th>
                                <th width="120">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($product->attributes as $pa)
                                <tr data-id="{{ $pa->id }}">
                                    <td>{{ $pa->attribute->name }}</td>

                                    <td>
                                        @php $type = $pa->attribute->type; @endphp

                                        @if($type === 'string')
                                            <input type="text" class="form-control updateAttr"
                                                   value="{{ $pa->value }}" data-id="{{ $pa->id }}" />

                                        @elseif($type === 'number')
                                            <input type="number" class="form-control updateAttr"
                                                   value="{{ $pa->value }}" data-id="{{ $pa->id }}" />

                                        @elseif($type === 'color')
                                            <input type="color" class="form-control updateAttr"
                                                   value="{{ $pa->value }}" data-id="{{ $pa->id }}" />

                                        @elseif($type === 'date')
                                            <input type="date" class="form-control updateAttr"
                                                   value="{{ $pa->value }}" data-id="{{ $pa->id }}" />
                                        @endif
                                    </td>

                                    <td>{{ $type }}</td>

                                    <td class="text-center">
                                        <button class="btn btn-danger btn-sm deleteAttr" data-id="{{ $pa->id }}">
                                            🗑
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>


            {{-- ============= ГАЛЕРЕЯ ============= --}}
            <div class="tab-pane fade" id="gallery">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>Дополнительные фото</h5>

                        <input type="file" id="galleryUpload" class="form-control mb-3">

                        <div class="row" id="galleryList">
                            @foreach($product->images as $img)
                                <div class="col-md-3 mb-3" id="img-{{ $img->id }}">
                                    <div class="card">
                                        <img src="/storage/{{ $img->path }}" class="card-img-top">
                                        <button class="btn btn-danger btn-sm deleteImage" data-id="{{ $img->id }}">Удалить</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="addAttributeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Добавить параметр</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="form-label">Выберите параметр</label>
                    <select id="newAttributeId" class="form-select mb-3">
                        <option value="">Выберите...</option>

                        @foreach(\App\Models\Attribute::all() as $attr)
                            @if(!$product->attributes->contains('attribute_id', $attr->id))
                                <option value="{{ $attr->id }}" data-type="{{ $attr->type }}">
                                    {{ $attr->name }} ({{ $attr->type }})
                                </option>
                            @endif
                        @endforeach
                    </select>

                    <label class="form-label">Введите значение</label>
                    <div id="newAttributeInput"></div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button class="btn btn-success" id="saveNewAttribute">Сохранить</button>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function(){

            // Превью основного фото
            $("#photo").change(function(){
                let reader = new FileReader();
                reader.onload = (e) => $("#preview").attr("src", e.target.result).show();
                reader.readAsDataURL(this.files[0]);
            });

            // Сохранение основного блока
            $("#saveProduct").click(function(){

                let fd = new FormData();
                fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
                fd.append('barcode', $("#barcode").val());
                fd.append('name', $("#name").val());
                fd.append('unit', $("#unit").val());
                fd.append('category_id', $("#category_id").val());
                fd.append('base_price', $("#base_price").val());
                fd.append('description', $("#description").val());

                if ($("#photo")[0].files.length) {
                    fd.append('photo', $("#photo")[0].files[0]);
                }

                $.ajax({
                    url: "/api/products/{{ $product->id }}",
                    method: "POST",
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(res){
                        alert("Основная информация обновлена!");
                    },
                    error: function(xhr){
                        let msg = "";
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, (k,v)=> msg += v + "\n");
                        } else {
                            msg = "Ошибка сохранения";
                        }
                        alert(msg);
                    }
                });
            });

            $('#generateBarcode').click(function(){
                $.get("{{ route('products.generate_barcode') }}", function(res) {
                    if (res.success) {
                        $('#barcode').val(res.barcode);
                    }
                });
            });

            $('#printBarcode').click(function(){
                printBarcodeLabel($('#barcode').val(), $('#name').val());
            });

            $('#printPriceTag').click(function(){
                printPriceTag($('#barcode').val(), $('#name').val(), $('#base_price').val());
            });
// ====== УСТАНОВКА INPUT ПО ТИПУ ======
            $("#newAttributeId").change(function () {
                let type = $(this).find(':selected').data('type');
                let input = "";

                if (type === "string")
                    input = `<input type="text" id="newAttrValue" class="form-control">`;

                if (type === "number")
                    input = `<input type="number" id="newAttrValue" class="form-control">`;

                if (type === "color")
                    input = `<input type="color" id="newAttrValue" class="form-control">`;

                if (type === "date")
                    input = `<input type="date" id="newAttrValue" class="form-control">`;

                $("#newAttributeInput").html(input);
            });

// ====== СОХРАНИТЬ НОВЫЙ АТРИБУТ ======
            $("#saveNewAttribute").click(function(){

                $.post("/api/products/{{ $product->id }}/attributes", {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    attribute_id: $("#newAttributeId").val(),
                    value: $("#newAttrValue").val()
                }, function(res){

                    location.reload(); // обновляем вкладку параметров

                }).fail(function(xhr){
                    alert("Ошибка при добавлении атрибута");
                });
            });

// ====== ИЗМЕНЕНИЕ ЗНАЧЕНИЯ АТРИБУТА ======
            $(".updateAttr").change(function(){

                $.post("/api/product-attributes/" + $(this).data("id"), {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    value: $(this).val()
                });
            });

// ====== УДАЛЕНИЕ АТРИБУТА ======
            $(".deleteAttr").click(function(){

                if(!confirm("Удалить параметр?")) return;

                let id = $(this).data("id");

                $.ajax({
                    url: "/api/product-attributes/" + id,
                    method: "DELETE",
                    data: {_token: $('meta[name=csrf-token]').attr('content')},
                    success: function(){
                        $("tr[data-id='" + id + "']").remove();
                    }
                });
            });


            // Загрузка доп. фото
            $("#galleryUpload").change(function(){

                let fd = new FormData();
                fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
                fd.append('image', this.files[0]);

                $.ajax({
                    url: `/api/products/{{ $product->id }}/images`,
                    method: "POST",
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(res){
                        let img = res.image;
                        $("#galleryList").append(`
                    <div class="col-md-3 mb-3" id="img-${img.id}">
                        <div class="card">
                            <img src="/storage/${img.path}" class="card-img-top">
                            <button class="btn btn-danger btn-sm deleteImage" data-id="${img.id}">Удалить</button>
                        </div>
                    </div>
                `);
                    }
                });
            });

            // Удаление доп. фото
            $(document).on("click", ".deleteImage", function(){
                let id = $(this).data("id");
                $.ajax({
                    url: `/api/product-images/${id}`,
                    method: "DELETE",
                    data: {_token: $('meta[name="csrf-token"]').attr('content')},
                    success: function(){
                        $("#img-" + id).remove();
                    }
                });
            });

        });

        function ean13Pattern(code) {
            const leftOdd = {
                0:'0001101',1:'0011001',2:'0010011',3:'0111101',4:'0100011',
                5:'0110001',6:'0101111',7:'0111011',8:'0110111',9:'0001011'
            };
            const leftEven = {
                0:'0100111',1:'0110011',2:'0011011',3:'0100001',4:'0011101',
                5:'0111001',6:'0000101',7:'0010001',8:'0001001',9:'0010111'
            };
            const right = {
                0:'1110010',1:'1100110',2:'1101100',3:'1000010',4:'1011100',
                5:'1001110',6:'1010000',7:'1000100',8:'1001000',9:'1110100'
            };
            const parity = {
                0:'OOOOOO',1:'OOEOEE',2:'OOEEOE',3:'OOEEEO',4:'OEOOEE',
                5:'OEEOOE',6:'OEEEOO',7:'OEOEOE',8:'OEOEEO',9:'OEEOEO'
            };
            const digits = code.split('').map(Number);
            let bits = '101';
            for (let i = 1; i <= 6; i++) {
                bits += parity[digits[0]][i - 1] === 'O' ? leftOdd[digits[i]] : leftEven[digits[i]];
            }
            bits += '01010';
            for (let i = 7; i <= 12; i++) bits += right[digits[i]];
            return bits + '101';
        }

        function barcodeSvg(code) {
            const bits = ean13Pattern(code);
            let bars = '';
            for (let i = 0; i < bits.length; i++) {
                if (bits[i] === '1') bars += `<rect x="${i * 2}" y="0" width="2" height="90"/>`;
            }
            return `<svg width="220" height="130" viewBox="0 0 190 120" xmlns="http://www.w3.org/2000/svg">
                <g>${bars}</g><text x="95" y="112" font-size="16" text-anchor="middle">${code}</text>
            </svg>`;
        }

        function printBarcodeLabel(code, name) {
            if (!/^\d{13}$/.test(code || '')) {
                alert('Сначала укажите или сгенерируйте EAN-13 штрихкод.');
                return;
            }
            const w = window.open('', '_blank', 'width=420,height=320');
            w.document.write(`<html><head><title>Печать штрихкода</title></head><body style="font-family:Arial;text-align:center">
                <div style="font-size:14px;margin-bottom:8px">${name || ''}</div>
                ${barcodeSvg(code)}
                <script>window.print();<\/script>
            </body></html>`);
            w.document.close();
        }

        function printPriceTag(code, name, price) {
            if (!/^\d{13}$/.test(code || '')) {
                alert('Сначала укажите или сгенерируйте EAN-13 штрихкод.');
                return;
            }
            const w = window.open('', '_blank', 'width=420,height=360');
            w.document.write(`<html><head><title>Печать ценника</title></head><body style="font-family:Arial;text-align:center">
                <div style="font-size:18px;font-weight:bold;margin-bottom:6px">${name || ''}</div>
                <div style="font-size:28px;font-weight:bold;margin-bottom:8px">${parseFloat(price || 0).toFixed(2)} ₽</div>
                ${barcodeSvg(code)}
                <script>window.print();<\/script>
            </body></html>`);
            w.document.close();
        }
    </script>
@endpush
