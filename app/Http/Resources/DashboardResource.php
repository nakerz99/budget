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
                'total' => (float) $this->monthly_spending['total'],
                'budget_limit' => (float) $this->monthly_spending['budget_limit'],
                'remaining' => (float) $this->monthly_spending['remaining'],
                'percentage_used' => (float) $this->monthly_spending['percentage_used'],
            ],
            'budget_status' => [
                'total_budget' => (float) $this->budget_status['total_budget'],
                'total_spent' => (float) $this->budget_status['total_spent'],
                'remaining' => (float) $this->budget_status['remaining'],
                'categories' => BudgetResource::collection($this->budget_status['categories']),
            ],
            'spending_by_category' => $this->spending_by_category,
            'recent_transactions' => TransactionResource::collection($this->recent_transactions),
            'upcoming_bills' => BillResource::collection($this->upcoming_bills),
            'account_balances' => AccountResource::collection($this->account_balances),
            'savings_progress' => [
                'total_saved' => (float) $this->savings_progress['total_saved'],
                'active_goals' => (int) $this->savings_progress['active_goals'],
                'goals' => SavingsGoalResource::collection($this->savings_progress['goals']),
            ],
        ];
    }
}
