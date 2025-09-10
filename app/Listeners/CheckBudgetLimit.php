<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Events\BudgetExceeded;
use App\Models\Budget;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CheckBudgetLimit
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TransactionCreated  $event
     * @return void
     */
    public function handle(TransactionCreated $event)
    {
        $transaction = $event->transaction;
        
        // Only check for expense transactions
        if ($transaction->type !== 'expense') {
            return;
        }
        
        // Get the current month's budget for this category
        $currentMonth = Carbon::now()->startOfMonth();
        
        $budget = Budget::where('user_id', $transaction->user_id)
            ->where('category_id', $transaction->category_id)
            ->where('month_year', $currentMonth)
            ->first();
            
        if (!$budget) {
            return;
        }
        
        // Calculate total spent in this category for the current month
        $totalSpent = Transaction::where('user_id', $transaction->user_id)
            ->where('category_id', $transaction->category_id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$currentMonth, $currentMonth->copy()->endOfMonth()])
            ->sum(DB::raw('ABS(amount)'));
        
        // Check if budget is exceeded
        if ($totalSpent > $budget->amount) {
            event(new BudgetExceeded($budget, $transaction->category, $totalSpent));
        }
    }
}
