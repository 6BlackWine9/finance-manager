<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'premium_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'premium_until' => 'datetime',
        'password' => 'hashed',
    ];

    // Отношения
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Методы для работы с премиум подпиской
    public function isPremium()
    {
        return $this->premium_until && $this->premium_until->isFuture();
    }

    public function activatePremium($months = 1)
    {
        $startDate = $this->isPremium() ? $this->premium_until : now();
        $this->premium_until = $startDate->addMonths($months);
        $this->save();
    }

    public function deactivatePremium()
    {
        $this->premium_until = null;
        $this->save();
    }

    public function getPremiumDaysLeft()
    {
        if (!$this->isPremium()) {
            return 0;
        }
        return now()->diffInDays($this->premium_until, false);
    }

    // Методы для лимитов
    public function getTransactionLimitInfo()
    {
        if ($this->isPremium()) {
            return [
                'limit' => null,
                'current' => $this->transactions()->count(),
                'remaining' => null,
                'unlimited' => true
            ];
        }

        $limit = 100; // Лимит для бесплатных пользователей
        $current = $this->transactions()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'limit' => $limit,
            'current' => $current,
            'remaining' => max(0, $limit - $current),
            'unlimited' => false
        ];
    }

    public function canCreateTransaction()
    {
        if ($this->isPremium()) {
            return true;
        }

        $limitInfo = $this->getTransactionLimitInfo();
        return $limitInfo['remaining'] > 0;
    }

    public function getCategoriesLimit()
    {
        return $this->isPremium() ? null : 5;
    }

    public function canCreateCategory()
    {
        if ($this->isPremium()) {
            return true;
        }

        return $this->categories()->count() < $this->getCategoriesLimit();
    }

    // Статистические методы
    public function getTotalSpentAttribute()
    {
        return $this->payments()->where('status', 'paid')->sum('amount') / 100; // Конвертируем из копеек
    }

    public function getMonthlyIncome()
    {
        return $this->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'income');
            })
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
    }

    public function getMonthlyExpenses()
    {
        return $this->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'expense');
            })
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
    }

    public function getBalance()
    {
        $income = $this->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'income');
            })
            ->sum('amount');

        $expenses = $this->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'expense');
            })
            ->sum('amount');

        return $income - $expenses;
    }
}
