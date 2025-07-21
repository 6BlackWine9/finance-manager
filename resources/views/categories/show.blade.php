@extends('layouts.app')

@section('title', 'Детали категории')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Детали категории</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Название:</div>
                        <div class="col-md-8">{{ $category->name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Тип:</div>
                        <div class="col-md-8">
                            @if($category->type === 'income')
                                <span class="badge bg-success">Доход</span>
                            @else
                                <span class="badge bg-danger">Расход</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Цвет:</div>
                        <div class="col-md-8">
                            <span class="badge" style="background-color: {{ $category->color }};">&nbsp;&nbsp;&nbsp;</span>
                            {{ $category->color }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Иконка:</div>
                        <div class="col-md-8">
                            <i class="{{ $category->icon }} fa-lg me-2"></i>
                            {{ $category->icon }}
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-8 offset-md-4">
                            <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-primary">
                                Редактировать
                            </a>
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены?')">
                                    Удалить
                                </button>
                            </form>
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                                Назад
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection