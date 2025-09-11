<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budget;
use App\Models\Bill;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        set_time_limit(30); // Prevent infinite loops
        
        $user = Auth::user();
        
        // Get date range
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Income vs Expenses
        $incomeVsExpenses = $this->getIncomeVsExpenses($user->id, $startDate, $endDate);
        
        // Category Breakdown
        $categoryBreakdown = $this->getCategoryBreakdown($user->id, $startDate, $endDate);
        
        // Monthly Trend (last 6 months)
        $monthlyTrend = $this->getMonthlyTrend($user->id);
        
        // Top Expenses
        $topExpenses = $this->getTopExpenses($user->id, $startDate, $endDate);
        
        // Account Balances
        $accountBalances = $this->getAccountBalances($user->id);
        
        // Budget Performance
        $budgetPerformance = $this->getBudgetPerformance($user->id, $startDate, $endDate);
        
        // Savings Progress
        $savingsProgress = $this->getSavingsProgress($user->id);
        
        // Financial Summary
        $summary = $this->getFinancialSummary($user->id, $startDate, $endDate);
        
        return view('reports.index', compact(
            'incomeVsExpenses',
            'categoryBreakdown',
            'monthlyTrend',
            'topExpenses',
            'accountBalances',
            'budgetPerformance',
            'savingsProgress',
            'summary',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Get income vs expenses data.
     */
    private function getIncomeVsExpenses($userId, $startDate, $endDate)
    {
        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
            
        $expenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        return [
            'income' => $income,
            'expenses' => abs($expenses),
            'net' => $income - abs($expenses),
        ];
    }
    
    /**
     * Get category breakdown data.
     */
    private function getCategoryBreakdown($userId, $startDate, $endDate)
    {
        return Transaction::where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy('category_id', 'categories.name', 'categories.color')
            ->select(
                'categories.name',
                'categories.color',
                DB::raw('SUM(ABS(transactions.amount)) as total')
            )
            ->orderBy('total', 'desc')
            ->get();
    }
    
    /**
     * Get monthly trend data.
     */
    private function getMonthlyTrend($userId)
    {
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        
        $monthlyData = Transaction::where('user_id', $userId)
            ->where('transaction_date', '>=', $sixMonthsAgo)
            ->get()
            ->groupBy(function($transaction) {
                return $transaction->transaction_date->format('Y-m');
            })
            ->map(function($monthTransactions) {
                return $monthTransactions->groupBy('type')->map(function($typeTransactions) {
                    return $typeTransactions->sum('amount');
                });
            });
        
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            
            $monthDataForMonth = $monthlyData->get($month, collect());
            
            $trend[] = [
                'month' => $monthLabel,
                'income' => $monthDataForMonth->get('income', 0),
                'expenses' => abs($monthDataForMonth->get('expense', 0)),
            ];
        }
        
        return $trend;
    }
    
    
    /**
     * Get top expenses.
     */
    private function getTopExpenses($userId, $startDate, $endDate)
    {
        return Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('amount')
            ->take(10)
            ->with('category')
            ->get();
    }
    
    /**
     * Get account balances.
     */
    private function getAccountBalances($userId)
    {
        return \App\Models\Account::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('balance', 'desc')
            ->get();
    }
    
    /**
     * Get budget performance.
     */
    private function getBudgetPerformance($userId, $startDate, $endDate)
    {
        $monthDate = Carbon::parse($startDate)->startOfMonth();
        
        $budgets = Budget::where('user_id', $userId)
            ->where('month_year', $monthDate)
            ->with('category')
            ->get();
        
        $spending = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthDate, $monthDate->copy()->endOfMonth()])
            ->groupBy('category_id')
            ->select('category_id', DB::raw('SUM(ABS(amount)) as total'))
            ->pluck('total', 'category_id');
        
        $performance = [];
        foreach ($budgets as $budget) {
            $spent = $spending->get($budget->category_id, 0);
            $remaining = $budget->amount - $spent;
            $performance[] = [
                'category' => $budget->category->name,
                'budget' => $budget->amount,
                'spent' => $spent,
                'remaining' => $remaining,
                'percentage' => $budget->amount > 0 ? round(($spent / $budget->amount) * 100, 1) : 0,
            ];
        }
        
        return $performance;
    }
    
    /**
     * Get savings progress.
     */
    private function getSavingsProgress($userId)
    {
        return SavingsGoal::where('user_id', $userId)
            ->where('is_completed', false)
            ->orderBy('target_date')
            ->get()
            ->map(function ($goal) {
                $goal->progress_percentage = $goal->target_amount > 0 
                    ? round(($goal->current_amount / $goal->target_amount) * 100, 1) 
                    : 0;
                return $goal;
            });
    }
    
    /**
     * Get financial summary.
     */
    private function getFinancialSummary($userId, $startDate, $endDate)
    {
        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
            
        $totalExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
            
        $totalSavings = SavingsGoal::where('user_id', $userId)
            ->sum('current_amount');
            
        $totalBills = Bill::where('user_id', $userId)
            ->where('frequency', 'monthly')
            ->sum('amount');
            
        $accountsTotal = \App\Models\Account::where('user_id', $userId)
            ->where('is_active', true)
            ->sum('balance');
        
        $daysInPeriod = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $avgDailySpending = $daysInPeriod > 0 ? abs($totalExpenses) / $daysInPeriod : 0;
        
        return [
            'total_income' => $totalIncome,
            'total_expenses' => abs($totalExpenses),
            'net_income' => $totalIncome - abs($totalExpenses),
            'total_savings' => $totalSavings,
            'monthly_bills' => $totalBills,
            'net_worth' => $accountsTotal,
            'avg_daily_spending' => $avgDailySpending,
            'savings_rate' => $totalIncome > 0 ? round((($totalIncome - abs($totalExpenses)) / $totalIncome) * 100, 1) : 0,
        ];
    }
    
    /**
     * Export report as CSV.
     */
    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['category', 'account'])
            ->orderBy('transaction_date', 'desc')
            ->get();
        
        $filename = 'transactions_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Date', 'Description', 'Category', 'Account', 'Type', 'Amount']);
            
            // Data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_date->format('Y-m-d'),
                    $transaction->description ?: '',
                    $transaction->category->name,
                    $transaction->account->name,
                    ucfirst($transaction->type),
                    $transaction->amount,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
