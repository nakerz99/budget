<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show user dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get current month data
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $endOfMonth = \Carbon\Carbon::now()->endOfMonth();
        
        // Monthly spending
        $monthlySpending = \App\Models\Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
        
        // Monthly budget
        $monthlyBudget = \App\Models\Budget::where('user_id', $user->id)
            ->where('month_year', $startOfMonth)
            ->sum('amount');
        
        // Account balance
        $accountBalance = \App\Models\Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->sum('balance');
        
        // Savings goals count
        $savingsGoalsCount = \App\Models\SavingsGoal::where('user_id', $user->id)
            ->where('is_completed', false)
            ->count();
        
        // Recent transactions
        $recentTransactions = \App\Models\Transaction::where('user_id', $user->id)
            ->with(['category', 'account'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Upcoming bills
        $upcomingBills = \App\Models\Bill::where('user_id', $user->id)
            ->where('is_paid', false)
            ->whereBetween('due_date', [\Carbon\Carbon::today(), \Carbon\Carbon::today()->addDays(7)])
            ->orderBy('due_date')
            ->limit(3)
            ->get();
        
        
        return view('dashboard.index', compact(
            'user',
            'monthlySpending',
            'monthlyBudget',
            'accountBalance',
            'savingsGoalsCount',
            'recentTransactions',
            'upcomingBills'
        ));
    }
}
