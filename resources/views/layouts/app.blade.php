<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF для AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Склад-Торговля') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />

    <style>
        body { background: #f8f9fa; }
        footer { background: #fff; border-top: 1px solid #ddd; }
    </style>
    <style>
        /* PC mode: show table */
        #itemsTableWrapper { display: block; }


        /* Mobile responsive */
        @media (max-width: 768px) {

            #itemsCardWrapper { display: block; }
        }

        .item-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 12px;
            padding: 12px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .item-card {
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .swipe-delete {
            position: absolute;
            right: -90px;
            top: 0;
            height: 100%;
            width: 90px;
            background: #dc3545;
            color: #fff;
            border: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: right 0.2s ease;
        }

        .item-card.swiped {
            transform: translateX(-90px);
        }

        .item-card.swiped .swipe-delete {
            right: 0;
        }
        .scan-btn {
            font-size: 22px;
            padding: 14px;
            width: 100%;
            border-radius: 10px;
        }
        /* ===== ПК-КОМПАКТНАЯ ВЕРСИЯ ===== */
        @media (min-width: 992px) {  /* desktop */

            #itemsCardWrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
            }

            .item-card {
                width: calc(50% - 15px);
                padding: 10px 12px;
                border-radius: 8px;
                margin-bottom: 10px;
            }

            .item-card h5 {
                font-size: 16px;
                margin-bottom: 10px !important;
            }

            .item-card label {
                font-size: 13px;
                margin-bottom: 2px;
            }

            .item-card input,
            .item-card select {
                font-size: 14px;
                padding: 6px 8px;
            }

            .scan-btn {
                font-size: 14px;
                padding: 8px;
                border-radius: 6px;
            }

            .swipe-delete {
                width: 60px;
                font-size: 14px;
            }

            .item-card.swiped {
                transform: translateX(-60px);
            }
        }
        /* Карточки: базовый стиль уже есть выше, добавим адаптив для ПК */

        /* >= 992px: 2 колонки */
        @media (min-width: 992px) {
            #itemsCardWrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
            }
            .item-card {
                width: calc(50% - 15px);
            }
        }

        /* >= 1400px: 3 колонки */
        @media (min-width: 1400px) {
            .item-card {
                width: calc(33.333% - 15px);
            }
        }
        /* Компактный вид карточек для ПК */
        @media (min-width: 992px) {
            #itemsWrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
            }

            .sale-item-card {
                width: calc(33.333% - 12px);
                padding: 10px !important;
                font-size: 14px;
            }

            .sale-item-card input,
            .sale-item-card select {
                padding: 4px 6px !important;
                height: 32px !important;
                font-size: 14px !important;
            }

            .sale-item-card .form-label {
                font-size: 12px;
                margin-bottom: 2px;
            }

            .sale-item-card .btn-sm {
                padding: 2px 6px;
            }
        }

        /* Мобильная версия — оставляем как была */
        @media (max-width: 991px) {
            .sale-item-card {
                width: 100%;
            }
        }

    </style>

    @stack('head')
</head>
<body>

<!-- ==== НАВИГАЦИЯ ==== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <a class="navbar-brand" href="/">Склад & Торговля</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            @auth
            <ul class="navbar-nav me-auto">

                {{-- ===== СКЛАД ===== --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Склад</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/receipts/create">📥 Приёмка товара</a></li>
                        <li><a class="dropdown-item" href="/receipts/journal">📚 Журнал приёмки</a></li>
                        <li><a class="dropdown-item" href="/stock/transfers/create">📤 Перемещение</a></li>
                        <li><a class="dropdown-item" href="/transfers/journal">📚 Журнал перемещений</a></li>
                        <li><a class="dropdown-item" href="/stock">📦 Остатки</a></li>
                        <li><a class="dropdown-item" href="{{ route('reports.in_transit') }}">🚚 Товар в пути</a></li>
                        <li><a class="dropdown-item" href="/product-search">     Поиск товара</a></li>
                    </ul>
                </li>




                {{-- ===== НОМЕНКЛАТУРА ===== --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Номенклатура</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/products">📃 Все товары</a></li>
                        <li><a class="dropdown-item" href="/products/create">➕ Добавить товар</a></li>
                        <li><a class="dropdown-item" href="/categories">🗂 Категории (дерево)</a></li>
                        <li><a class="dropdown-item" href="/attributes">🔧 Параметры (атрибуты)</a></li>
                        <li><a class="dropdown-item" href="/units">📏 Единицы измерения</a></li>


                    </ul>
                </li>

                {{-- ===== СПРАВОЧНИКИ ===== --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Справочники</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/warehouses">🏭 Склады</a></li>
                        <li><a class="dropdown-item" href="/suppliers">🚚 Поставщики</a></li>
                        <li><a class="dropdown-item" href="/clients">🧾 Клиенты</a></li>
                        <li><a class="dropdown-item" href="/stores">🏬 Магазины</a></li>
                        @if(auth()->user()->role === 'superadmin')
                            <li><a class="dropdown-item" href="/users">Пользователи</a></li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Магазин</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/sales/create">🧾 Продажа товара</a></li>
                        <li  >
                            <a class="dropdown-item" href="/sales">
                                🧾 Продажи
                            </a>
                        </li>
                        {{-- позже: отчёты по продажам --}}
                    </ul>
                </li>
                {{-- ===== ОТЧЁТЫ ===== --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Отчёты</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/stock/movements">📄 Движение товара</a></li>
                        <li><a class="dropdown-item" href="/reports/stock">📊 Остатки</a></li>
                        <li><a class="dropdown-item" href="/reports/sales">💰 Продажи</a></li>
                        <li><a class="dropdown-item" href="{{ route('prices.history') }}">История цен</a></li>
                        <li><a class="dropdown-item" href="{{ route('audit.index') }}">Журнал действий</a></li>
                        <li>
                            <form action="{{ route('backup.database') }}" method="POST" class="px-3 py-1">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary w-100">Резервная копия БД</button>
                            </form>
                        </li>
                        <li  >
                            <a class="dropdown-item" href="{{ route('analytics.index') }}">
                                📊 Аналитика
                            </a>
                        </li>
                        <li >
                            <a class="dropdown-item" {{ request()->is('inventory*') ? 'active fw-bold text-primary' : '' }}"
                               href="{{ route('inventory.index') }}">
                                📦 Инвентаризации
                            </a>
                        </li>
                    </ul>
                </li>
                @php
                    $unread = \App\Models\Notification::where('is_read', false)->count();
                @endphp

                <li class="nav-item">
                    <a href="javascript:void(0)" onclick="openNotif()" class="nav-link position-relative">

                        🔔
                        @if($unread > 0)
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                {{ $unread }}
            </span>
                        @endif

                    </a>
                </li>
                <div class="d-flex ms-auto align-items-center">

                    @auth
                        <span class="text-white me-3">
                    {{ auth()->user()->name }}
                </span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm">
                                Выйти
                            </button>
                        </form>
                    @endauth

                </div>

            </ul>
            @endauth

        </div>
    </div>
</nav>

<!-- ==== КОНТЕНТ ==== -->
<div class="container py-4">
    @yield('content')
</div>

<!-- ==== ФУТЕР ==== -->
<footer class="text-center p-3">
    © {{ date('Y') }} Склад-Торговля
</footer>
<div id="notifPopup" class="notif-popup" style="display:none">
    <div class="card" style="width:350px;">
        <div class="card-header">
            Уведомления
            <button onclick="closeNotif()" class="btn btn-sm btn-light float-end">×</button>
        </div>
        <ul class="list-group list-group-flush">
            @foreach(\App\Models\Notification::orderBy('created_at','desc')->take(8)->get() as $n)
                <li class="list-group-item">
                    {{ $n->message }}
                    <div class="text-muted small">{{ $n->created_at->format('d.m.Y H:i') }}</div>
                </li>
            @endforeach
        </ul>
        <div class="card-footer">
            <a href="/notifications">Все уведомления →</a>
        </div>
    </div>
</div>
<!-- Barcode Scanner Component -->
<div class="modal fade" id="barcodeScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Сканирование штрихкода</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close" onclick="BarcodeScanner.stop()"></button>
            </div>

            <div class="modal-body text-center">
                <video id="barcodeScannerVideo" style="width:100%; max-height:300px;"></video>
                <p class="mt-2 text-muted small">Наведите камеру на штрихкод (EAN-13).</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal" onclick="BarcodeScanner.stop()">Закрыть</button>
            </div>

        </div>
    </div>
</div>
<script>
    function openNotif() {
        document.getElementById('notifPopup').style.display = 'block';
    }
    function closeNotif() {
        document.getElementById('notifPopup').style.display = 'none';
    }
</script>
<script>
    class BarcodeScanner {

        static targetInput = null;
        static reader = null;

        static async open(inputSelector) {
            BarcodeScanner.targetInput = document.querySelector(inputSelector);

            if (!BarcodeScanner.targetInput) {
                alert("Поле ввода не найдено: " + inputSelector);
                return;
            }

            if (!navigator.mediaDevices?.getUserMedia) {
                alert("Этот браузер не поддерживает камеру.");
                return;
            }

            if (!BarcodeScanner.reader && window.ZXing?.BrowserMultiFormatReader) {
                BarcodeScanner.reader = new ZXing.BrowserMultiFormatReader();
            }

            if (!BarcodeScanner.reader) {
                alert("Не загружена библиотека ZXing.");
                return;
            }

            const modal = new bootstrap.Modal(document.getElementById("barcodeScannerModal"));
            modal.show();

            BarcodeScanner.reader.decodeFromVideoDevice(
                null,
                "barcodeScannerVideo",
                (result, err) => {
                    if (result) {
                        const code = result.getText();
                        BarcodeScanner.targetInput.value = code;
                        BarcodeScanner.targetInput.dispatchEvent(new Event('change'));
                        BarcodeScanner.stop();
                    }
                }
            );
        }

        static stop() {
            if (BarcodeScanner.reader) {
                BarcodeScanner.reader.reset();
            }
            const modalEl = document.getElementById("barcodeScannerModal");
            const instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) instance.hide();
        }
    }
</script>

<style>
    .notif-popup {
        position: fixed;
        right: 20px;
        top: 70px;
        z-index: 9999;
    }
</style>

<!-- ===== JS ===== -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://unpkg.com/@zxing/library@0.20.0"></script>
@stack('scripts')
@yield('scripts')
</body>
</html>
