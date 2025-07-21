@extends('layouts.app')

@section('title', 'Категории')

@section('content')
<div class="container">
    <!-- Заголовок -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-tags me-2"></i>Категории</h2>
                <div class="d-flex align-items-center gap-3">
                    @if(!auth()->user()->canCreateCategory())
                        <div class="text-end">
                            <small class="text-muted d-block">Лимит категорий: {{ auth()->user()->categories()->count() }}/{{ auth()->user()->getCategoriesLimit() }}</small>
                            <a href="{{ route('subscription') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-crown me-1"></i>Получить Премиум
                            </a>
                        </div>
                    @endif
                    @if(auth()->user()->canCreateCategory())
                        <a href="{{ route('categories.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Добавить категорию
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Список категорий -->
    <div class="row">
        @if($categories->count() > 0)
            @foreach($categories as $category)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <!-- Заголовок категории -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3 rounded-circle" 
                                     style="width: 40px; height: 40px; background-color: {{ $category->color }};"></div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1 fw-bold">{{ $category->name }}</h5>
                                    <span class="badge {{ $category->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                                        <i class="fas {{ $category->type === 'income' ? 'fa-arrow-up' : 'fa-arrow-down' }} me-1"></i>
                                        {{ $category->type === 'income' ? 'Доход' : 'Расход' }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Статистика -->
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end pe-3">
                                        <div class="text-muted small mb-1">Транзакций</div>
                                        <div class="h5 mb-0 fw-bold text-primary">{{ $category->transactions_count }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ps-3">
                                        <div class="text-muted small mb-1">Общая сумма</div>
                                        <div class="h6 mb-0 fw-bold {{ $category->type === 'income' ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($category->transactions_sum_amount ?? 0, 0) }} ₽
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Действия -->
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <div class="d-grid gap-2 d-md-flex">
                                <a href="{{ route('categories.edit', $category) }}" 
                                   class="btn btn-outline-primary flex-fill">
                                    <i class="fas fa-edit me-1"></i>Редактировать
                                </a>
                                <button type="button" class="btn btn-outline-danger flex-fill" 
                                        onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}', {{ $category->transactions_count }})">
                                    <i class="fas fa-trash me-1"></i>Удалить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-tags fa-4x text-muted"></i>
                        </div>
                        <h5 class="text-muted">Нет категорий</h5>
                        <p class="text-muted mb-4">Создайте первую категорию для организации ваших транзакций</p>
                        @if(auth()->user()->canCreateCategory())
                            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Добавить категорию
                            </a>
                        @else
                            <div class="alert alert-info d-inline-block">
                                <i class="fas fa-info-circle me-2"></i>
                                Достигнут лимит категорий для бесплатного тарифа.
                                <a href="{{ route('subscription') }}" class="alert-link">Получите Премиум</a> для неограниченного количества категорий.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p>Вы уверены, что хотите удалить категорию <strong id="categoryName"></strong>?</p>
                    <div id="warningMessage" class="alert alert-danger" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Внимание!</strong> В этой категории есть транзакции. Удаление невозможно.
                    </div>
                    <small class="text-muted">Это действие нельзя отменить.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="deleteButton">
                        <i class="fas fa-trash me-1"></i>Удалить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteCategory(id, name, transactionsCount) {
    const form = document.getElementById('deleteForm');
    const categoryName = document.getElementById('categoryName');
    const warningMessage = document.getElementById('warningMessage');
    const deleteButton = document.getElementById('deleteButton');
    
    form.action = `/categories/${id}`;
    categoryName.textContent = name;
    
    if (transactionsCount > 0) {
        warningMessage.style.display = 'block';
        deleteButton.disabled = true;
        deleteButton.innerHTML = '<i class="fas fa-ban me-1"></i>Нельзя удалить';
        deleteButton.classList.remove('btn-danger');
        deleteButton.classList.add('btn-secondary');
    } else {
        warningMessage.style.display = 'none';
        deleteButton.disabled = false;
        deleteButton.innerHTML = '<i class="fas fa-trash me-1"></i>Удалить';
        deleteButton.classList.remove('btn-secondary');
        deleteButton.classList.add('btn-danger');
    }
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
