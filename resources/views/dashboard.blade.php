@extends('layouts.app')

@section('title', 'Дашборд')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-tachometer-alt me-2"></i>Дашборд</h2>
    </div>
    <div class="col-md-6 text-end">
        @if(!auth()->user()->isPremium())
            @php
                $limitInfo = auth()->user()->getTransactionLimitInfo();
            @endphp
            <div class="alert alert-warning d-inline-block mb-0 me-2">
                <small>
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Транзакций: {{ $limitInfo['current'] }}/{{ $limitInfo['limit'] }}
                </small>
            </div>
        @endif
        
        <div class="dropdown d-inline-block">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="periodDropdown" data-bs-toggle="dropdown">
                {{ now()->format('F Y') }}
            </button>
            <ul class="dropdown-menu" aria-labelledby="periodDropdown">
                <li><a class="dropdown-item" href="#">Текущий месяц</a></li>
                <li><a class="dropdown-item" href="#">Прошлый месяц</a></li>
                <li><a class="dropdown-item" href="#">Квартал</a></li>
                <li><a class="dropdown-item" href="#">Год</a></li>
                @if(auth()->user()->isPremium())
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Произвольный период</a></li>
                @endif
            </ul>
        </div>
    </div>
</div>

<!-- Статистические карточки -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Доходы</h5>
                        <p class="card-text h4">{{ number_format($incomes, 2) }} ₽</p>
                    </div>
                    <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Расходы</h5>
                        <p class="card-text h4">{{ number_format($expenses, 2) }} ₽</p>
                    </div>
                    <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Баланс</h5>
                        <p class="card-text h4">{{ number_format($incomes - $expenses, 2) }} ₽</p>
                    </div>
                    <i class="fas fa-wallet fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Премиум функции -->
@if(auth()->user()->isPremium())
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-crown me-2"></i>Премиум функции</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('analytics') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-chart-line me-2"></i>Расширенная аналитика
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('reports') }}" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-file-alt me-2"></i>Отчеты
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('export.excel') }}" class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-file-excel me-2"></i>Экспорт Excel
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('export.pdf') }}" class="btn btn-outline-danger w-100 mb-2">
                            <i class="fas fa-file-pdf me-2"></i>Экспорт PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Основной контент -->
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-exchange-alt me-2"></i>Последние транзакции</h5>
                @if(!auth()->user()->isPremium())
                    <small class="text-muted">Показано последние 5 из {{ auth()->user()->transactions()->count() }}</small>
                @endif
            </div>
            <div class="card-body">
                @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Категория</th>
                                    <th>Описание</th>
                                    <th>Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions->take(auth()->user()->isPremium() ? 10 : 5) as $transaction)
                                <tr class="{{ $transaction->type === 'income' ? 'table-success' : 'table-danger' }}">
                                    <td>{{ $transaction->date->format('d.m.Y') }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $transaction->category->color }};">
                                            <i class="{{ $transaction->category->icon }} me-1"></i>
                                            {{ $transaction->category->name }}
                                        </span>
                                    </td>
                                    <td>{{ Str::limit($transaction->description, 30) }}</td>
                                    <td>{{ number_format($transaction->amount, 2) }} ₽</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">У вас пока нет транзакций</p>
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Добавить первую транзакцию
                        </a>
                    </div>
                @endif
                
                @if($transactions->count() > 0)
                    <a href="{{ route('transactions.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-list me-1"></i> Все транзакции
                    </a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie me-2"></i>Расходы по категориям</h5>
            </div>
            <div class="card-body">
                @if($expensesByCategory->count() > 0)
                    <canvas id="expensesChart" height="300"></canvas>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Нет данных для отображения</p>
                        <small class="text-muted">Добавьте расходы, чтобы увидеть график</small>
                    </div>
                @endif
            </div>
        </div>
        
        @if(!auth()->user()->isPremium())
        <div class="card border-warning mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-crown me-2"></i>Премиум</h5>
            </div>
            <div class="card-body">
                <p>Откройте все возможности приложения с премиум подпиской!</p>
                <ul class="mb-3">
                    <li>Неограниченные транзакции</li>
                    <li>Расширенная аналитика</li>
                    <li>Экспорт данных</li>
                    <li>Настраиваемые отчеты</li>
                </ul>
                <a href="{{ route('subscription') }}" class="btn btn-warning w-100">
                    Подробнее
                </a>
            </div>
        </div>
        @else
        <div class="card border-success mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-crown me-2"></i>Премиум активен</h5>
            </div>
            <div class="card-body">
                <p>Подписка активна до:</p>
                <p class="h5 text-success">{{ auth()->user()->premium_until ? auth()->user()->premium_until->format('d.m.Y') : 'Не установлено' }}</p>
                <p class="mb-0">Осталось дней: <strong>{{ auth()->user()->getPremiumDaysLeft() }}</strong></p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($expensesByCategory->count() > 0)
        const ctx = document.getElementById('expensesChart');
        if (ctx) {
            const expensesData = @json($expensesByCategory);
            
            const labels = expensesData.map(item => item.name);
            const data = expensesData.map(item => parseFloat(item.total) || 0);
            const backgroundColors = expensesData.map(item => item.color);
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.formattedValue + ' ₽';
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif
    });
</script>
@endsection