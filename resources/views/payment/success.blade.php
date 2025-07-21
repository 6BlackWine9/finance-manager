@extends('layouts.app')

@section('title', 'Оплата успешна')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-success mb-4">Оплата прошла успешно!</h2>
                    <p class="lead">Поздравляем! Ваша премиум подписка активирована.</p>
                    
                    @if(isset($months))
                        <div class="alert alert-success">
                            <strong>Подписка активна на {{ $months }} {{ $months == 1 ? 'месяц' : ($months < 5 ? 'месяца' : 'месяцев') }}</strong>
                        </div>
                    @endif
                    
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary me-3">
                            <i class="fas fa-tachometer-alt me-2"></i>Перейти к дашборду
                        </a>
                        <a href="{{ route('subscription') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-crown me-2"></i>Управление подпиской
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection