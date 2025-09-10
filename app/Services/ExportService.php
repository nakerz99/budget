<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Bill;
use App\Models\SavingsGoal;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExportService
{
    /**
     * Export transactions to CSV
     */
    public function exportTransactionsToCsv(User $user, array $filters = [])
    {
        $query = Transaction::where('user_id', $user->id)
            ->with(['account', 'category']);

        // Apply filters
        if (isset($filters['start_date'])) {
            $query->where('transaction_date', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('transaction_date', '<=', $filters['end_date']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $filename = 'transactions_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filePath = 'exports/' . $filename;

        $csvData = $this->generateTransactionsCsv($transactions);
        Storage::disk('public')->put($filePath, $csvData);

        return [
            'filename' => $filename,
            'file_path' => $filePath,
            'download_url' => asset('storage/' . $filePath),
            'record_count' => $transactions->count(),
        ];
    }

    /**
     * Export budgets to CSV
     */
    public function exportBudgetsToCsv(User $user, string $month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        
        $budgets = Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$month])
            ->with('category')
            ->get();

        $filename = 'budgets_' . $user->id . '_' . $month . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filePath = 'exports/' . $filename;

        $csvData = $this->generateBudgetsCsv($budgets);
        Storage::disk('public')->put($filePath, $csvData);

        return [
            'filename' => $filename,
            'file_path' => $filePath,
            'download_url' => asset('storage/' . $filePath),
            'record_count' => $budgets->count(),
        ];
    }

    /**
     * Export savings goals to CSV
     */
    public function exportSavingsGoalsToCsv(User $user)
    {
        $goals = SavingsGoal::where('user_id', $user->id)
            ->with('account')
            ->get();

        $filename = 'savings_goals_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filePath = 'exports/' . $filename;

        $csvData = $this->generateSavingsGoalsCsv($goals);
        Storage::disk('public')->put($filePath, $csvData);

        return [
            'filename' => $filename,
            'file_path' => $filePath,
            'download_url' => asset('storage/' . $filePath),
            'record_count' => $goals->count(),
        ];
    }

    /**
     * Export bills to CSV
     */
    public function exportBillsToCsv(User $user)
    {
        $bills = Bill::where('user_id', $user->id)
            ->with('category')
            ->get();

        $filename = 'bills_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filePath = 'exports/' . $filename;

        $csvData = $this->generateBillsCsv($bills);
        Storage::disk('public')->put($filePath, $csvData);

        return [
            'filename' => $filename,
            'file_path' => $filePath,
            'download_url' => asset('storage/' . $filePath),
            'record_count' => $bills->count(),
        ];
    }

    /**
     * Export comprehensive financial data to CSV
     */
    public function exportComprehensiveDataToCsv(User $user, array $filters = [])
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subYear()->format('Y-m-d');
        $endDate = $filters['end_date'] ?? Carbon::now()->format('Y-m-d');

        // Get all data
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['account', 'category'])
            ->get();

        $budgets = Budget::where('user_id', $user->id)
            ->whereBetween('month_year', [$startDate, $endDate])
            ->with('category')
            ->get();

        $savingsGoals = SavingsGoal::where('user_id', $user->id)
            ->with('account')
            ->get();

        $bills = Bill::where('user_id', $user->id)
            ->with('category')
            ->get();

        $accounts = Account::where('user_id', $user->id)->get();
        $categories = Category::where('user_id', $user->id)->get();

        $filename = 'comprehensive_data_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filePath = 'exports/' . $filename;

        $csvData = $this->generateComprehensiveCsv([
            'transactions' => $transactions,
            'budgets' => $budgets,
            'savings_goals' => $savingsGoals,
            'bills' => $bills,
            'accounts' => $accounts,
            'categories' => $categories,
            'user' => $user,
        ]);

        Storage::disk('public')->put($filePath, $csvData);

        return [
            'filename' => $filename,
            'file_path' => $filePath,
            'download_url' => asset('storage/' . $filePath),
            'record_count' => $transactions->count() + $budgets->count() + $savingsGoals->count() + $bills->count(),
        ];
    }

    /**
     * Generate transactions CSV data
     */
    private function generateTransactionsCsv($transactions)
    {
        $csv = "Date,Type,Description,Amount,Account,Category,Location,Receipt\n";

        foreach ($transactions as $transaction) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $transaction->transaction_date->format('Y-m-d'),
                ucfirst($transaction->type),
                $this->escapeCsvField($transaction->description ?? ''),
                number_format($transaction->amount, 2),
                $this->escapeCsvField($transaction->account->name),
                $this->escapeCsvField($transaction->category->name),
                $this->escapeCsvField($transaction->location ?? ''),
                $transaction->receipt_path ? 'Yes' : 'No'
            );
        }

        return $csv;
    }

    /**
     * Generate budgets CSV data
     */
    private function generateBudgetsCsv($budgets)
    {
        $csv = "Month,Category,Budget Amount,Spent Amount,Remaining,Percentage Used,Rollover\n";

        foreach ($budgets as $budget) {
            $remaining = $budget->amount - $budget->spent;
            $percentage = $budget->amount > 0 ? ($budget->spent / $budget->amount) * 100 : 0;

            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%.1f%%,%s\n",
                $budget->month_year->format('Y-m'),
                $this->escapeCsvField($budget->category->name),
                number_format($budget->amount, 2),
                number_format($budget->spent, 2),
                number_format($remaining, 2),
                $percentage,
                $budget->rollover ? 'Yes' : 'No'
            );
        }

        return $csv;
    }

    /**
     * Generate savings goals CSV data
     */
    private function generateSavingsGoalsCsv($goals)
    {
        $csv = "Name,Target Amount,Current Amount,Remaining,Percentage,Target Date,Days Remaining,Account,Completed\n";

        foreach ($goals as $goal) {
            $remaining = $goal->target_amount - $goal->current_amount;
            $percentage = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
            $daysRemaining = Carbon::parse($goal->target_date)->diffInDays(now());

            $csv .= sprintf(
                "%s,%s,%s,%s,%.1f%%,%s,%d,%s,%s\n",
                $this->escapeCsvField($goal->name),
                number_format($goal->target_amount, 2),
                number_format($goal->current_amount, 2),
                number_format($remaining, 2),
                $percentage,
                $goal->target_date->format('Y-m-d'),
                $daysRemaining,
                $this->escapeCsvField($goal->account->name),
                $goal->is_completed ? 'Yes' : 'No'
            );
        }

        return $csv;
    }

    /**
     * Generate bills CSV data
     */
    private function generateBillsCsv($bills)
    {
        $csv = "Name,Amount,Due Date,Frequency,Category,Paid,Recurring,Notes\n";

        foreach ($bills as $bill) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $this->escapeCsvField($bill->name),
                number_format($bill->amount, 2),
                $bill->due_date->format('Y-m-d'),
                $bill->frequency ?? 'One-time',
                $this->escapeCsvField($bill->category->name),
                $bill->is_paid ? 'Yes' : 'No',
                $bill->is_recurring ? 'Yes' : 'No',
                $this->escapeCsvField($bill->notes ?? '')
            );
        }

        return $csv;
    }

    /**
     * Generate comprehensive CSV data
     */
    private function generateComprehensiveCsv($data)
    {
        $csv = "=== BUDGET TRACKER EXPORT ===\n";
        $csv .= "Export Date: " . now()->format('Y-m-d H:i:s') . "\n";
        $csv .= "User: " . $data['user']->full_name . " (" . $data['user']->username . ")\n\n";

        // Transactions
        $csv .= "=== TRANSACTIONS ===\n";
        $csv .= $this->generateTransactionsCsv($data['transactions']) . "\n";

        // Budgets
        $csv .= "=== BUDGETS ===\n";
        $csv .= $this->generateBudgetsCsv($data['budgets']) . "\n";

        // Savings Goals
        $csv .= "=== SAVINGS GOALS ===\n";
        $csv .= $this->generateSavingsGoalsCsv($data['savings_goals']) . "\n";

        // Bills
        $csv .= "=== BILLS ===\n";
        $csv .= $this->generateBillsCsv($data['bills']) . "\n";

        // Accounts
        $csv .= "=== ACCOUNTS ===\n";
        $csv .= "Name,Type,Balance,Active\n";
        foreach ($data['accounts'] as $account) {
            $csv .= sprintf(
                "%s,%s,%s,%s\n",
                $this->escapeCsvField($account->name),
                ucfirst($account->type),
                number_format($account->balance, 2),
                $account->is_active ? 'Yes' : 'No'
            );
        }
        $csv .= "\n";

        // Categories
        $csv .= "=== CATEGORIES ===\n";
        $csv .= "Name,Type,Color,Active\n";
        foreach ($data['categories'] as $category) {
            $csv .= sprintf(
                "%s,%s,%s,%s\n",
                $this->escapeCsvField($category->name),
                ucfirst($category->type),
                $category->color,
                $category->is_active ? 'Yes' : 'No'
            );
        }

        return $csv;
    }

    /**
     * Escape CSV field
     */
    private function escapeCsvField($field)
    {
        if (is_null($field)) {
            return '';
        }

        $field = (string) $field;
        
        // Escape quotes and wrap in quotes if contains comma, quote, or newline
        if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
            $field = str_replace('"', '""', $field);
            $field = '"' . $field . '"';
        }

        return $field;
    }

    /**
     * Clean up old export files
     */
    public function cleanupOldExports(int $daysOld = 7)
    {
        $cutoffDate = now()->subDays($daysOld);
        $files = Storage::disk('public')->files('exports');
        
        $deletedCount = 0;
        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);
            if ($lastModified < $cutoffDate->timestamp) {
                Storage::disk('public')->delete($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Get export statistics
     */
    public function getExportStatistics()
    {
        $files = Storage::disk('public')->files('exports');
        
        return [
            'total_files' => count($files),
            'total_size' => array_sum(array_map(function($file) {
                return Storage::disk('public')->size($file);
            }, $files)),
            'oldest_file' => count($files) > 0 ? min(array_map(function($file) {
                return Storage::disk('public')->lastModified($file);
            }, $files)) : null,
            'newest_file' => count($files) > 0 ? max(array_map(function($file) {
                return Storage::disk('public')->lastModified($file);
            }, $files)) : null,
        ];
    }
}
