@extends('layouts.app')

@section('title', 'Добавить категорию')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus me-2"></i>Добавить категорию</h4>
                </div>
                <div class="card-body">
                    @if(!auth()->user()->canCreateCategory())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Внимание!</strong> Вы достигли лимита категорий для бесплатного тарифа ({{ auth()->user()->getCategoriesLimit() }} категорий).
                            <a href="{{ route('subscription') }}" class="alert-link">Получите Премиум</a> для неограниченного количества категорий.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('categories.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">Название категории</label>
                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" 
                                   placeholder="Введите название категории" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="type" class="form-label fw-bold">Тип категории</label>
                            <select class="form-select form-select-lg @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Выберите тип</option>
                                <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>
                                    Доход
                                </option>
                                <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>
                                    Расход
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="color" class="form-label fw-bold">Цвет категории</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" class="form-control form-control-color form-control-lg @error('color') is-invalid @enderror" 
                                       id="color" name="color" value="{{ old('color', '#007bff') }}" 
                                       style="width: 80px; height: 50px;" required>
                                <div class="flex-grow-1">
                                    <div class="form-text">Выберите цвет, который будет использоваться для отображения этой категории</div>
                                    <div class="mt-2">
                                        <span class="badge rounded-pill fs-6 px-3 py-2" 
                                              style="background-color: {{ old('color', '#007bff') }}; color: white;" 
                                              id="colorPreview">
                                            <span id="categoryName">{{ old('name', 'Название категории') }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary btn-lg" {{ !auth()->user()->canCreateCategory() ? 'disabled' : '' }}>
                                <i class="fas fa-save me-2"></i>Создать категорию
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Обновление превью цвета и названия
document.getElementById('color').addEventListener('input', function() {
    const preview = document.getElementById('colorPreview');
    preview.style.backgroundColor = this.value;
});

document.getElementById('name').addEventListener('input', function() {
    const categoryName = document.getElementById('categoryName');
    categoryName.textContent = this.value || 'Название категории';
});
</script>
@endsection