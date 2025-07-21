@extends('layouts.app')

@section('title', 'Профиль пользователя')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-user me-2"></i>Профиль пользователя</h4>
                    @if(auth()->user()->isPremium())
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-crown me-1"></i>ПРЕМИУМ
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Информация о пользователе -->
                        <div class="col-md-6">
                            <h5>Личная информация</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Имя:</strong></td>
                                    <td>{{ auth()->user()->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ auth()->user()->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Дата регистрации:</strong></td>
                                    <td>{{ $stats['account_created']->format('d.m.Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Статус:</strong></td>
                                    <td>
                                        @if(auth()->user()->isPremium())
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-crown me-1"></i>Премиум до {{ auth()->user()->premium_until->format('d.m.Y') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Бесплатный</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Статистика -->
                        <div class="col-md-6">
                            <h5>Статистика</h5>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $stats['total_transactions'] }}</h4>
                                            <small>Транзакций</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $stats['total_categories'] }}</h4>
                                            <small>Категорий</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ number_format($stats['total_income'], 0) }} ₽</h4>
                                            <small>Доходы</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ number_format($stats['total_expenses'], 0) }} ₽</h4>
                                            <small>Расходы</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Экспорт данных (только для премиум) -->
                    @if(auth()->user()->isPremium())
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="fas fa-download me-2"></i>Экспорт данных</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Экспортируйте все ваши финансовые данные в Excel формате.</p>
                                    <a href="{{ route('profile.export') }}" class="btn btn-success">
                                        <i class="fas fa-file-excel me-1"></i>Скачать Excel (.xls)
                                    </a>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Экспорт включает все транзакции, категории и статистику
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Действия -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i>Редактировать профиль
                                </a>
                                <a href="{{ route('profile.password') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-key me-1"></i>Изменить пароль
                                </a>
                                @if(!auth()->user()->isPremium())
                                    <a href="{{ route('subscription') }}" class="btn btn-warning">
                                        <i class="fas fa-crown me-1"></i>Получить Премиум
                                    </a>
                                @endif
                                <button type="button" class="btn btn-outline-danger ms-auto" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                    <i class="fas fa-trash me-1"></i>Удалить аккаунт
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно удаления аккаунта -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удаление аккаунта</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Внимание!</strong> Это действие необратимо. Все ваши данные будут удалены навсегда.
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Подтвердите пароль:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить аккаунт</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
