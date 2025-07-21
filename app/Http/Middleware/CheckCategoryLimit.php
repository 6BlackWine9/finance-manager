<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCategoryLimit
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Премиум пользователи не имеют лимитов
        if ($user->isPremium()) {
            return $next($request);
        }
        
        // Проверяем лимит категорий для бесплатных пользователей
        if (!$user->canCreateCategory()) {
            return redirect()->route('subscription')
                ->with('error', 'Достигнут лимит категорий для бесплатного тарифа (максимум 5). Оформите премиум подписку для неограниченного количества категорий.');
        }
        
        return $next($request);
    }
}
