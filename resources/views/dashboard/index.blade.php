@extends('layouts.app')

@section('title', 'Дашборд')

@section('content')
<div class="container-fluid">
    <!-- Заголовок с информацией о подписке -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-tachometer-alt me-2"></i>Дашборд</h2>
                <div class="d-flex gap-2 align-items-center">
                    @if(auth()->user()->isPremium())
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-crown me-1"></i>ПРЕМИУМ
                        </span>
                        <small class="text-muted">до {{ auth()->user()->premium_until->format('d.m.Y') }}</small>
                    @else
                        <a href="{{ route('subscription') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-crown me-1"></i>Получить Премиум
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Информация о лимитах для бесплатных пользователей -->
    @if(!auth()->user()->isPremium())
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6><i class="fas fa-info-circle me-2"></i>Бесплатный тариф</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small>Транзакций в месяце: <strong>{{ $limitInfo['current'] }}/{{ $limitInfo['limit'] }}</strong></small>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar {{ $limitInfo['remaining'] <= 10 ? 'bg-danger' : 'bg-primary' }}" 
                                         style="width: {{ ($limitInfo['current'] / $limitInfo['limit']) * 100 }}%"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small>Категорий: <strong>{{ $stats['categories_count'] }}/5</strong></small>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar {{ $stats['categories_count'] >= 4 ? 'bg-warning' : 'bg-success' }}" 
                                         style="width: {{ ($stats['categories_count'] / 5) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('subscription') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-arrow-up me-1"></i>Улучшить до Премиум
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Фильтр периода -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <form method="GET" class="d-flex gap-2 align-items-center">
                        <label class="form-label mb-0">Период:</label>
                        <select name="period" class="form-select" style="width: auto;" onchange="this.form.submit()">
                            <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Неделя</option>
                            <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Месяц</option>
                        </select>
                        
                        @if(auth()->user()->isPremium())
                            <div class="ms-auto">
                                <a href="{{ route('analytics') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-chart-line me-1"></i>Расширенная аналитика
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Основная статистика -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Доходы за период</h6>
                            <h4>{{ number_format($stats['income'], 2) }} ₽</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Расходы за период</h6>
                            <h4>{{ number_format($stats['expenses'], 2) }} ₽</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white {{ $stats['balance'] >= 0 ? 'bg-info' : 'bg-warning' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Общий баланс</h6>
                            <h4>{{ number_format($stats['balance'], 2) }} ₽</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- График доходов и расходов -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-chart-area me-2"></i>Доходы и расходы за {{ $period === 'week' ? 'неделю' : 'месяц' }}</h5>
                    @if(!auth()->user()->isPremium())
                        <small class="text-muted">Базовый график</small>
                    @endif
                </div>
                <div class="card-body">
                    <canvas id="incomeExpenseChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- То�� категории -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Топ расходов</h5>
                </div>
                <div class="card-body">
                    @if($topExpenseCategories->count() > 0)
                        @foreach($topExpenseCategories as $category)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-2" style="width: 12px; height: 12px; background-color: {{ $category->category->color }}; border-radius: 50%;"></div>
                                    <span>{{ $category->category->name }}</span>
                                </div>
                                <strong>{{ number_format($category->total, 2) }} ₽</strong>
                            </div>
                        @endforeach
                        
                        @if(!auth()->user()->isPremium())
                            <div class="text-center mt-3">
                                <small class="text-muted">Показано топ-3 категории</small><br>
                                <a href="{{ route('subscription') }}" class="btn btn-outline-warning btn-sm mt-2">
                                    <i class="fas fa-crown me-1"></i>Больше в Премиум
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-pie fa-3x mb-3"></i>
                            <p>Нет данных о расходах</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Премиум функции -->
    @if($premiumFeatures)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5><i class="fas fa-crown me-2"></i>Премиум аналитика</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted">Норма сбережений</h6>
                            <h4 class="{{ $premiumFeatures['savings_rate'] > 20 ? 'text-success' : ($premiumFeatures['savings_rate'] > 10 ? 'text-warning' : 'text-danger') }}">
                                {{ $premiumFeatures['savings_rate'] }}%
                            </h4>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted">Тренд расходов</h6>
                            <h4 class="{{ $premiumFeatures['spending_trend'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $premiumFeatures['spending_trend'] > 0 ? '↗' : '↘' }}
                                {{ abs($premiumFeatures['spending_trend']) }}%
                            </h4>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted">Активных категорий</h6>
                            <h4 class="text-info">{{ $premiumFeatures['category_insights']->count() }}</h4>
                        </div>
                        <div class="col-md-3 text-center">
                            <a href="{{ route('analytics') }}" class="btn btn-warning">
                                <i class="fas fa-chart-line me-1"></i>Подробнее
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Последние транзакции -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-history me-2"></i>Последние транзакции</h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>Все транзакции
                    </a>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Категория</th>
                                        <th>Описание</th>
                                        <th class="text-end">Сумма</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->date->format('d.m.Y') }}</td>
                                            <td>
                                                <span class="badge" style="background-color: {{ $transaction->category->color }};">
                                                    {{ $transaction->category->name }}
                                                </span>
                                            </td>
                                            <td>{{ $transaction->description }}</td>
                                            <td class="text-end">
                                                <span class="fw-bold {{ $transaction->category->type === 'income' ? 'text-success' : 'text-danger' }}">
                                                    {{ $transaction->category->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} ₽
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-receipt fa-3x mb-3"></i>
                            <p>Нет транзакций</p>
                            <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Добавить транзакцию
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// График доходов и расходов
const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData['labels']) !!},
        datasets: [{
            label: 'Доходы',
            data: {!! json_encode(array_values($chartData['income']->toArray())) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Расходы',
            data: {!! json_encode(array_values($chartData['expenses']->toArray())) !!},
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' ₽';
                    }
                }
            },
            x: {
                ticks: {
                    maxTicksLimit: 10 // Ограничиваем количество меток на оси X
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' ₽';
                    }
                }
            }
        }
    }
});
</script>
@endsection
