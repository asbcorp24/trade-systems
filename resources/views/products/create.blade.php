@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4">Добавить товар (Номенклатура)</h2>

        <div class="card">
            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Штрихкод (опционально)</label>
                        <div class="input-group">
                            <input type="text" id="barcode" class="form-control">
                            <button class="btn btn-outline-primary" type="button" id="generateBarcode">Сгенерировать</button>
                            <button class="btn btn-outline-secondary" type="button" id="printBarcode">Печать</button>
                            <button class="btn btn-outline-success" type="button" id="printPriceTag">Ценник</button>
                        </div>
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
