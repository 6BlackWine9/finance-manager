<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTransactionLimit
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Премиум пользователи не имеют лимитов
        if ($user->isPremium()) {
            return $next($request);
        }
        
        // Проверяем лимит транзакций для бесплатных пользователей
        if (!$user->canCreateTransaction()) {
            return redirect()->route('subscription')
                ->with('error', 'Достигнут лимит транзакций для бесплатного тарифа. Оформите премиум подписку для неограниченного использования.');
        }
        
        return $next($request);
    }
}
