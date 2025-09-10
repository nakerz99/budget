<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
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
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'spent' => (float) $this->spent,
            'month_year' => $this->month_year->format('Y-m-d'),
            'rollover' => $this->rollover,
            'remaining' => (float) $this->remaining,
            'utilization_percentage' => (float) $this->utilization_percentage,
            'is_exceeded' => $this->isExceeded(),
            'is_near_limit' => $this->isNearLimit(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relationships
            'category' => new CategoryResource($this->whenLoaded('category')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
