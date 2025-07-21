<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $user->transactions()->with('category');

        // Фильтрация
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->orderBy('date', 'desc')->paginate(20);
        $categories = $user->categories()->orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'categories'));
    }

    public function create()
    {
        $user = auth()->user();
        $categories = $user->categories()->orderBy('name')->get();
        
        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        // Проверяем что категория принадлежит пользователю
        $category = $user->categories()->findOrFail($request->category_id);

        $user->transactions()->create([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return redirect()->route('transactions.index')
            ->with('success', 'Транзакция успешно добавлена.');
    }

    public function edit(Transaction $transaction)
    {
        // Проверяем что транзакция принадлежит текущему пользователю
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $user = auth()->user();
        $categories = $user->categories()->orderBy('name')->get();
        
        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        // Проверяем что транзакция принадлежит текущему пользователю
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $user = auth()->user();
        
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        // Проверяем что категория принадлежит пользователю
        $category = $user->categories()->findOrFail($request->category_id);

        $transaction->update([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return redirect()->route('transactions.index')
            ->with('success', 'Транзакция успешно обновлена.');
    }

    public function destroy(Transaction $transaction)
    {
        // Проверяем что транзакция принадлежит текущему пользователю
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Транзакция успешно удалена.');
    }

    // ПРЕМИУМ: Дублирование транзакции
    public function duplicate(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $user = auth()->user();
        
        if (!$user->isPremium()) {
            return redirect()->route('subscription')
                ->with('error', 'Дублирование транзакций доступно только для премиум пользователей.');
        }

        if (!$user->canCreateTransaction()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Достигнут лимит транзакций.');
        }

        $user->transactions()->create([
            'category_id' => $transaction->category_id,
            'amount' => $transaction->amount,
            'description' => $transaction->description . ' (копия)',
            'date' => now()->toDateString(),
        ]);

        return redirect()->route('transactions.index')
            ->with('success', 'Транзакция успешно дублирована.');
    }

    // ПРЕМИУМ: Массовое удаление
    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isPremium()) {
            return redirect()->route('subscription')
                ->with('error', 'Массовое удаление доступно только для премиум пользователей.');
        }

        $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:transactions,id'
        ]);

        $deleted = $user->transactions()
            ->whereIn('id', $request->transaction_ids)
            ->delete();

        return redirect()->route('transactions.index')
            ->with('success', "Удалено транзакций: {$deleted}");
    }
}
