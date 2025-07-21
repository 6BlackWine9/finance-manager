<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'type',
        'date',
        'description'
    ];

    protected $dates = ['date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
{
    return $this->belongsTo(Category::class)->withDefault([
        'name' => 'Удаленная категория',
        'color' => '#6c757d',
        'icon' => 'fa-question-circle'
    ]);
}

    protected $casts = [
    'date' => 'date',
    'amount' => 'decimal:2',
];

public function scopeIncome($query)
{
    return $query->where('type', 'income');
}

public function scopeExpense($query)
{
    return $query->where('type', 'expense');
}

    public function scopeForUser($query, $user = null)
    {
        return $query->where('user_id', $user ? $user->id : auth()->id());
    }
    
}