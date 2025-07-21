@extends('layouts.app')

@section('title', 'Оплата отменена')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-warning mb-4">Оплата отменена</h2>
                    <p class="lead">Вы отменили процесс оплаты. Никакие средства не были списаны.</p>
                    
                    <div class="mt-4">
                        <a href="{{ route('subscription') }}" class="btn btn-primary me-3">
                            <i class="fas fa-arrow-left me-2"></i>Вернуться к подпискам
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-tachometer-alt me-2"></i>Дашборд
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection