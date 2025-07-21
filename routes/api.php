<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Получение категорий по типу
    Route::get('/categories', function (Request $request) {
        $request->validate([
            'type' => 'required|in:income,expense'
        ]);

        return $request->user()->categories()
            ->where('type', $request->type)
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'icon']);
    });

    // Получение статистики по транзакциям
    Route::get('/transactions/stats', [TransactionController::class, 'stats']);

    // API для работы с категориями
    Route::apiResource('categories', CategoryController::class)->except(['create', 'edit']);

    // API для работы с транзакциями
    Route::apiResource('transactions', TransactionController::class)->except(['create', 'edit']);
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});