<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalResource extends JsonResource
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
            'name' => $this->name,
            'target_amount' => (float) $this->target_amount,
            'current_amount' => (float) $this->current_amount,
            'target_date' => $this->target_date->toDateString(),
            'color' => $this->color,
            'is_completed' => $this->is_completed,
            'progress_percentage' => (float) $this->progress_percentage,
            'remaining_amount' => (float) $this->remaining_amount,
            'days_remaining' => $this->days_remaining,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relationships
            'account' => new AccountResource($this->whenLoaded('account')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
