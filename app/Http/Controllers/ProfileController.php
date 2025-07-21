<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Response;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        
        // Статистика пользователя
        $stats = [
            'total_transactions' => $user->transactions()->count(),
            'total_categories' => $user->categories()->count(),
            'total_income' => $user->transactions()
                ->whereHas('category', function($query) {
                    $query->where('type', 'income');
                })
                ->sum('amount'),
            'total_expenses' => $user->transactions()
                ->whereHas('category', function($query) {
                    $query->where('type', 'expense');
                })
                ->sum('amount'),
            'account_created' => $user->created_at,
            'last_transaction' => $user->transactions()->latest('date')->first()
        ];
        
        return view('profile.show', compact('stats'));
    }

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Профиль успешно обновлен.');
    }

    public function editPassword()
    {
        return view('profile.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Пароль успешно изменен.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();
        
        // Удаляем все связанные данные
        $user->transactions()->delete();
        $user->categories()->delete();
        $user->payments()->delete();
        
        auth()->logout();
        $user->delete();

        return redirect('/')->with('success', 'Аккаунт успешно удален.');
    }

    // ПРЕМИУМ: Экспорт в Excel
    public function exportData(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isPremium()) {
            return redirect()->route('subscription')
                ->with('error', 'Экспорт данных доступен только для премиум пользователей.');
        }

        return $this->exportToExcel($user);
    }

    private function exportToExcel($user)
    {
        $filename = 'finance_export_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.xls';
        
        $html = $this->generateExcelHtml($user);
        
        return Response::make($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ]);
    }

    private function generateExcelHtml($user)
    {
        try {
            $transactions = $user->transactions()->with('category')->orderBy('date', 'desc')->get();
            $categories = $user->categories()->withCount('transactions')->withSum('transactions', 'amount')->get();
            
            // Вычисляем статистику
            $totalIncome = $user->transactions()
                ->whereHas('category', function($q) { 
                    $q->where('type', 'income'); 
                })->sum('amount');
                
            $totalExpenses = $user->transactions()
                ->whereHas('category', function($q) { 
                    $q->where('type', 'expense'); 
                })->sum('amount');
            
            $html = '
            <html>
            <head>
                <meta charset="utf-8">
                <title>Экспорт финансовых данных</title>
                <style>
                    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                    th { background-color: #4472C4; color: white; font-weight: bold; }
                    .income { color: green; }
                    .expense { color: red; }
                    .header { font-size: 18px; font-weight: bold; margin: 20px 0 10px 0; }
                </style>
            </head>
            <body>
                <div class="header">Финансовый отчет - ' . htmlspecialchars($user->name) . '</div>
                <p>Дата создания: ' . date('d.m.Y H:i') . '</p>
                
                <div class="header">Общая статистика</div>
                <table>
                    <tr><th>Показатель</th><th>Значение</th></tr>
                    <tr><td>Общий баланс</td><td>' . number_format($user->getBalance(), 2) . ' ₽</td></tr>
                    <tr><td>Всего доходов</td><td class="income">' . number_format($totalIncome, 2) . ' ₽</td></tr>
                    <tr><td>Всего расходов</td><td class="expense">' . number_format($totalExpenses, 2) . ' ₽</td></tr>
                    <tr><td>Количество транзакций</td><td>' . $user->transactions()->count() . '</td></tr>
                    <tr><td>Количество категорий</td><td>' . $user->categories()->count() . '</td></tr>
                    <tr><td>Дата регистрации</td><td>' . $user->created_at->format('d.m.Y') . '</td></tr>
                    <tr><td>Статус подписки</td><td>' . ($user->isPremium() ? 'Премиум до ' . $user->premium_until->format('d.m.Y') : 'Бесплатный') . '</td></tr>
                </table>
                
                <div class="header">Категории</div>
                <table>
                    <tr>
                        <th>Название</th>
                        <th>Тип</th>
                        <th>Цвет</th>
                        <th>Количество транзакций</th>
                        <th>Общая сумма</th>
                    </tr>';
            
            foreach ($categories as $category) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($category->name) . '</td>
                    <td>' . ($category->type === 'income' ? 'Доход' : 'Расход') . '</td>
                    <td>' . htmlspecialchars($category->color) . '</td>
                    <td>' . $category->transactions_count . '</td>
                    <td class="' . $category->type . '">' . number_format($category->transactions_sum_amount ?? 0, 2) . ' ₽</td>
                </tr>';
            }
            
            $html .= '</table>
                
                <div class="header">Транзакции</div>
                <table>
                    <tr>
                        <th>Дата</th>
                        <th>Категория</th>
                        <th>Тип</th>
                        <th>Описание</th>
                        <th>Сумма</th>
                    </tr>';
            
            foreach ($transactions as $transaction) {
                $html .= '<tr>
                    <td>' . $transaction->date->format('d.m.Y') . '</td>
                    <td>' . htmlspecialchars($transaction->category->name) . '</td>
                    <td>' . ($transaction->category->type === 'income' ? 'Доход' : 'Расход') . '</td>
                    <td>' . htmlspecialchars($transaction->description) . '</td>
                    <td class="' . $transaction->category->type . '">' . number_format($transaction->amount, 2) . ' ₽</td>
                </tr>';
            }
            
            $html .= '</table>
            </body>
            </html>';
            
            return $html;
            
        } catch (\Exception $e) {
            \Log::error('Excel export error: ' . $e->getMessage());
            return '<html><body><h1>Ошибка экспорта: ' . $e->getMessage() . '</h1></body></html>';
        }
    }
}
