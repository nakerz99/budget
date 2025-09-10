<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRecurringTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Process recurring transactions
        $recurringTransactions = Transaction::where('is_recurring', true)
            ->whereNotNull('recurring_data')
            ->get();

        foreach ($recurringTransactions as $transaction) {
            $recurringData = $transaction->recurring_data;
            
            // Check if it's time to create a new transaction
            if ($this->shouldCreateTransaction($transaction, $recurringData)) {
                $newTransaction = $transaction->replicate();
                $newTransaction->is_recurring = false;
                $newTransaction->transaction_date = Carbon::now();
                $newTransaction->save();
                
                Log::info('Created recurring transaction: ' . $newTransaction->description);
            }
        }
        
        // Process recurring bills that are due
        $recurringBills = Bill::where('is_recurring', true)
            ->where('is_paid', true)
            ->where('due_date', '<=', Carbon::today())
            ->get();
        
        foreach ($recurringBills as $bill) {
            // Create next bill instance
            $nextDueDate = $this->calculateNextDueDate($bill->due_date, $bill->frequency);
            
            $newBill = $bill->replicate();
            $newBill->due_date = $nextDueDate;
            $newBill->is_paid = false;
            $newBill->save();
            
            Log::info('Created next recurring bill: ' . $newBill->name . ' due on ' . $nextDueDate->format('Y-m-d'));
        }
    }
    
    /**
     * Determine if a recurring transaction should be created.
     *
     * @param Transaction $transaction
     * @param array $recurringData
     * @return bool
     */
    private function shouldCreateTransaction($transaction, $recurringData)
    {
        // This is a simplified version
        // In a real application, you would check the frequency and last created date
        return false;
    }
    
    /**
     * Calculate the next due date based on frequency.
     *
     * @param Carbon $currentDueDate
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
