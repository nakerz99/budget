<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardDataService;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Bill;
use App\Models\SavingsGoal;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard data
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $dashboardService = app(DashboardDataService::class);
        
        $dashboardData = $dashboardService->getDashboardData($user);

        return response()->json([
            'success' => true,
            'data' => new DashboardResource((object) $dashboardData)
        ]);
    }

    private function getMonthlySpending($user, $month)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
            ->sum('amount');
    }

    private function getMonthlyBudget($user, $month)
    {
        return Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$month])
            ->sum('amount');
    }

    private function getAccountBalances($user)
    {
        return Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->select('id', 'name', 'type', 'balance', 'color')
            ->get();
    }

    private function getUpcomingBills($user)
    {
        $nextWeek = Carbon::now()->addDays(7);
        
        return Bill::where('user_id', $user->id)
            ->where('is_paid', false)
            ->whereBetween('due_date', [Carbon::now(), $nextWeek])
            ->with('category')
            ->orderBy('due_date')
            ->get();
    }

    private function getSavingsProgress($user)
    {
        $goals = SavingsGoal::where('user_id', $user->id)
            ->where('is_completed', false)
            ->get();

        $totalTarget = $goals->sum('target_amount');
        $totalCurrent = $goals->sum('current_amount');
        $progressPercentage = $totalTarget > 0 ? ($totalCurrent / $totalTarget) * 100 : 0;

        return [
            'total_target' => $totalTarget,
            'total_current' => $totalCurrent,
            'progress_percentage' => round($progressPercentage, 2),
            'goals' => $goals
        ];
    }

    private function getRecentTransactions($user, $limit = 10)
    {
        return Transaction::where('user_id', $user->id)
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getSpendingByCategory($user, $month)
    {
        return Transaction::where('transactions.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', 'categories.color', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderBy('total', 'desc')
            ->get();
    }

    private function getMonthlyTrends($user, $months = 6)
    {
        $trends = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            
            $income = Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
                ->sum('amount');
                
            $expenses = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
                ->sum('amount');
            
            $trends[] = [
                'month' => $month,
                'income' => $income,
                'expenses' => $expenses,
                'savings' => $income - $expenses
            ];
        }
        
        return $trends;
    }
}
