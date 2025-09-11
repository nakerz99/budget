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
        
        // Get all accounts with their status
        $allAccounts = Account::where('user_id', $user->id)
            ->orderBy('is_active', 'desc')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
        
        // Calculate total savings and statistics
        $savingsAccounts = $allAccounts->where('type', 'savings');
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
        
        
        return view('savings.index', compact(
            'savingsGoals',
            'totalSavings',
            'totalGoalTarget',
            'totalGoalCurrent',
            'activeGoalsCount',
            'completedGoalsCount',
            'monthlySavingsRate',
            'allAccounts'
        ))->with([
            'totalSaved' => $totalGoalCurrent,
            'totalTarget' => $totalGoalTarget,
            'completedGoals' => $savingsGoals->where('is_completed', true)
        ]);
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
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Savings goal created successfully',
                'goal' => $goal->load('account'),
            ]);
        }
        
        return redirect()->route('savings.index')
            ->with('success', 'Savings goal created successfully');
    }
    
    /**
     * Show the form for editing a savings goal.
     *
     * @param SavingsGoal $savingsGoal
     * @return \Illuminate\Http\JsonResponse
     */
    public function editGoal(SavingsGoal $savingsGoal)
    {
        $user = Auth::user();
        
        // Verify the goal belongs to the user
        if ($savingsGoal->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'id' => $savingsGoal->id,
            'name' => $savingsGoal->name,
            'target_amount' => $savingsGoal->target_amount,
            'current_amount' => $savingsGoal->current_amount,
            'target_date' => $savingsGoal->target_date ? $savingsGoal->target_date->format('Y-m-d') : null,
            'description' => $savingsGoal->description,
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
            'current_amount' => 'required|numeric|min:0',
            'target_date' => 'nullable|date|after:today',
            'description' => 'nullable|string|max:1000',
        ]);
        
        $savingsGoal->update([
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'current_amount' => $request->current_amount,
            'target_date' => $request->target_date,
            'description' => $request->description,
        ]);
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Savings goal updated successfully',
                'goal' => $savingsGoal->load('account'),
            ]);
        }
        
        return redirect()->route('savings.index')
            ->with('success', 'Savings goal updated successfully');
    }
    
    /**
     * Delete a savings goal.
     *
     * @param SavingsGoal $savingsGoal
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyGoal(SavingsGoal $savingsGoal)
    {
        // Verify goal belongs to user
        if ($savingsGoal->user_id !== Auth::id()) {
            abort(403);
        }
        
        $savingsGoal->delete();
        
        // Check if this is a web request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Savings goal deleted successfully',
            ]);
        }
        
        return redirect()->route('savings.index')
            ->with('success', 'Savings goal deleted successfully');
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeAccount(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'account_type' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'balance' => 'required|numeric|min:0',
        ]);
        
        $user = Auth::user();
        
        $account = Account::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'type' => 'savings',
            'balance' => $request->balance,
            'bank_name' => $request->bank_name,
            'account_type' => $request->account_type,
            'account_number' => $request->account_number,
            'color' => '#10B981',
            'is_active' => true,
        ]);
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Savings account created successfully',
                'account' => $account,
            ]);
        }
        
        return redirect()->route('savings.index')
            ->with('success', 'Savings account created successfully');
    }
    
    /**
     * Show the form for editing a savings account.
     *
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function editAccount(Account $account)
    {
        $user = Auth::user();
        
        // Verify the account belongs to the user
        if ($account->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'id' => $account->id,
            'name' => $account->name,
            'bank_name' => $account->bank_name,
            'account_type' => $account->account_type,
            'account_number' => $account->account_number,
            'balance' => $account->balance,
        ]);
    }
    
    /**
     * Update a savings account.
     *
     * @param Request $request
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateAccount(Request $request, Account $account)
    {
        // Verify account belongs to user
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'account_type' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'balance' => 'required|numeric|min:0',
        ]);
        
        $account->update([
            'name' => $request->name,
            'bank_name' => $request->bank_name,
            'account_type' => $request->account_type,
            'account_number' => $request->account_number,
            'balance' => $request->balance,
        ]);
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Savings account updated successfully',
                'account' => $account,
            ]);
        }
        
        return redirect()->route('savings.index')
            ->with('success', 'Savings account updated successfully');
    }
    
    /**
     * Delete a savings account.
     *
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyAccount(Account $account)
    {
        // Verify account belongs to user
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Delete associated savings goals first
        $account->savingsGoals()->delete();
        
        // Delete the account
        $account->delete();
        
        // Check if this is a web request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Savings account deleted successfully',
            ]);
        }
        
        return redirect()->route('savings.index')
            ->with('success', 'Savings account deleted successfully');
    }
}
