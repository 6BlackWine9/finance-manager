@extends('layouts.app')

@section('title', 'Детали транзакции')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Детали транзакции</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Тип:</div>
                        <div class="col-md-8">
                            @if($transaction->type === 'income')
                                <span class="badge bg-success">Доход</span>
                            @else
                                <span class="badge bg-danger">Расход</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Категория:</div>
                        <div class="col-md-8">
                            <span class="badge" style="background-color: {{ $transaction->category->color }};">
                                <i class="{{ $transaction->category->icon }} me-1"></i>
                                {{ $transaction->category->name }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Сумма:</div>
                        <div class="col-md-8 {{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                            {{ number_format($transaction->amount, 2) }} ₽
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Дата:</div>
                        <div class="col-md-8">{{ $transaction->date->format('d.m.Y') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Описание:</div>
                        <div class="col-md-8">{{ $transaction->description ?? '—' }}</div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-8 offset-md-4">
                            <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-primary">
                                Редактировать
                            </a>
                            <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены?')">
                                    Удалить
                                </button>
                            </form>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
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