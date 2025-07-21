<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $period = $request->get('period', 'month');
            
            // Определяем период для фильтрации
            $dateRange = $this->getDateRange($period);
            
            // Базовая статистика
            $stats = $this->getBasicStats($user, $dateRange);
            
            // Данные для базовых графиков
            $chartData = $this->getBasicChartData($user, $dateRange, $period);
            
            // Последние транзакции
            $recentTransactions = $user->transactions()
                ->with('category')
                ->orderBy('date', 'desc')
                ->limit(5)
                ->get();
            
            // Топ категории по расходам
            $topExpenseCategories = $this->getTopCategories($user, $dateRange, 'expense', 3);
            
            // Информация о лимитах
            $limitInfo = $user->getTransactionLimitInfo();
            
            // Премиум функции (только для премиум пользователей)
            $premiumFeatures = $user->isPremium() ? $this->getPremiumFeatures($user, $dateRange) : null;
            
            return view('dashboard.index', compact(
                'stats',
                'chartData',
                'recentTransactions',
                'topExpenseCategories',
                'limitInfo',
                'premiumFeatures',
                'period'
            ));
            
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Ошибка загрузки дашборда');
        }
    }

    private function getDateRange($period)
    {
        switch ($period) {
            case 'week':
                return [
                    'start' => now()->startOfWeek(),
                    'end' => now()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth()
                ];
            default:
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth()
                ];
        }
    }

    private function getBasicStats($user, $dateRange)
    {
        // Доходы и расходы за выбранный период
        $periodIncome = $user->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'income');
            })
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->sum('amount');

        $periodExpenses = $user->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'expense');
            })
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->sum('amount');

        // Общий баланс (все время)
        $totalBalance = $user->getBalance();
        
        $transactionsCount = $user->transactions()
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->count();

        return [
            'income' => $periodIncome,
            'expenses' => $periodExpenses,
            'balance' => $totalBalance,
            'transactions_count' => $transactionsCount,
            'categories_count' => $user->categories()->count()
        ];
    }

    private function getBasicChartData($user, $dateRange, $period)
    {
        $format = $period === 'week' ? '%Y-%m-%d' : '%Y-%m-%d';
        
        $incomeData = $user->transactions()
            ->select(
                DB::raw("DATE_FORMAT(date, '{$format}') as period"),
                DB::raw('SUM(amount) as total')
            )
            ->whereHas('category', function($query) {
                $query->where('type', 'income');
            })
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->groupBy('period')
            ->orderBy('period')
            ->limit(30)
            ->get()
            ->pluck('total', 'period');

        $expenseData = $user->transactions()
            ->select(
                DB::raw("DATE_FORMAT(date, '{$format}') as period"),
                DB::raw('SUM(amount) as total')
            )
            ->whereHas('category', function($query) {
                $query->where('type', 'expense');
            })
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->groupBy('period')
            ->orderBy('period')
            ->limit(30)
            ->get()
            ->pluck('total', 'period');

        return [
            'income' => $incomeData,
            'expenses' => $expenseData,
            'labels' => $this->generateBasicLabels($dateRange, $period)
        ];
    }

    private function generateBasicLabels($dateRange, $period)
    {
        $labels = [];
        $current = $dateRange['start']->copy();
        $count = 0;
        $maxLabels = $period === 'week' ? 7 : 15; // Ограничиваем количество меток

        while ($current <= $dateRange['end'] && $count < $maxLabels) {
            if ($period === 'week') {
                $labels[] = $current->format('d.m'); // Только день и месяц для недели
            } else {
                $labels[] = $current->format('d.m'); // Только день и месяц для месяца
            }
            $current->addDay();
            $count++;
        }

        return $labels;
    }

    private function getTopCategories($user, $dateRange, $type, $limit = 5)
    {
        return $user->transactions()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->whereHas('category', function($query) use ($type) {
                $query->where('type', $type);
            })
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getPremiumFeatures($user, $dateRange)
    {
        try {
            return [
                'savings_rate' => $this->calculateSavingsRate($user, $dateRange),
                'spending_trend' => $this->getSpendingTrend($user),
                'category_insights' => $this->getCategoryInsights($user, $dateRange),
            ];
        } catch (\Exception $e) {
            Log::error('Premium features error: ' . $e->getMessage());
            return null;
        }
    }

    private function calculateSavingsRate($user, $dateRange)
    {
        $income = $user->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'income');
            })
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->sum('amount');

        $expenses = $user->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'expense');
            })
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->sum('amount');

        if ($income == 0) return 0;

        return round((($income - $expenses) / $income) * 100, 2);
    }

    private function getSpendingTrend($user)
    {
        $currentMonth = $user->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'expense');
            })
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $previousMonth = $user->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'expense');
            })
            ->whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('amount');

        if ($previousMonth == 0) return 0;

        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
    }

    private function getCategoryInsights($user, $dateRange)
    {
        return $user->categories()
            ->withSum(['transactions' => function($query) use ($dateRange) {
                $query->whereBetween('date', [$dateRange['start'], $dateRange['end']]);
            }], 'amount')
            ->having('transactions_sum_amount', '>', 0)
            ->orderBy('transactions_sum_amount', 'desc')
            ->limit(5)
            ->get();
    }

    public function updatePeriod(Request $request)
    {
        $period = $request->get('period', 'month');
        return redirect()->route('dashboard', ['period' => $period]);
    }

    // ПРЕМИУМ: Упрощенная аналитика
    public function analytics(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user->isPremium()) {
                return redirect()->route('subscription')
                    ->with('error', 'Расширенная аналитика доступна только для премиум пользователей.');
            }

            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($period);
            
            Log::info('Loading analytics for user: ' . $user->id);
            
            // Упрощенная аналитика с проверками
            $analytics = [];
            
            // Расходы по категориям
            try {
                $analytics['spending_by_category'] = $this->getSpendingByCategory($user, $dateRange);
                Log::info('Spending by category loaded: ' . $analytics['spending_by_category']->count() . ' items');
            } catch (\Exception $e) {
                Log::error('Error loading spending by category: ' . $e->getMessage());
                $analytics['spending_by_category'] = collect();
            }
            
            // Источники доходов
            try {
                $analytics['income_sources'] = $this->getIncomeSources($user, $dateRange);
                Log::info('Income sources loaded: ' . $analytics['income_sources']->count() . ' items');
            } catch (\Exception $e) {
                Log::error('Error loading income sources: ' . $e->getMessage());
                $analytics['income_sources'] = collect();
            }
            
            // Анализ бюджета
            try {
                $analytics['budget_analysis'] = $this->getBudgetAnalysis($user, $dateRange);
                Log::info('Budget analysis loaded');
            } catch (\Exception $e) {
                Log::error('Error loading budget analysis: ' . $e->getMessage());
                $analytics['budget_analysis'] = null;
            }
            
            // Прогноз
            try {
                $analytics['forecasting'] = $this->getSpendingForecast($user);
                Log::info('Forecasting loaded');
            } catch (\Exception $e) {
                Log::error('Error loading forecasting: ' . $e->getMessage());
                $analytics['forecasting'] = null;
            }

            Log::info('Analytics loaded successfully');
            
            return view('dashboard.analytics', compact('analytics', 'period'));
            
        } catch (\Exception $e) {
            Log::error('Analytics error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('dashboard')->with('error', 'Ошибка загрузки аналитики. Попробуйте позже.');
        }
    }

    private function getSpendingByCategory($user, $dateRange)
    {
        return $user->transactions()
            ->select('categories.name', 'categories.color', DB::raw('SUM(transactions.amount) as total'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'expense')
            ->whereBetween('transactions.date', [$dateRange['start'], $dateRange['end']])
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderBy('total', 'desc')
            ->limit(10) // Ограничиваем количество
            ->get();
    }

    private function getIncomeSources($user, $dateRange)
    {
        return $user->transactions()
            ->select('categories.name', 'categories.color', DB::raw('SUM(transactions.amount) as total'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'income')
            ->whereBetween('transactions.date', [$dateRange['start'], $dateRange['end']])
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderBy('total', 'desc')
            ->limit(10) // Ограничиваем количество
            ->get();
    }

    private function getBudgetAnalysis($user, $dateRange)
    {
        $avgMonthlyExpenses = $user->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'expense');
            })
            ->where('date', '>=', now()->subMonths(6))
            ->avg(DB::raw('amount'));

        $currentMonthExpenses = $user->transactions()
            ->whereHas('category', function($query) {
                $query->where('type', 'expense');
            })
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        
        return [
            'average_monthly' => $avgMonthlyExpenses ?? 0,
            'current_month' => $currentMonthExpenses,
            'variance' => $currentMonthExpenses - ($avgMonthlyExpenses ?? 0),
            'status' => $currentMonthExpenses > ($avgMonthlyExpenses ?? 0) ? 'over' : 'under'
        ];
    }

    private function getSpendingForecast($user)
    {
        $lastThreeMonths = [];
        for ($i = 2; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $amount = $user->transactions()
                ->whereHas('category', function($query) {
                    $query->where('type', 'expense');
                })
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');
            $lastThreeMonths[] = $amount;
        }

        $avgSpending = array_sum($lastThreeMonths) / count($lastThreeMonths);
        $trend = count($lastThreeMonths) > 1 ? 
            ($lastThreeMonths[count($lastThreeMonths) - 1] - $lastThreeMonths[0]) / (count($lastThreeMonths) - 1) : 0;

        return [
            'next_month_forecast' => $avgSpending + $trend,
            'trend' => $trend,
            'confidence' => 'medium'
        ];
    }
}
