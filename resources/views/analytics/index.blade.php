@extends('layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">📊 Аналитика склада</h2>

        {{-- Фильтры --}}
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Дата с</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">по</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Склад</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Все склады</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ $warehouseId == $w->id ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Магазин</label>
                <select name="store_id" class="form-select">
                    <option value="">Все магазины</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex justify-content-start mt-2">
                <button class="btn btn-primary me-2">Применить</button>
                <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">Сброс</a>
            </div>
        </form>

        {{-- Вкладки --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-main">📊 Общая аналитика</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-stores">🛒 Магазины</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-out">📉 ТОП расхода</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-fifo">⏳ FIFO / сроки годности</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-batch">🏷 Партии</a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- TAB 1: Общая аналитика --}}
            <div class="tab-pane fade show active" id="tab-main">
                {{-- карточки --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-bg-primary mb-3">
                            <div class="card-body">
                                <div class="card-title">ТОП товаров</div>
                                <div class="display-6">{{ count($topProducts['labels']) }}</div>
                                <small>по движению за период</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-bg-success mb-3">
                            <div class="card-body">
                                <div class="card-title">Складов</div>
                                <div class="display-6">{{ $warehouses->count() }}</div>
                                <small>участвуют в остатках</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-bg-info mb-3">
                            <div class="card-body">
                                <div class="card-title">Дней в периоде</div>
                                <div class="display-6">{{ count($history['labels']) }}</div>
                                <small>для графика движений</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-bg-warning mb-3">
                            <div class="card-body">
                                <div class="card-title">Товаров с закупками</div>
                                <div class="display-6">{{ $avgPriceTable->count() }}</div>
                                <small>участвуют в себестоимости</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- графики ТОП + запасы по складам --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">ТОП товаров по движению</div>
                            <div class="card-body">
                                <canvas id="topProductsChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">Запасы по складам</div>
                            <div class="card-body">
                                <canvas id="stockByWarehouseChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- история движений --}}
                <div class="card mb-4">
                    <div class="card-header">История движений по дням</div>
                    <div class="card-body">
                        <canvas id="historyChart" height="120"></canvas>
                    </div>
                </div>

                {{-- средняя цена / себестоимость --}}
                <div class="card mb-4">
                    <div class="card-header">Средняя цена закупки и себестоимость</div>
                    <div class="card-body p-0">
                        <div class="table-responsive mb-0">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Товар</th>
                                    <th class="text-end">Кол-во</th>
                                    <th class="text-end">Сумма закупки</th>
                                    <th class="text-end">Средняя цена</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($avgPriceTable as $row)
                                    <tr>
                                        <td>{{ $row['product'] }}</td>
                                        <td class="text-end">{{ number_format($row['qty'], 2, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($row['cost'], 2, ',', ' ') }} ₽</td>
                                        <td class="text-end">{{ number_format($row['avg_price'], 2, ',', ' ') }} ₽</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Нет данных за выбранный период
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- TAB 2: Магазины --}}
            <div class="tab-pane fade" id="tab-stores">
                <div class="card mb-4">
                    <div class="card-header">Запасы по магазинам</div>
                    <div class="card-body">
                        <canvas id="stockByStoreChart" height="160"></canvas>
                    </div>
                </div>
            </div>

            {{-- TAB 3: ТОП расхода (убыль) --}}
            <div class="tab-pane fade" id="tab-out">
                <div class="card mb-4">
                    <div class="card-header">ТОП товаров по расходу (direction = out)</div>
                    <div class="card-body">
                        <canvas id="topOutChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            {{-- TAB 4: FIFO / сроки годности --}}
            <div class="tab-pane fade" id="tab-fifo">
                <div class="card mb-4">
                    <div class="card-header">Партии с ближайшими сроками годности</div>
                    <div class="card-body">
                        <canvas id="fifoChart" height="200"></canvas>
                        <p class="mt-2 text-muted small">
                            Красным можно подсветить партии, у которых скоро истекает срок.
                        </p>
                    </div>
                </div>
            </div>

            {{-- TAB 5: Аналитика по партиям --}}
            <div class="tab-pane fade" id="tab-batch">
                <div class="card mb-4">
                    <div class="card-header">Аналитика по партиям</div>
                    <div class="card-body p-0">
                        <div class="table-responsive mb-0">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Товар</th>
                                    <th>Партия</th>
                                    <th>Срок годности</th>
                                    <th class="text-end">Остаток</th>
                                    <th class="text-end">Сумма закупки</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($batchAnalytics as $row)
                                    <tr>
                                        <td>{{ $row['product'] }}</td>
                                        <td>{{ $row['batch'] }}</td>
                                        <td>{{ $row['expiry'] }}</td>
                                        <td class="text-end">{{ number_format($row['qty'], 3, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($row['cost'], 2, ',', ' ') }} ₽</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Нет партий с положительным остатком
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const topProducts      = @json($topProducts);
        const stockByWarehouse = @json($stockByWarehouse);
        const stockByStore     = @json($stockByStore);
        const history          = @json($history);
        const topOut           = @json($topOut);
        const fifo             = @json($fifoChart);

        // ТОП товаров по движению
        if (document.getElementById('topProductsChart')) {
            new Chart(document.getElementById('topProductsChart'), {
                type: 'bar',
                data: {
                    labels: topProducts.labels,
                    datasets: [{
                        label: 'Движение, шт',
                        data: topProducts.data,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { ticks: { autoSkip: false } } }
                }
            });
        }

        // Запасы по складам
        if (document.getElementById('stockByWarehouseChart')) {
            new Chart(document.getElementById('stockByWarehouseChart'), {
                type: 'bar',
                data: {
                    labels: stockByWarehouse.labels,
                    datasets: [{
                        label: 'Остаток, шт',
                        data: stockByWarehouse.data,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                }
            });
        }

        // Запасы по магазинам
        if (document.getElementById('stockByStoreChart')) {
            new Chart(document.getElementById('stockByStoreChart'), {
                type: 'bar',
                data: {
                    labels: stockByStore.labels,
                    datasets: [{
                        label: 'Остаток, шт',
                        data: stockByStore.data,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                }
            });
        }

        // История движений
        if (document.getElementById('historyChart')) {
            new Chart(document.getElementById('historyChart'), {
                type: 'line',
                data: {
                    labels: history.labels,
                    datasets: [{
                        label: 'Движение, шт (итог за день)',
                        data: history.qty,
                        tension: 0.2,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                }
            });
        }

        // ТОП расхода
        if (document.getElementById('topOutChart')) {
            new Chart(document.getElementById('topOutChart'), {
                type: 'bar',
                data: {
                    labels: topOut.labels,
                    datasets: [{
                        label: 'Расход, шт',
                        data: topOut.data,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { ticks: { autoSkip: false } } }
                }
            });
        }

        // FIFO / сроки годности
        if (document.getElementById('fifoChart')) {
            new Chart(document.getElementById('fifoChart'), {
                type: 'bar',
                data: {
                    labels: fifo.labels,
                    datasets: [{
                        label: 'Остаток по партиям',
                        data: fifo.data,
                        backgroundColor: fifo.expiry.map(function(e) {
                            if (!e) return 'rgba(100, 100, 100, 0.7)';
                            const dExp   = new Date(e);
                            const today  = new Date();
                            const diff   = (dExp - today) / (1000*3600*24);
                            return diff < 30 ? 'rgba(255, 0, 0, 0.7)' : 'rgba(0, 128, 255, 0.7)';
                        })
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { ticks: { autoSkip: false } } }
                }
            });
        }
    </script>
@endsection
