<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPremium
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        if (!$user->isPremium()) {
            return redirect()->route('subscription')
                ->with('error', 'Эта функция доступна только для премиум пользователей.');
        }

        return $next($request);
    }
}