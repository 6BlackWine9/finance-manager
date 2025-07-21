<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $categories = $user->categories()
            ->withCount('transactions')
            ->withSum('transactions', 'amount')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $user->categories()->create([
            'name' => $request->name,
            'type' => $request->type,
            'color' => $request->color,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Категория успешно добавлена.');
    }

    public function edit(Category $category)
    {
        // Проверяем что категория принадлежит текущему пользователю
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        // Проверяем что категория принадлежит текущему пользователю
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $category->update([
            'name' => $request->name,
            'type' => $request->type,
            'color' => $request->color,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Категория успешно обновлена.');
    }

    public function destroy(Category $category)
    {
        // Проверяем что категория принадлежит текущему пользователю
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        // Проверяем есть ли транзакции в этой категории
        if ($category->transactions()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Нельзя удалить категорию, в которой есть транзакции.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Категория успешно удалена.');
    }
}
