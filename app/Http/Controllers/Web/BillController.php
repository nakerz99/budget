<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Category;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BillController extends Controller
{
    /**
     * Display the bills page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get upcoming bills (next 30 days)
        $upcomingBills = Bill::where('user_id', $user->id)
            ->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(30)])
            ->orderBy('due_date')
            ->with('category')
            ->get();
        
        // Get overdue bills
        $overdueBills = Bill::where('user_id', $user->id)
            ->where('due_date', '<', Carbon::today())
            ->where('is_paid', false)
            ->orderBy('due_date')
            ->with('category')
            ->get();
        
        // Get paid bills
        $paidBills = Bill::where('user_id', $user->id)
            ->where('is_paid', true)
            ->orderBy('due_date', 'desc')
            ->with('category')
            ->limit(10)
            ->get();
        
        // Get recurring bills
        $recurringBills = Bill::where('user_id', $user->id)
            ->where('is_recurring', true)
            ->orderBy('due_date')
            ->with('category')
            ->get();
        
        // Get all bills for management, sorted by due date (urgent first)
        $allBills = Bill::where('user_id', $user->id)
            ->orderBy('is_paid') // Unpaid bills first
            ->orderBy('due_date') // Then by due date
            ->with('category')
            ->paginate(20);
        
        // Calculate summary
        $summary = [
            'total_monthly' => Bill::where('user_id', $user->id)
                ->where('frequency', 'monthly')
                ->sum('amount'),
            'due_this_week' => Bill::where('user_id', $user->id)
                ->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(7)])
                ->where('is_paid', false)
                ->sum('amount'),
            'overdue_amount' => $overdueBills->sum('amount'),
            'upcoming_count' => $upcomingBills->count(),
        ];
        
        // Get categories for the form
        $categories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Get accounts for payment
        $accounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('bills.index', compact(
            'upcomingBills',
            'overdueBills',
            'allBills',
            'summary',
            'categories',
            'accounts',
            'paidBills',
            'recurringBills'
        ))->with([
            'totalUpcoming' => $summary['upcoming_count']
        ]);
    }
    
    /**
     * Store a new bill.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'due_date' => 'required|date',
            'frequency' => 'nullable|in:once,weekly,monthly,yearly',
            'is_recurring' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $user = Auth::user();
        
        // Verify category belongs to user
        $category = Category::where('id', $request->category_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $bill = Bill::create([
            'user_id' => $user->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'frequency' => $request->frequency ?: 'once',
            'is_recurring' => $request->is_recurring ?? false,
            'is_paid' => false,
            'notes' => $request->notes,
        ]);
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill added successfully',
                'bill' => $bill->load('category'),
            ]);
        }
        
        return redirect()->route('bills.index')
            ->with('success', 'Bill added successfully');
    }
    
    /**
     * Show the form for editing a bill.
     *
     * @param Bill $bill
     * @return \Illuminate\View\View
     */
    public function edit(Bill $bill)
    {
        $user = Auth::user();
        
        // Verify bill belongs to user
        if ($bill->user_id !== $user->id) {
            abort(403);
        }
        
        // Get categories for the form
        $categories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('bills.edit', compact('bill', 'categories'));
    }
    
    /**
     * Update a bill.
     *
     * @param Request $request
     * @param Bill $bill
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Bill $bill)
    {
        // Verify bill belongs to user
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'due_date' => 'required|date',
            'frequency' => 'nullable|in:once,weekly,monthly,yearly',
            'is_recurring' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Verify category belongs to user
        $category = Category::where('id', $request->category_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $bill->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'category_id' => $request->category_id,
            'due_date' => $request->due_date,
            'frequency' => $request->frequency ?: 'once',
            'is_recurring' => $request->is_recurring ?? false,
            'notes' => $request->notes,
        ]);
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill updated successfully',
                'bill' => $bill->load('category'),
            ]);
        }
        
        return redirect()->route('bills.index')
            ->with('success', 'Bill updated successfully');
    }
    
    /**
     * Delete a bill.
     *
     * @param Bill $bill
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Bill $bill)
    {
        // Verify bill belongs to user
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }
        
        $bill->delete();
        
        // Check if this is a web request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill deleted successfully',
            ]);
        }
        
        return redirect()->route('bills.index')
            ->with('success', 'Bill deleted successfully');
    }
    
    /**
     * Mark a bill as paid.
     *
     * @param Request $request
     * @param Bill $bill
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function markPaid(Request $request, Bill $bill)
    {
        // Verify bill belongs to user
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }
        
        $user = Auth::user();
        
        // Get the first available account if no account_id provided
        $accountId = $request->get('account_id');
        if (!$accountId) {
            $account = Account::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
            
            if (!$account) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'No account available for payment',
                    ], 400);
                }
                return redirect()->route('bills.index')
                    ->with('error', 'No account available for payment');
            }
            $accountId = $account->id;
        }
        
        // Verify account belongs to user
        $account = Account::where('id', $accountId)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // Mark bill as paid
        $bill->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);
        
        // Create a transaction for the payment
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accountId,
            'category_id' => $bill->category_id,
            'amount' => -$bill->amount,
            'type' => 'expense',
            'description' => 'Bill payment: ' . $bill->name,
            'transaction_date' => $request->payment_date ?: now(),
        ]);
        
        // Update account balance
        $account->balance -= $bill->amount;
        $account->save();
        
        // If recurring, create next bill
        if ($bill->is_recurring && $bill->frequency !== 'once') {
            $nextDueDate = $this->calculateNextDueDate($bill->due_date, $bill->frequency);
            
            Bill::create([
                'user_id' => $bill->user_id,
                'category_id' => $bill->category_id,
                'name' => $bill->name,
                'amount' => $bill->amount,
                'due_date' => $nextDueDate,
                'frequency' => $bill->frequency,
                'is_recurring' => true,
                'is_paid' => false,
                'notes' => $bill->notes,
            ]);
        }
        
        // Check if this is a web request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill marked as paid',
                'transaction' => $transaction,
            ]);
        }
        
        return redirect()->route('bills.index')
            ->with('success', 'Bill marked as paid');
    }
    
    /**
     * Mark a bill as unpaid.
     *
     * @param Bill $bill
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function markUnpaid(Bill $bill)
    {
        // Verify bill belongs to user
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }
        
        $bill->update([
            'is_paid' => false,
            'paid_at' => null,
        ]);
        
        // Check if this is a web request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill marked as unpaid',
            ]);
        }
        
        return redirect()->route('bills.index')
            ->with('success', 'Bill marked as unpaid');
    }
    
    /**
     * Calculate the next due date based on frequency.
     *
     * @param string $currentDueDate
     * @param string $frequency
     * @return Carbon
     */
    private function calculateNextDueDate($currentDueDate, $frequency)
    {
        $date = Carbon::parse($currentDueDate);
        
        switch ($frequency) {
            case 'weekly':
                return $date->addWeek();
            case 'monthly':
                return $date->addMonth();
            case 'yearly':
                return $date->addYear();
            default:
                return $date;
        }
    }
}
