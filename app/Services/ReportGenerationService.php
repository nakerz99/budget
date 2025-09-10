<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Bill;
use App\Models\SavingsGoal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ReportGenerationService
{
    /**
     * Generate comprehensive spending report
     */
    public function generateSpendingReport(User $user, string $startDate, string $endDate)
    {
        $transactions = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['category', 'account'])
            ->get();

        $totalSpent = $transactions->sum('amount');
        $transactionCount = $transactions->count();

        // Spending by category
        $spendingByCategory = $transactions->groupBy('category_id')->map(function ($categoryTransactions) {
            $category = $categoryTransactions->first()->category;
            return [
                'category_name' => $category->name,
                'category_color' => $category->color,
                'total_amount' => $categoryTransactions->sum('amount'),
                'transaction_count' => $categoryTransactions->count(),
                'percentage' => 0, // Will be calculated after we have total
            ];
        })->sortByDesc('total_amount');

        // Calculate percentages
        $spendingByCategory = $spendingByCategory->map(function ($category) use ($totalSpent) {
            $category['percentage'] = $totalSpent > 0 ? ($category['total_amount'] / $totalSpent) * 100 : 0;
            return $category;
        });

        // Daily spending pattern
        $dailySpending = $transactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->transaction_date)->format('Y-m-d');
        })->map(function ($dayTransactions) {
            return [
                'date' => $dayTransactions->first()->transaction_date->format('Y-m-d'),
                'day_name' => $dayTransactions->first()->transaction_date->format('l'),
                'total_amount' => $dayTransactions->sum('amount'),
                'transaction_count' => $dayTransactions->count(),
            ];
        })->sortBy('date');

        // Top transactions
        $topTransactions = $transactions->sortByDesc('amount')->take(10);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1,
            ],
            'summary' => [
                'total_spent' => $totalSpent,
                'transaction_count' => $transactionCount,
                'average_per_transaction' => $transactionCount > 0 ? $totalSpent / $transactionCount : 0,
                'average_per_day' => $dailySpending->count() > 0 ? $totalSpent / $dailySpending->count() : 0,
            ],
            'spending_by_category' => $spendingByCategory,
            'daily_spending' => $dailySpending,
            'top_transactions' => $topTransactions,
        ];
    }

    /**
     * Generate income report
     */
    public function generateIncomeReport(User $user, string $startDate, string $endDate)
    {
        $transactions = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['category', 'account'])
            ->get();

        $totalIncome = $transactions->sum('amount');
        $transactionCount = $transactions->count();

        // Income by category
        $incomeByCategory = $transactions->groupBy('category_id')->map(function ($categoryTransactions) {
            $category = $categoryTransactions->first()->category;
            return [
                'category_name' => $category->name,
                'category_color' => $category->color,
                'total_amount' => $categoryTransactions->sum('amount'),
                'transaction_count' => $categoryTransactions->count(),
                'percentage' => 0,
            ];
        })->sortByDesc('total_amount');

        // Calculate percentages
        $incomeByCategory = $incomeByCategory->map(function ($category) use ($totalIncome) {
            $category['percentage'] = $totalIncome > 0 ? ($category['total_amount'] / $totalIncome) * 100 : 0;
            return $category;
        });

        // Monthly income trend
        $monthlyIncome = $transactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->transaction_date)->format('Y-m');
        })->map(function ($monthTransactions) {
            return [
                'month' => $monthTransactions->first()->transaction_date->format('Y-m'),
                'month_name' => $monthTransactions->first()->transaction_date->format('F Y'),
                'total_amount' => $monthTransactions->sum('amount'),
                'transaction_count' => $monthTransactions->count(),
            ];
        })->sortBy('month');

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'transaction_count' => $transactionCount,
                'average_per_transaction' => $transactionCount > 0 ? $totalIncome / $transactionCount : 0,
            ],
            'income_by_category' => $incomeByCategory,
            'monthly_income' => $monthlyIncome,
        ];
    }

    /**
     * Generate savings report
     */
    public function generateSavingsReport(User $user, string $startDate, string $endDate)
    {
        $goals = SavingsGoal::where('user_id', $user->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->where('is_completed', true)
                            ->whereBetween('updated_at', [$startDate, $endDate]);
                    });
            })
            ->with('account')
            ->get();

        $totalTarget = $goals->sum('target_amount');
        $totalCurrent = $goals->sum('current_amount');
        $completedGoals = $goals->where('is_completed', true)->count();
        $activeGoals = $goals->where('is_completed', false)->count();

        // Savings progress by goal
        $goalProgress = $goals->map(function ($goal) {
            $percentage = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
            $daysRemaining = Carbon::parse($goal->target_date)->diffInDays(now());
            
            return [
                'id' => $goal->id,
                'name' => $goal->name,
                'target_amount' => $goal->target_amount,
                'current_amount' => $goal->current_amount,
                'remaining_amount' => $goal->target_amount - $goal->current_amount,
                'percentage' => $percentage,
                'target_date' => $goal->target_date,
                'days_remaining' => $daysRemaining,
                'is_completed' => $goal->is_completed,
                'account_name' => $goal->account->name,
            ];
        });

        // Monthly savings contributions
        $monthlyContributions = $this->getMonthlySavingsContributions($user, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_target' => $totalTarget,
                'total_current' => $totalCurrent,
                'total_remaining' => $totalTarget - $totalCurrent,
                'overall_percentage' => $totalTarget > 0 ? ($totalCurrent / $totalTarget) * 100 : 0,
                'active_goals' => $activeGoals,
                'completed_goals' => $completedGoals,
            ],
            'goal_progress' => $goalProgress,
            'monthly_contributions' => $monthlyContributions,
        ];
    }

    /**
     * Generate budget performance report
     */
    public function generateBudgetReport(User $user, string $month)
    {
        $budgets = Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$month])
            ->with('category')
            ->get();

        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');
        $exceededBudgets = $budgets->where('spent', '>', 'amount')->count();
        $onTrackBudgets = $budgets->where('spent', '<=', 'amount')->count();

        $budgetPerformance = $budgets->map(function ($budget) {
            $percentage = $budget->amount > 0 ? ($budget->spent / $budget->amount) * 100 : 0;
            $remaining = $budget->amount - $budget->spent;
            
            return [
                'category_id' => $budget->category_id,
                'category_name' => $budget->category->name,
                'category_color' => $budget->category->color,
                'budget_amount' => $budget->amount,
                'spent_amount' => $budget->spent,
                'remaining_amount' => $remaining,
                'percentage_used' => $percentage,
                'is_exceeded' => $budget->spent > $budget->amount,
                'is_on_track' => $percentage <= 100,
            ];
        })->sortByDesc('spent_amount');

        return [
            'month' => $month,
            'summary' => [
                'total_budget' => $totalBudget,
                'total_spent' => $totalSpent,
                'remaining_budget' => $totalBudget - $totalSpent,
                'budget_utilization' => $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0,
                'exceeded_budgets' => $exceededBudgets,
                'on_track_budgets' => $onTrackBudgets,
            ],
            'budget_performance' => $budgetPerformance,
        ];
    }

    /**
     * Generate comprehensive financial report
     */
    public function generateComprehensiveReport(User $user, string $startDate, string $endDate)
    {
        $spendingReport = $this->generateSpendingReport($user, $startDate, $endDate);
        $incomeReport = $this->generateIncomeReport($user, $startDate, $endDate);
        $savingsReport = $this->generateSavingsReport($user, $startDate, $endDate);

        // Calculate net worth change
        $netIncome = $incomeReport['summary']['total_income'];
        $netSpending = $spendingReport['summary']['total_spent'];
        $netSavings = $netIncome - $netSpending;

        // Calculate savings rate
        $savingsRate = $netIncome > 0 ? ($netSavings / $netIncome) * 100 : 0;

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1,
            ],
            'financial_summary' => [
                'total_income' => $netIncome,
                'total_spending' => $netSpending,
                'net_savings' => $netSavings,
                'savings_rate' => $savingsRate,
            ],
            'spending_analysis' => $spendingReport,
            'income_analysis' => $incomeReport,
            'savings_analysis' => $savingsReport,
        ];
    }

    /**
     * Export report to CSV
     */
    public function exportToCsv(array $reportData, string $filename)
    {
        $csvData = $this->convertReportToCsv($reportData);
        
        $filePath = 'reports/' . $filename . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        Storage::disk('public')->put($filePath, $csvData);
        
        return $filePath;
    }

    /**
     * Export report to PDF (placeholder - would need PDF library)
     */
    public function exportToPdf(array $reportData, string $filename)
    {
        // This would require a PDF library like dompdf or tcpdf
        // For now, return a placeholder
        return [
            'success' => false,
            'message' => 'PDF export requires PDF library installation',
        ];
    }

    /**
     * Get monthly savings contributions
     */
    private function getMonthlySavingsContributions(User $user, string $startDate, string $endDate)
    {
        // This would need to be implemented based on how you track savings contributions
        // For now, return empty array
        return [];
    }

    /**
     * Convert report data to CSV format
     */
    private function convertReportToCsv(array $reportData)
    {
        $csv = '';
        
        // Add summary data
        if (isset($reportData['financial_summary'])) {
            $csv .= "Financial Summary\n";
            $csv .= "Total Income," . $reportData['financial_summary']['total_income'] . "\n";
            $csv .= "Total Spending," . $reportData['financial_summary']['total_spending'] . "\n";
            $csv .= "Net Savings," . $reportData['financial_summary']['net_savings'] . "\n";
            $csv .= "Savings Rate," . $reportData['financial_summary']['savings_rate'] . "%\n\n";
        }

        // Add spending by category
        if (isset($reportData['spending_analysis']['spending_by_category'])) {
            $csv .= "Spending by Category\n";
            $csv .= "Category,Amount,Percentage\n";
            foreach ($reportData['spending_analysis']['spending_by_category'] as $category) {
                $csv .= $category['category_name'] . "," . $category['total_amount'] . "," . $category['percentage'] . "%\n";
            }
            $csv .= "\n";
        }

        return $csv;
    }
}
