<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Bill;
use App\Models\SavingsGoal;
use App\Models\Account;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardDataService
{
    /**
     * Get comprehensive dashboard data for a user
     */
    public function getDashboardData(User $user)
    {
        return Cache::remember("dashboard.{$user->id}", 300, function () use ($user) {
            $currentMonth = Carbon::now()->format('Y-m');
            
            return [
                'monthly_spending' => $this->getMonthlySpending($user, $currentMonth),
                'budget_progress' => $this->getBudgetProgress($user, $currentMonth),
                'upcoming_bills' => $this->getUpcomingBills($user),
                'savings_progress' => $this->getSavingsProgress($user),
                'account_balances' => $this->getAccountBalances($user),
                'recent_transactions' => $this->getRecentTransactions($user),
                'spending_by_category' => $this->getSpendingByCategory($user, $currentMonth),
                'monthly_trends' => $this->getMonthlyTrends($user),
            ];
        });
    }

    /**
     * Get monthly spending data
     */
    public function getMonthlySpending(User $user, string $month)
    {
        $totalSpent = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
            ->sum('amount');

        $totalIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
            ->sum('amount');

        return [
            'total_spent' => $totalSpent,
            'total_income' => $totalIncome,
            'net_flow' => $totalIncome - $totalSpent,
            'month' => $month,
        ];
    }

    /**
     * Get budget progress data
     */
    public function getBudgetProgress(User $user, string $month)
    {
        $budgets = Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$month])
            ->with('category')
            ->get();

        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');

        $categoryProgress = $budgets->map(function ($budget) {
            return [
                'category_id' => $budget->category_id,
                'category_name' => $budget->category->name,
                'budget_amount' => $budget->amount,
                'spent_amount' => $budget->spent,
                'remaining' => $budget->amount - $budget->spent,
                'percentage_used' => $budget->amount > 0 ? ($budget->spent / $budget->amount) * 100 : 0,
                'is_exceeded' => $budget->spent > $budget->amount,
            ];
        });

        return [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'remaining' => $totalBudget - $totalSpent,
            'percentage_used' => $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0,
            'categories' => $categoryProgress,
        ];
    }

    /**
     * Get upcoming bills (next 7 days)
     */
    public function getUpcomingBills(User $user)
    {
        $upcomingBills = Bill::where('user_id', $user->id)
            ->where('is_paid', false)
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->with('category')
            ->orderBy('due_date')
            ->get();

        $overdueBills = Bill::where('user_id', $user->id)
            ->where('is_paid', false)
            ->where('due_date', '<', now())
            ->with('category')
            ->orderBy('due_date')
            ->get();

        return [
            'upcoming' => $upcomingBills,
            'overdue' => $overdueBills,
            'total_upcoming' => $upcomingBills->sum('amount'),
            'total_overdue' => $overdueBills->sum('amount'),
        ];
    }

    /**
     * Get savings progress data
     */
    public function getSavingsProgress(User $user)
    {
        $goals = SavingsGoal::where('user_id', $user->id)
            ->where('is_completed', false)
            ->with('account')
            ->get();

        $totalTarget = $goals->sum('target_amount');
        $totalCurrent = $goals->sum('current_amount');

        $goalProgress = $goals->map(function ($goal) {
            return [
                'id' => $goal->id,
                'name' => $goal->name,
                'target_amount' => $goal->target_amount,
                'current_amount' => $goal->current_amount,
                'remaining' => $goal->target_amount - $goal->current_amount,
                'percentage' => $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0,
                'target_date' => $goal->target_date,
                'days_remaining' => Carbon::parse($goal->target_date)->diffInDays(now()),
            ];
        });

        return [
            'total_target' => $totalTarget,
            'total_current' => $totalCurrent,
            'total_remaining' => $totalTarget - $totalCurrent,
            'overall_percentage' => $totalTarget > 0 ? ($totalCurrent / $totalTarget) * 100 : 0,
            'goals' => $goalProgress,
        ];
    }

    /**
     * Get account balances
     */
    public function getAccountBalances(User $user)
    {
        $accounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $totalBalance = $accounts->sum('balance');

        $accountBalances = $accounts->map(function ($account) {
            return [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type,
                'balance' => $account->balance,
                'color' => $account->color,
            ];
        });

        return [
            'total_balance' => $totalBalance,
            'accounts' => $accountBalances,
        ];
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions(User $user, int $limit = 10)
    {
        return Transaction::where('user_id', $user->id)
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get spending by category for current month
     */
    public function getSpendingByCategory(User $user, string $month)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.id, categories.name, categories.color, SUM(transactions.amount) as total_spent')
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderBy('total_spent', 'desc')
            ->get();
    }

    /**
     * Get monthly trends (last 6 months)
     */
    public function getMonthlyTrends(User $user)
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $months[] = [
                'month' => $month,
                'month_name' => Carbon::parse($month . '-01')->format('M Y'),
                'spending' => $this->getMonthlySpending($user, $month)['total_spent'],
                'income' => $this->getMonthlySpending($user, $month)['total_income'],
            ];
        }

        return $months;
    }

    /**
     * Clear dashboard cache for user
     */
    public function clearDashboardCache(User $user)
    {
        Cache::forget("dashboard.{$user->id}");
    }

    /**
     * Clear dashboard cache for all users
     */
    public function clearAllDashboardCache()
    {
        // This would need to be implemented based on your cache driver
        // For file cache, we'd need to clear the entire cache
        Cache::flush();
    }
}
