@extends('layouts.app')

@section('title', 'Транзакции')

@section('content')
<div class="container-fluid">
    <!-- Заголовок -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-receipt me-2"></i>Транзакции</h2>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Добавить транзакцию
                </a>
            </div>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Фильтры</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Категория</label>
                            <select name="category_id" class="form-select">
                                <option value="">Все категории</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Тип</label>
                            <select name="type" class="form-select">
                                <option value="">Все типы</option>
                                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Доходы</option>
                                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Расходы</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Дата от</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Дата до</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Поиск</label>
                            <input type="text" name="search" class="form-control" placeholder="Описание..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Список транзакций -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Список транзакций</h6>
                    @if($transactions->count() > 0)
                        <small class="text-muted">Всего: {{ $transactions->total() }}</small>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        @if(auth()->user()->isPremium())
                                            <th width="50" class="text-center">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                        @endif
                                        <th>Дата</th>
                                        <th>Категория</th>
                                        <th>Описание</th>
                                        <th class="text-end">Сумма</th>
                                        <th width="150" class="text-center">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            @if(auth()->user()->isPremium())
                                                <td class="text-center">
                                                    <input type="checkbox" name="transaction_ids[]" value="{{ $transaction->id }}" class="form-check-input transaction-checkbox">
                                                </td>
                                            @endif
                                            <td>
                                                <span class="fw-medium">{{ $transaction->date->format('d.m.Y') }}</span>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill" style="background-color: {{ $transaction->category->color }}; color: white;">
                                                    {{ $transaction->category->name }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-dark">{{ $transaction->description }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold fs-6 {{ $transaction->category->type === 'income' ? 'text-success' : 'text-danger' }}">
                                                    {{ $transaction->category->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} ₽
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('transactions.edit', $transaction) }}" 
                                                       class="btn btn-outline-primary" title="Редактировать">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if(auth()->user()->isPremium())
                                                        <form method="POST" action="{{ route('transactions.duplicate', $transaction) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-info" title="Дублировать">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteTransaction({{ $transaction->id }})" title="Удалить">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Пагинация -->
                        @if($transactions->hasPages())
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted">
                                        Показано {{ $transactions->firstItem() }}-{{ $transactions->lastItem() }} из {{ $transactions->total() }} транзакций
                                    </div>
                                    <div>
                                        {{ $transactions->links() }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Массовые действия для премиум -->
                        @if(auth()->user()->isPremium())
                        <div class="mx-3 mb-3" id="bulkActions" style="display: none;">
                            <div class="alert alert-warning mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-check-square me-2"></i><strong>Выбрано транзакций:</strong> <span id="selectedCount">0</span></span>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                                        <i class="fas fa-trash me-1"></i>Удалить выбранные
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-receipt fa-4x text-muted"></i>
                            </div>
                            <h5 class="text-muted">Нет транзакций</h5>
                            <p class="text-muted mb-4">Добавьте первую транзакцию, чтобы начать отслеживать финансы</p>
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
                    <p>Вы уверены, что хотите удалить эту транзакцию?</p>
                    <small class="text-muted">Это действие нельзя отменить.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Удалить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteTransaction(id) {
    const form = document.getElementById('deleteForm');
    form.action = `/transactions/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

@if(auth()->user()->isPremium())
// Массовые действия
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.transaction-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActions();
});

document.querySelectorAll('.transaction-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const selected = document.querySelectorAll('.transaction-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = selected.length;
    } else {
        bulkActions.style.display = 'none';
    }
}

function bulkDelete() {
    const selected = document.querySelectorAll('.transaction-checkbox:checked');
    if (selected.length === 0) return;
    
    if (confirm(`Вы уверены, что хотите удалить ${selected.length} транзакций?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("transactions.bulk-delete") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        selected.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'transaction_ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}
@endif
</script>
@endsection
