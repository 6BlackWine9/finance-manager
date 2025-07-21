@extends('layouts.app')

@section('title', 'Редактировать категорию')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Редактировать категорию</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('categories.update', $category) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">Название категории</label>
                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $category->name) }}" 
                                   placeholder="Введите название категории" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="type" class="form-label fw-bold">Тип категории</label>
                            <select class="form-select form-select-lg @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="income" {{ old('type', $category->type) === 'income' ? 'selected' : '' }}>
                                    Доход
                                </option>
                                <option value="expense" {{ old('type', $category->type) === 'expense' ? 'selected' : '' }}>
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
                                       id="color" name="color" value="{{ old('color', $category->color) }}" 
                                       style="width: 80px; height: 50px;" required>
                                <div class="flex-grow-1">
                                    <div class="form-text">Выберите цвет, который будет использоваться для отображения этой категории</div>
                                    <div class="mt-2">
                                        <span class="badge rounded-pill fs-6 px-3 py-2" 
                                              style="background-color: {{ old('color', $category->color) }}; color: white;" 
                                              id="colorPreview">
                                            <span id="categoryName">{{ old('name', $category->name) }}</span>
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
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
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
