<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;

// Главная страница
Route::get('/', function () {
    return view('welcome');
});

// Аутентификация
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Webhook для Stripe (должен быть вне middleware auth)
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');

// Защищенные маршруты
Route::middleware(['auth'])->group(function () {
    
    // Дашборд
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update-period', [DashboardController::class, 'updatePeriod'])->name('dashboard.update-period');
    
    // Аналитика (премиум функция)
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    
    // Транзакции
    Route::resource('transactions', TransactionController::class)->except(['show']);
    Route::post('/transactions/bulk-delete', [TransactionController::class, 'bulkDelete'])->name('transactions.bulk-delete');
    Route::post('/transactions/{transaction}/duplicate', [TransactionController::class, 'duplicate'])->name('transactions.duplicate');

    // Категории
    Route::resource('categories', CategoryController::class)->except(['show']);
    
    // Подписки и платежи
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription');
    Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
    
    // Профиль пользователя
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::get('/export', [ProfileController::class, 'exportData'])->name('export');
        Route::get('/password', [ProfileController::class, 'editPassword'])->name('password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });
});

// Тестовые маршруты (только в режиме разработки)
if (config('app.debug')) {
    Route::middleware('auth')->prefix('test')->name('test.')->group(function () {
        
        // Тестовые платежи
        Route::get('/payment-success/{paymentId}', function($paymentId) {
            $user = auth()->user();
            
            try {
                if ($paymentId === 'latest') {
                    // Месячная подписка
                    $payment = \App\Models\Payment::create([
                        'user_id' => $user->id,
                        'amount' => 299,
                        'currency' => 'rub',
                        'status' => 'paid',
                        'type' => 'subscription',
                        'stripe_payment_id' => 'test_monthly_' . time(),
                        'metadata' => ['plan' => 'monthly', 'months' => 1]
                    ]);
                    $months = 1;
                    $message = 'Тестовая месячная подписка активирована!';
                    
                } elseif ($paymentId === 'yearly') {
                    // Годовая подписка
                    $payment = \App\Models\Payment::create([
                        'user_id' => $user->id,
                        'amount' => 2990,
                        'currency' => 'rub',
                        'status' => 'paid',
                        'type' => 'subscription',
                        'stripe_payment_id' => 'test_yearly_' . time(),
                        'metadata' => ['plan' => 'yearly', 'months' => 12]
                    ]);
                    $months = 12;
                    $message = 'Тестовая годовая подписка активирована!';
                    
                } else {
                    $payment = \App\Models\Payment::find($paymentId);
                    if (!$payment) {
                        throw new \Exception('Платеж не найден');
                    }
                    $months = $payment->metadata['months'] ?? 1;
                    $message = 'Тестовая подписка активирована!';
                }
                
                if ($payment && $payment->user_id === $user->id) {
                    $payment->update(['status' => 'paid']);
                    $user->activatePremium($months);
                    
                    return redirect()->route('dashboard')
                        ->with('success', $message);
                }
                
                throw new \Exception('Ошибка обработки платежа');
                
            } catch (\Exception $e) {
                \Log::error('Test payment error: ' . $e->getMessage());
                return redirect()->route('subscription')
                    ->with('error', 'Ошибка активации тестовой подписки: ' . $e->getMessage());
            }
        })->name('payment.success');

        // Деактивация премиум для тестирования
        Route::get('/deactivate-premium', function() {
            try {
                $user = auth()->user();
                $user->update([
                    'premium_until' => null
                ]);
                
                return redirect()->route('subscription')
                    ->with('info', 'Премиум подписка деактивирована для тестирования.');
                    
            } catch (\Exception $e) {
                return redirect()->route('subscription')
                    ->with('error', 'Ошибка деактивации: ' . $e->getMessage());
            }
        })->name('deactivate.premium');
    });
}
