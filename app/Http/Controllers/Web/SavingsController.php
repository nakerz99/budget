<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SavingsController extends Controller
{
    /**
     * Display the savings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all savings goals
        $savingsGoals = SavingsGoal::where('user_id', $user->id)
            ->with('account')
            ->orderBy('target_date')
            ->get();
        
        // Calculate progress for each goal
        foreach ($savingsGoals as $goal) {
            $goal->progress_percentage = $goal->target_amount > 0 
                ? round(($goal->current_amount / $goal->target_amount) * 100, 1) 
                : 0;
            
            $goal->days_remaining = Carbon::now()->diffInDays($goal->target_date, false);
            $goal->monthly_required = $goal->days_remaining > 0 && $goal->target_amount > $goal->current_amount
                ? round((($goal->target_amount - $goal->current_amount) / $goal->days_remaining) * 30, 2)
                : 0;
        }
        
        // Get savings accounts (accounts with type 'savings')
        $savingsAccounts = Account::where('user_id', $user->id)
            ->where('type', 'savings')
            ->where('is_active', true)
            ->get();
        
        // Calculate total savings and statistics
        $totalSavings = $savingsAccounts->sum('balance');
        $totalGoalTarget = $savingsGoals->sum('target_amount');
        $totalGoalCurrent = $savingsGoals->sum('current_amount');
        $activeGoalsCount = $savingsGoals->where('is_completed', false)->count();
        $completedGoalsCount = $savingsGoals->where('is_completed', true)->count();
        
        // Calculate monthly savings rate (last 3 months average)
        $threeMonthsAgo = Carbon::now()->subMonths(3)->startOfMonth();
        $savingsTransactions = Transaction::where('user_id', $user->id)
            ->whereIn('account_id', $savingsAccounts->pluck('id'))
            ->where('type', 'income')
            ->where('transaction_date', '>=', $threeMonthsAgo)
            ->sum('amount');
        
        $monthlySavingsRate = $savingsTransactions / 3;
        
        // Get all accounts for transfers
        $allAccounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('savings.index', compact(
            'savingsGoals',
            'savingsAccounts',
            'totalSavings',
            'totalGoalTarget',
            'totalGoalCurrent',
            'activeGoalsCount',
            'completedGoalsCount',
            'monthlySavingsRate',
            'allAccounts'
        ));
    }
    
    /**
     * Store a new savings goal.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeGoal(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0.01',
            'target_date' => 'required|date|after:today',
            'account_id' => 'required|exists:accounts,id',
            'color' => 'nullable|string|max:7',
        ]);
        
        $user = Auth::user();
        
        // Verify account belongs to user
        $account = Account::where('id', $request->account_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $goal = SavingsGoal::create([
            'user_id' => $user->id,
            'account_id' => $request->account_id,
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'current_amount' => 0,
            'target_date' => $request->target_date,
            'color' => $request->color ?: '#8B5CF6',
            'is_completed' => false,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Savings goal created successfully',
            'goal' => $goal->load('account'),
        ]);
    }
    
    /**
     * Update a savings goal.
     *
     * @param Request $request
     * @param SavingsGoal $savingsGoal
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGoal(Request $request, SavingsGoal $savingsGoal)
    {
        // Verify goal belongs to user
        if ($savingsGoal->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0.01',
            'target_date' => 'required|date|after:today',
            'account_id' => 'required|exists:accounts,id',
            'color' => 'nullable|string|max:7',
        ]);
        
        // Verify account belongs to user
        $account = Account::where('id', $request->account_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $savingsGoal->update([
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'target_date' => $request->target_date,
            'account_id' => $request->account_id,
            'color' => $request->color ?: $savingsGoal->color,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Savings goal updated successfully',
            'goal' => $savingsGoal->load('account'),
        ]);
    }
    
    /**
     * Delete a savings goal.
     *
     * @param SavingsGoal $savingsGoal
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyGoal(SavingsGoal $savingsGoal)
    {
        // Verify goal belongs to user
        if ($savingsGoal->user_id !== Auth::id()) {
            abort(403);
        }
        
        $savingsGoal->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Savings goal deleted successfully',
        ]);
    }
    
    /**
     * Add contribution to a savings goal.
     *
     * @param Request $request
     * @param SavingsGoal $savingsGoal
     * @return \Illuminate\Http\JsonResponse
     */
    public function addContribution(Request $request, SavingsGoal $savingsGoal)
    {
        // Verify goal belongs to user
        if ($savingsGoal->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'from_account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string|max:255',
        ]);
        
        $user = Auth::user();
        
        // Verify accounts belong to user
        $fromAccount = Account::where('id', $request->from_account_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        $toAccount = Account::find($savingsGoal->account_id);
        
        // Check if from account has sufficient balance
        if ($fromAccount->balance < $request->amount) {
            return response()->json([
                'error' => 'Insufficient balance in source account',
            ], 400);
        }
        
        // Get or create a savings category
        $savingsCategory = Category::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Savings',
                'type' => 'expense'
            ],
            [
                'color' => '#8B5CF6',
                'icon' => 'piggy-bank',
                'is_active' => true
            ]
        );
        
        // Create transfer transactions
        // Withdrawal from source account
        $withdrawalTransaction = Transaction::create([
            'user_id' => $user->id,
            'account_id' => $request->from_account_id,
            'category_id' => $savingsCategory->id,
            'amount' => -$request->amount,
            'type' => 'transfer',
            'description' => $request->description ?: 'Savings contribution: ' . $savingsGoal->name,
            'transaction_date' => now(),
        ]);
        
        // Deposit to savings account
        $depositTransaction = Transaction::create([
            'user_id' => $user->id,
            'account_id' => $savingsGoal->account_id,
            'category_id' => $savingsCategory->id,
            'amount' => $request->amount,
            'type' => 'transfer',
            'description' => $request->description ?: 'Savings contribution: ' . $savingsGoal->name,
            'transaction_date' => now(),
        ]);
        
        // Update account balances
        $fromAccount->balance -= $request->amount;
        $fromAccount->save();
        
        $toAccount->balance += $request->amount;
        $toAccount->save();
        
        // Update savings goal
        $savingsGoal->current_amount += $request->amount;
        
        // Check if goal is completed
        if ($savingsGoal->current_amount >= $savingsGoal->target_amount) {
            $savingsGoal->is_completed = true;
        }
        
        $savingsGoal->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Contribution added successfully',
            'goal' => $savingsGoal->load('account'),
        ]);
    }
    
    /**
     * Store a new savings account.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:7',
        ]);
        
        $user = Auth::user();
        
        $account = Account::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'type' => 'savings',
            'balance' => $request->balance,
            'color' => $request->color ?: '#10B981',
            'is_active' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Savings account created successfully',
            'account' => $account,
        ]);
    }
}
