<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\Transaction;

class RecalculateBalances extends Command
{
    protected $signature = 'balances:recalculate';
    protected $description = 'Recalculate all account balances based on transactions';

    public function handle()
    {
        $this->info('Recalculating account balances...');
        
        $accounts = Account::all();
        
        foreach ($accounts as $account) {
            // Reset balance to 0
            $initialBalance = $account->balance;
            $account->balance = 0;
            $account->save();
            
            // Calculate balance from all transactions
            $income = Transaction::where('account_id', $account->id)
                ->where('type', 'income')
                ->sum('amount');
                
            $expenses = Transaction::where('account_id', $account->id)
                ->where('type', 'expense')
                ->sum('amount');
            
            $newBalance = $income - $expenses;
            
            $account->balance = $newBalance;
            $account->save();
            
            $this->info("Account: {$account->name}");
            $this->info("  Initial Balance: ₱" . number_format($initialBalance, 2));
            $this->info("  Income: ₱" . number_format($income, 2));
            $this->info("  Expenses: ₱" . number_format($expenses, 2));
            $this->info("  New Balance: ₱" . number_format($newBalance, 2));
            $this->info("");
        }
        
        $this->info('Balance recalculation complete!');
    }
}
