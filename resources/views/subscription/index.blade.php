@extends('layouts.app')

@section('title', 'Подписка')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Заголовок -->
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-primary mb-3">
                    <i class="fas fa-crown me-3 text-warning"></i>Выберите тарифный план
                </h1>
                <p class="lead text-muted">Управляйте финансами эффективнее с подходящим тарифом</p>
            </div>
            <!-- Тарифные планы -->
            <div id="pricing" class="row justify-content-center">
                <!-- Бесплатный тариф -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 {{ !auth()->user()->isPremium() ? 'border-primary shadow' : 'border-light' }}">
                        <div class="card-header {{ !auth()->user()->isPremium() ? 'bg-primary text-white' : 'bg-light' }} text-center py-3">
                            <h4 class="mb-0">
                                <i class="fas fa-user me-2"></i>Бесплатный
                            </h4>
                            @if(!auth()->user()->isPremium())
                                <small class="badge bg-light text-primary mt-2">Текущий план</small>
                            @endif
                        </div>
                        <div class="card-body text-center d-flex flex-column">
                            <div class="mb-4">
                                <h1 class="display-4 fw-bold text-primary mb-0">0 ₽</h1>
                                <small class="text-muted">навсегда</small>
                            </div>
                            
                            <ul class="list-unstyled text-start flex-grow-1">
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span><strong>100 транзакций</strong> в месяц</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>До <strong>5 категорий</strong></span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Базовая аналитика</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Графики доходов и расходов</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-times text-danger me-3"></i>
                                    <span class="text-muted">Экспорт данных</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-times text-danger me-3"></i>
                                    <span class="text-muted">Расширенная аналитика</span>
                                </li>
                            </ul>
                            
                            @if(!auth()->user()->isPremium())
                                <button class="btn btn-outline-primary mt-auto" disabled>
                                    <i class="fas fa-check me-1"></i>Активный план
                                </button>
                            @else
                                <p class="text-muted mt-auto mb-0">
                                    <small>Вернуться к бесплатному плану можно в любое время</small>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Премиум месячный тариф -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-warning shadow-lg position-relative">
                        <div class="position-absolute top-0 start-50 translate-middle">
                            <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                <i class="fas fa-star me-1"></i>Популярный
                            </span>
                        </div>
                        <div class="card-header bg-warning text-dark text-center py-3">
                            <h4 class="mb-0">
                                <i class="fas fa-crown me-2"></i>Премиум
                            </h4>
                            <small>Месячная подписка</small>
                            @if(auth()->user()->isPremium())
                                <div class="mt-2">
                                    <small class="badge bg-dark text-warning">Активный план</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-body text-center d-flex flex-column">
                            <div class="mb-4">
                                <h1 class="display-4 fw-bold text-warning mb-0">299 ₽</h1>
                                <small class="text-muted">в месяц</small>
                            </div>
                            
                            <ul class="list-unstyled text-start flex-grow-1">
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span><strong>Неограниченные</strong> транзакции</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span><strong>Неограниченные</strong> категории</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Расширенная аналитика</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Экспорт в Excel</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Прогнозирование расходов</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Детальные отчеты</span>
                                </li>
                            </ul>
                            
                            @if(!auth()->user()->isPremium())
                                <form method="POST" action="{{ route('subscription.subscribe') }}" class="mt-auto">
                                    @csrf
                                    <input type="hidden" name="plan" value="monthly">
                                    <button type="submit" class="btn btn-warning btn-lg w-100 shadow">
                                        <i class="fas fa-crown me-2"></i>Получить Премиум
                                    </button>
                                </form>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-shield-alt me-1"></i>Безопасная оплата через Stripe
                                </small>
                            @else
                                <button class="btn btn-warning mt-auto" disabled>
                                    <i class="fas fa-check me-1"></i>Активный план
                                </button>
                                <form method="POST" action="{{ route('subscription.subscribe') }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="plan" value="monthly">
                                    <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                        <i class="fas fa-plus me-1"></i>Продлить на месяц
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Премиум годовой тариф -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-success shadow position-relative">
                        <div class="position-absolute top-0 start-50 translate-middle">
                            <span class="badge bg-success px-3 py-2 fs-6">
                                <i class="fas fa-gift me-1"></i>Выгодно -17%
                            </span>
                        </div>
                        <div class="card-header bg-success text-white text-center py-3">
                            <h4 class="mb-0">
                                <i class="fas fa-crown me-2"></i>Премиум
                            </h4>
                            <small>Годовая подписка</small>
                        </div>
                        <div class="card-body text-center d-flex flex-column">
                            <div class="mb-4">
                                <h1 class="display-4 fw-bold text-success mb-0">2990 ₽</h1>
                                <small class="text-muted">в год</small>
                                <div class="mt-2">
                                </div>
                            </div>
                            
                            <ul class="list-unstyled text-start flex-grow-1">
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Все возможности месячного</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span><strong>Скидка 17%</strong> от месячной цены</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Ранний доступ к функциям</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Приоритетная поддержка</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>Резервное копирование</span>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-check text-success me-3"></i>
                                    <span>API доступ</span>
                                </li>
                            </ul>
                            
                            <div class="mt-auto">
                                <button class="btn btn-outline-success w-100" disabled>
                                    <i class="fas fa-hourglass-half me-2"></i>Скоро в продаже
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.display-4 {
    font-size: 2.5rem;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }
    
    .display-5 {
        font-size: 1.8rem;
    }
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.progress {
    border-radius: 10px;
}

.badge {
    font-size: 0.75em;
}
</style>
@endsection
