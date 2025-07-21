@extends('layouts.app')

@section('title', 'Редактировать транзакцию')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Редактировать транзакцию</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="category_id" class="form-label fw-bold">Категория</label>
                            <select class="form-select form-select-lg @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="">Выберите категорию</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}
                                            data-color="{{ $category->color }}"
                                            data-type="{{ $category->type }}">
                                        {{ $category->name }} ({{ $category->type === 'income' ? 'Доход' : 'Расход' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="amount" class="form-label fw-bold">Сумма</label>
                            <div class="input-group input-group-lg">
                                <input type="number" step="0.01" min="0.01" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount', $transaction->amount) }}" 
                                       placeholder="0.00" required>
                                <span class="input-group-text">₽</span>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Описание</label>
                            <input type="text" class="form-control form-control-lg @error('description') is-invalid @enderror" 
                                   id="description" name="description" value="{{ old('description', $transaction->description) }}" 
                                   placeholder="Описание транзакции" required>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="date" class="form-label fw-bold">Дата</label>
                            <input type="date" class="form-control form-control-lg @error('date') is-invalid @enderror" 
                                   id="date" name="date" value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
