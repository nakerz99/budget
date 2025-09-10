<?php

namespace App\Services;

use App\Models\User;
use App\Models\Budget;
use App\Models\Transaction;
use App\Models\Category;
use Carbon\Carbon;

class BudgetCalculationService
{
    /**
     * Calculate budget utilization for a specific month
     */
    public function calculateBudgetUtilization(User $user, string $month)
    {
        $budgets = Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$month])
            ->with('category')
            ->get();

        $utilization = [];

        foreach ($budgets as $budget) {
            $spent = $this->calculateCategorySpending($user, $budget->category_id, $month);
            $remaining = $budget->amount - $spent;
            $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;

            $utilization[] = [
                'budget_id' => $budget->id,
                'category_id' => $budget->category_id,
                'category_name' => $budget->category->name,
                'budget_amount' => $budget->amount,
                'spent_amount' => $spent,
                'remaining_amount' => $remaining,
                'percentage_used' => $percentage,
                'is_exceeded' => $spent > $budget->amount,
                'is_near_limit' => $percentage >= 80 && $percentage < 100,
            ];
        }

        return $utilization;
    }

    /**
     * Calculate spending for a specific category in a month
     */
    public function calculateCategorySpending(User $user, int $categoryId, string $month)
    {
        return Transaction::where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->where('type', 'expense')
            ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
            ->sum('amount');
    }

    /**
     * Update budget spent amounts for a month
     */
    public function updateBudgetSpentAmounts(User $user, string $month)
    {
        $budgets = Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$month])
            ->get();

        foreach ($budgets as $budget) {
            $spent = $this->calculateCategorySpending($user, $budget->category_id, $month);
            $budget->update(['spent' => $spent]);
        }

        return $budgets;
    }

    /**
     * Create monthly budget from previous month
     */
    public function createMonthlyBudgetFromPrevious(User $user, string $targetMonth, bool $includeRollover = true)
    {
        $previousMonth = Carbon::parse($targetMonth . '-01')->subMonth()->format('Y-m');
        
        $previousBudgets = Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$previousMonth])
            ->get();

        $newBudgets = [];

        foreach ($previousBudgets as $previousBudget) {
            $budgetData = [
                'user_id' => $user->id,
                'category_id' => $previousBudget->category_id,
                'amount' => $previousBudget->amount,
                'month_year' => $targetMonth . '-01',
                'rollover' => $includeRollover ? $previousBudget->rollover : false,
            ];

            // Add rollover amount if enabled
            if ($includeRollover && $previousBudget->rollover) {
                $rolloverAmount = $previousBudget->amount - $previousBudget->spent;
                if ($rolloverAmount > 0) {
                    $budgetData['amount'] += $rolloverAmount;
                }
            }

            $newBudgets[] = Budget::create($budgetData);
        }

        return $newBudgets;
    }

    /**
     * Get budget recommendations based on spending patterns
     */
    public function getBudgetRecommendations(User $user, string $month)
    {
        $currentSpending = $this->getMonthlySpendingByCategory($user, $month);
        $previousSpending = $this->getMonthlySpendingByCategory($user, Carbon::parse($month . '-01')->subMonth()->format('Y-m'));

        $recommendations = [];

        foreach ($currentSpending as $categoryId => $currentAmount) {
            $previousAmount = $previousSpending[$categoryId] ?? 0;
            $change = $previousAmount > 0 ? (($currentAmount - $previousAmount) / $previousAmount) * 100 : 0;

            $recommendation = [
                'category_id' => $categoryId,
                'current_amount' => $currentAmount,
                'previous_amount' => $previousAmount,
                'change_percentage' => $change,
                'recommendation' => $this->generateRecommendation($currentAmount, $previousAmount, $change),
            ];

            $recommendations[] = $recommendation;
        }

        return $recommendations;
    }

    /**
     * Get monthly spending by category
     */
    private function getMonthlySpendingByCategory(User $user, string $month)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.id, SUM(transactions.amount) as total_spent')
            ->groupBy('categories.id')
            ->pluck('total_spent', 'categories.id')
            ->toArray();
    }

    /**
     * Generate budget recommendation based on spending patterns
     */
    private function generateRecommendation(float $currentAmount, float $previousAmount, float $changePercentage)
    {
        if ($changePercentage > 20) {
            return [
                'type' => 'warning',
                'message' => 'Spending increased significantly. Consider reducing budget or controlling expenses.',
                'suggested_action' => 'Review recent transactions and identify areas to cut back.',
            ];
        } elseif ($changePercentage < -20) {
            return [
                'type' => 'success',
                'message' => 'Great job! Spending decreased significantly.',
                'suggested_action' => 'Consider reallocating saved money to other categories or savings.',
            ];
        } elseif ($changePercentage > 10) {
            return [
                'type' => 'caution',
                'message' => 'Spending is trending upward. Monitor closely.',
                'suggested_action' => 'Track daily expenses to prevent overspending.',
            ];
        } else {
            return [
                'type' => 'info',
                'message' => 'Spending is stable. Keep up the good work!',
                'suggested_action' => 'Continue monitoring to maintain current spending levels.',
            ];
        }
    }

    /**
     * Calculate budget health score
     */
    public function calculateBudgetHealthScore(User $user, string $month)
    {
        $utilization = $this->calculateBudgetUtilization($user, $month);
        
        if (empty($utilization)) {
            return 0;
        }

        $totalScore = 0;
        $categoryCount = count($utilization);

        foreach ($utilization as $category) {
            $percentage = $category['percentage_used'];
            
            if ($percentage <= 100) {
                // Score based on how close to 100% without exceeding
                $score = 100 - ($percentage * 0.5);
            } else {
                // Penalty for exceeding budget
                $excess = $percentage - 100;
                $score = max(0, 50 - ($excess * 2));
            }
            
            $totalScore += $score;
        }

        return round($totalScore / $categoryCount);
    }

    /**
     * Get budget alerts for the month
     */
    public function getBudgetAlerts(User $user, string $month)
    {
        $utilization = $this->calculateBudgetUtilization($user, $month);
        $alerts = [];

        foreach ($utilization as $category) {
            if ($category['is_exceeded']) {
                $alerts[] = [
                    'type' => 'exceeded',
                    'category_name' => $category['category_name'],
                    'budget_amount' => $category['budget_amount'],
                    'spent_amount' => $category['spent_amount'],
                    'excess_amount' => $category['spent_amount'] - $category['budget_amount'],
                    'message' => "Budget exceeded for {$category['category_name']} by $" . number_format($category['spent_amount'] - $category['budget_amount'], 2),
                ];
            } elseif ($category['is_near_limit']) {
                $alerts[] = [
                    'type' => 'warning',
                    'category_name' => $category['category_name'],
                    'budget_amount' => $category['budget_amount'],
                    'spent_amount' => $category['spent_amount'],
                    'remaining_amount' => $category['remaining_amount'],
                    'percentage_used' => $category['percentage_used'],
                    'message' => "Approaching budget limit for {$category['category_name']} (" . number_format($category['percentage_used'], 1) . "% used)",
                ];
            }
        }

        return $alerts;
    }
}
