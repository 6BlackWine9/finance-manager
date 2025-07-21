@extends('layouts.app')

@section('title', 'Главная')

@section('content')
<div class="container py-5">
    <!-- Hero Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-5 mb-lg-0">
            <h1 class="display-4 fw-bold mb-4 text-primary">
                <i class="fas fa-chart-line me-3"></i>Finance Manager
            </h1>
            <p class="lead mb-4 text-muted">
                Простое и удобное приложение для управления личными финансами. 
                Контролируйте доходы и расходы, анализируйте траты и принимайте обоснованные финансовые решения.
            </p>
            <div class="d-flex gap-3 flex-wrap">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-tachometer-alt me-2"></i> Перейти к дашборду
                    </a>
                    @if(!auth()->user()->isPremium())
                        <a href="{{ route('subscription') }}" class="btn btn-outline-warning btn-lg px-4">
                            <i class="fas fa-crown me-2"></i> Получить Премиум
                        </a>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i> Начать бесплатно
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg px-4">
                        <i class="fas fa-sign-in-alt me-2"></i> Войти
                    </a>
                @endauth
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-3">
                                <i class="fas fa-arrow-up fa-2x text-primary mb-2"></i>
                                <h5 class="text-primary">Доходы</h5>
                                <h4>125,000 ₽</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3">
                                <i class="fas fa-arrow-down fa-2x text-danger mb-2"></i>
                                <h5 class="text-danger">Расходы</h5>
                                <h4>89,500 ₽</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3">
                                <i class="fas fa-wallet fa-2x text-info mb-2"></i>
                                <h5 class="text-info">Баланс</h5>
                                <h4>35,500 ₽</h4>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-chart-bar me-1"></i>
                            Пример дашборда Finance Manager
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-light rounded p-5 mb-5">
        <h2 class="text-center mb-5">Возможности приложения</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-chart-line fa-2x text-primary"></i>
                        </div>
                        <h5 class="card-title">Анализ финансов</h5>
                        <p class="card-text text-muted">
                            Наглядные графики и отчеты помогут понять структуру ваших доходов и расходов
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-tags fa-2x text-primary"></i>
                        </div>
                        <h5 class="card-title">Категории</h5>
                        <p class="card-text text-muted">
                            Организуйте транзакции по категориям для лучшего контроля расходов
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-crown fa-2x text-warning"></i>
                        </div>
                        <h5 class="card-title">Премиум функции</h5>
                        <p class="card-text text-muted">
                            Расширенная аналитика, экспорт данных и неограниченное количество транзакций
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    @guest
    <div class="text-center mb-5">
        <h2 class="mb-4">Тарифные планы</h2>
        <div class="row justify-content-center">
            <div class="col-md-5 mb-4">
                <div class="card border-primary h-100">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Бесплатный</h4>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-primary mb-3">0 ₽</h2>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>До 100 транзакций в месяц</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>До 5 категорий</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Базовая аналитика</li>
                            <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Экспорт данных</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-primary">Начать бесплатно</a>
                    </div>
                </div>
            </div>
            <div class="col-md-5 mb-4">
                <div class="card border-warning h-100 position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle">
                        <span class="badge bg-warning text-dark px-3 py-2">Популярный</span>
                    </div>
                    <div class="card-header bg-warning text-dark text-center">
                        <h4 class="mb-0"><i class="fas fa-crown me-2"></i>Премиум</h4>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-warning mb-3">299 ₽<small class="text-muted">/мес</small></h2>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Неограниченные транзакции</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Неограниченные категории</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Расширенная аналитика</li>
                            <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Экспорт в Excel</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-warning">Попробовать Премиум</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endguest

    <!-- CTA Section -->
    @guest
    <div class="bg-primary text-white rounded p-5 text-center">
        <h2 class="mb-4">Начните контролировать свои финансы уже сегодня!</h2>
        <p class="lead mb-4">
            Регистрация займет меньше минуты и откроет доступ ко всем возможностям приложения
        </p>
        <a href="{{ route('register') }}" class="btn btn-light btn-lg px-5">
            <i class="fas fa-rocket me-2"></i> Присоединиться бесплатно
        </a>
    </div>
    @else
    <div class="bg-primary text-white rounded p-5 text-center">
        <h2 class="mb-4">
            <i class="fas fa-user-check me-3"></i>Добро пожаловать, {{ auth()->user()->name }}!
        </h2>
        <p class="lead mb-4">
            Вы успешно авторизованы. Начните управлять своими финансами прямо сейчас!
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg px-4">
                <i class="fas fa-tachometer-alt me-2"></i> Дашборд
            </a>
            <a href="{{ route('transactions.create') }}" class="btn btn-outline-light btn-lg px-4">
                <i class="fas fa-plus me-2"></i> Добавить транзакцию
            </a>
        </div>
    </div>
    @endguest
</div>
@endsection