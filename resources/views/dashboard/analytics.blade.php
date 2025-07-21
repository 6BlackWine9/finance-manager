@extends('layouts.app')

@section('title', 'Расширенная аналитика')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-line me-2"></i>Расширенная аналитика</h2>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                        <i class="fas fa-crown me-1"></i>ПРЕМИУМ
                    </span>
                    <a href="{{ route('profile.export') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Экспорт в Excel
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Назад к дашборду
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Фильтр периода -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="d-flex gap-2 align-items-center">
                        <label class="form-label mb-0">Период:</label>
                        <select name="period" class="form-select" style="width: auto;" onchange="this.form.submit()">
                            <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Текущий месяц</option>
                            <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Текущий год</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Расходы по категориям -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="fas fa-pie-chart me-2"></i>Расходы по категориям</h5>
                </div>
                <div class="card-body">
                    @if(isset($analytics['spending_by_category']) && $analytics['spending_by_category']->count() > 0)
                        <div style="position: relative; height: 300px;">
                            <canvas id="spendingChart"></canvas>
                        </div>
                        <div class="mt-3">
                            @foreach($analytics['spending_by_category'] as $category)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2" style="width: 12px; height: 12px; background-color: {{ $category->color }}; border-radius: 50%;"></div>
                                        <span>{{ $category->name }}</span>
                                    </div>
                                    <strong>{{ number_format($category->total, 2) }} ₽</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-chart-pie fa-3x mb-3"></i>
                            <p>Нет данных о расходах за выбранный период</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Источники доходов -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="fas fa-money-bill-wave me-2"></i>Источники доходов</h5>
                </div>
                <div class="card-body">
                    @if(isset($analytics['income_sources']) && $analytics['income_sources']->count() > 0)
                        <div style="position: relative; height: 300px;">
                            <canvas id="incomeChart"></canvas>
                        </div>
                        <div class="mt-3">
                            @foreach($analytics['income_sources'] as $source)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2" style="width: 12px; height: 12px; background-color: {{ $source->color }}; border-radius: 50%;"></div>
                                        <span>{{ $source->name }}</span>
                                    </div>
                                    <strong>{{ number_format($source->total, 2) }} ₽</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                            <p>Нет данных о доходах за выбранный период</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Анализ бюджета -->
    @if(isset($analytics['budget_analysis']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calculator me-2"></i>Анализ бюджета</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Средние расходы</h6>
                                <h4>{{ number_format($analytics['budget_analysis']['average_monthly'], 2) }} ₽</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Текущий месяц</h6>
                                <h4>{{ number_format($analytics['budget_analysis']['current_month'], 2) }} ₽</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Отклонение</h6>
                                <h4 class="{{ $analytics['budget_analysis']['variance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $analytics['budget_analysis']['variance'] > 0 ? '+' : '' }}{{ number_format($analytics['budget_analysis']['variance'], 2) }} ₽
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Статус</h6>
                                <span class="badge {{ $analytics['budget_analysis']['status'] === 'over' ? 'bg-danger' : 'bg-success' }}">
                                    {{ $analytics['budget_analysis']['status'] === 'over' ? 'Превышение' : 'В рамках' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Прогноз расходов -->
    @if(isset($analytics['forecasting']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-crystal-ball me-2"></i>Прогноз расходов</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted">Прогноз на следующий месяц</h6>
                                <h4 class="text-info">{{ number_format($analytics['forecasting']['next_month_forecast'], 2) }} ₽</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted">Тренд</h6>
                                <h4 class="{{ $analytics['forecasting']['trend'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $analytics['forecasting']['trend'] > 0 ? '↗' : '↘' }}
                                    {{ number_format(abs($analytics['forecasting']['trend']), 2) }} ₽
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted">Точность прогноза</h6>
                                <span class="badge bg-warning text-dark">{{ ucfirst($analytics['forecasting']['confidence']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Analytics page loaded');
    
    try {
        // График расходов по категориям
        @if(isset($analytics['spending_by_category']) && $analytics['spending_by_category']->count() > 0)
        const spendingCanvas = document.getElementById('spendingChart');
        if (spendingCanvas) {
            console.log('Creating spending chart');
            const spendingCtx = spendingCanvas.getContext('2d');
            
            new Chart(spendingCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($analytics['spending_by_category']->pluck('name')->toArray()) !!},
                    datasets: [{
                        data: {!! json_encode($analytics['spending_by_category']->pluck('total')->toArray()) !!},
                        backgroundColor: {!! json_encode($analytics['spending_by_category']->pluck('color')->toArray()) !!},
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed.toLocaleString() + ' ₽';
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif

        // График источников доходов
        @if(isset($analytics['income_sources']) && $analytics['income_sources']->count() > 0)
        const incomeCanvas = document.getElementById('incomeChart');
        if (incomeCanvas) {
            console.log('Creating income chart');
            const incomeCtx = incomeCanvas.getContext('2d');
            
            new Chart(incomeCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($analytics['income_sources']->pluck('name')->toArray()) !!},
                    datasets: [{
                        data: {!! json_encode($analytics['income_sources']->pluck('total')->toArray()) !!},
                        backgroundColor: {!! json_encode($analytics['income_sources']->pluck('color')->toArray()) !!},
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed.toLocaleString() + ' ₽';
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif
        
        console.log('Charts created successfully');
        
    } catch (error) {
        console.error('Error creating charts:', error);
    }
});

// Предотвращаем любые проблемы с прокруткой
document.body.style.overflowX = 'hidden';
</script>

<style>
/* Фиксируем любые проблемы с прокруткой */
html, body {
    overflow-x: hidden;
    scroll-behavior: smooth;
}

.container-fluid {
    max-width: 100%;
    padding-bottom: 50px;
}

/* Фиксируем высоту графиков */
canvas {
    max-height: 300px !important;
}

/* Убираем любые анимации, которые могут вызывать проблемы */
* {
    -webkit-transform: none !important;
    transform: none !important;
    -webkit-animation: none !important;
    animation: none !important;
}
</style>
@endsection
