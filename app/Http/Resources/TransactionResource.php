<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'type' => $this->type,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date->toDateString(),
            'location' => $this->location,
            'receipt_path' => $this->receipt_path,
            'is_recurring' => $this->is_recurring,
            'recurring_data' => $this->recurring_data,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relationships
            'account' => new AccountResource($this->whenLoaded('account')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
