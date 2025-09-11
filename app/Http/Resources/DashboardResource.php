<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'monthly_spending' => [
                'total' => (float) $this->monthly_spending['total_spent'],
                'total_income' => (float) $this->monthly_spending['total_income'],
                'net_flow' => (float) $this->monthly_spending['net_flow'],
                'month' => $this->monthly_spending['month'],
            ],
            'budget_progress' => [
                'total_budget' => (float) $this->budget_progress['total_budget'],
                'total_spent' => (float) $this->budget_progress['total_spent'],
                'remaining' => (float) $this->budget_progress['remaining'],
                'categories' => BudgetResource::collection($this->budget_progress['categories']),
            ],
            'spending_by_category' => $this->spending_by_category,
            'recent_transactions' => TransactionResource::collection($this->recent_transactions),
            'upcoming_bills' => [
                'upcoming' => BillResource::collection($this->upcoming_bills['upcoming']),
                'overdue' => BillResource::collection($this->upcoming_bills['overdue']),
                'total_upcoming' => (float) $this->upcoming_bills['total_upcoming'],
                'total_overdue' => (float) $this->upcoming_bills['total_overdue'],
            ],
            'account_balances' => [
                'total_balance' => (float) $this->account_balances['total_balance'],
                'accounts' => AccountResource::collection($this->account_balances['accounts']),
            ],
            'savings_progress' => [
                'total_current' => (float) $this->savings_progress['total_current'],
                'total_target' => (float) $this->savings_progress['total_target'],
                'total_remaining' => (float) $this->savings_progress['total_remaining'],
                'overall_percentage' => (float) $this->savings_progress['overall_percentage'],
                'goals' => SavingsGoalResource::collection($this->savings_progress['goals']),
            ],
        ];
    }
}
