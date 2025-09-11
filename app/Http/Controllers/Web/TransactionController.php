<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Account;
use App\Events\TransactionCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display the transactions page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $filters = [
            'date_from' => $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d')),
            'category_id' => $request->get('category_id'),
            'account_id' => $request->get('account_id'),
            'type' => $request->get('type'),
            'search' => $request->get('search'),
            'sort' => $request->get('sort', 'transaction_date'),
            'order' => $request->get('order', 'desc'),
        ];
        
        // Build query
        $query = Transaction::where('user_id', $user->id)
            ->with(['category', 'account']);
        
        // Apply date filters
        if ($filters['date_from']) {
            $query->where('transaction_date', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->where('transaction_date', '<=', $filters['date_to']);
        }
        
        // Apply category filter
        if ($filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }
        
        // Apply account filter
        if ($filters['account_id']) {
            $query->where('account_id', $filters['account_id']);
        }
        
        // Apply type filter
        if ($filters['type']) {
            $query->where('type', $filters['type']);
        }
        
        // Apply search
        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('amount', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        // Apply sorting
        $query->orderBy($filters['sort'], $filters['order']);
        
        // Paginate results
        $transactions = $query->paginate(25)->withQueryString();
        
        // Get user's categories and accounts for filters
        $categories = Category::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $accounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Calculate summary statistics
        $summary = [
            'total_income' => Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$filters['date_from'], $filters['date_to']])
                ->sum('amount'),
            'total_expense' => Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$filters['date_from'], $filters['date_to']])
                ->sum('amount'),
            'transaction_count' => $transactions->total(),
        ];
        
        $summary['net_amount'] = $summary['total_income'] - abs($summary['total_expense']);
        
        return view('transactions.index', compact(
            'transactions',
            'categories',
            'accounts',
            'filters',
            'summary'
        ))->with([
            'totalIncome' => $summary['total_income'],
            'totalExpense' => $summary['total_expense'],
            'netAmount' => $summary['net_amount'],
            'transactionCount' => $summary['transaction_count']
        ]);
    }
    
    /**
     * Show the form for creating a new transaction.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get user's categories and accounts for the form
        $categories = Category::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $accounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('transactions.create', compact('categories', 'accounts'));
    }
    
    /**
     * Store a new transaction.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'account_id' => 'required|exists:accounts,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);
        
        $user = Auth::user();
        
        // Verify category and account belong to user
        $category = Category::where('id', $request->category_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        $account = Account::where('id', $request->account_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // Create transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'amount' => $request->type === 'expense' ? -abs($request->amount) : abs($request->amount),
            'category_id' => $request->category_id,
            'account_id' => $request->account_id,
            'transaction_date' => $request->transaction_date,
            'description' => $request->description,
        ]);
        
        // Update account balance
        $account->balance += $transaction->amount;
        $account->save();
        
        // Fire transaction created event
        event(new TransactionCreated($transaction));
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction added successfully',
                'transaction' => $transaction->load(['category', 'account']),
            ]);
        }
        
        return redirect()->route('transactions.index')
            ->with('success', 'Transaction added successfully');
    }
    
    /**
     * Show the form for editing a transaction.
     *
     * @param Transaction $transaction
     * @return \Illuminate\View\View
     */
    public function edit(Transaction $transaction)
    {
        $user = Auth::user();
        
        // Verify transaction belongs to user
        if ($transaction->user_id !== $user->id) {
            abort(403);
        }
        
        // Get user's categories and accounts for the form
        $categories = Category::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $accounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('transactions.edit', compact('transaction', 'categories', 'accounts'));
    }
    
    /**
     * Update a transaction.
     *
     * @param Request $request
     * @param Transaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Verify transaction belongs to user
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'account_id' => 'required|exists:accounts,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);
        
        $user = Auth::user();
        
        // Verify category and account belong to user
        $category = Category::where('id', $request->category_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        $account = Account::where('id', $request->account_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // Reverse old transaction from account balance
        $oldAccount = Account::find($transaction->account_id);
        $oldAccount->balance -= $transaction->amount;
        $oldAccount->save();
        
        // Update transaction
        $transaction->update([
            'type' => $request->type,
            'amount' => $request->type === 'expense' ? -abs($request->amount) : abs($request->amount),
            'category_id' => $request->category_id,
            'account_id' => $request->account_id,
            'transaction_date' => $request->transaction_date,
            'description' => $request->description,
        ]);
        
        // Apply new transaction to account balance
        $account->balance += $transaction->amount;
        $account->save();
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'transaction' => $transaction->load(['category', 'account']),
            ]);
        }
        
        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully');
    }
    
    /**
     * Delete a transaction.
     *
     * @param Transaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Transaction $transaction)
    {
        // Verify transaction belongs to user
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Reverse transaction from account balance
        $account = Account::find($transaction->account_id);
        $account->balance -= $transaction->amount;
        $account->save();
        
        // Delete transaction
        $transaction->delete();
        
        // Check if this is a web request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully',
            ]);
        }
        
        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully');
    }
    
    /**
     * Bulk delete transactions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:transactions,id',
        ]);
        
        $user = Auth::user();
        
        // Get transactions that belong to user
        $transactions = Transaction::whereIn('id', $request->transaction_ids)
            ->where('user_id', $user->id)
            ->get();
        
        // Reverse each transaction from account balance
        foreach ($transactions as $transaction) {
            $account = Account::find($transaction->account_id);
            $account->balance -= $transaction->amount;
            $account->save();
            
            $transaction->delete();
        }
        
        return response()->json([
            'success' => true,
            'message' => count($transactions) . ' transactions deleted successfully',
        ]);
    }
}
