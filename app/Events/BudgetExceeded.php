<?php

namespace App\Events;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetExceeded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $budget;
    public $category;
    public $amountSpent;
    public $percentageUsed;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Budget $budget, Category $category, $amountSpent)
    {
        $this->budget = $budget;
        $this->category = $category;
        $this->amountSpent = $amountSpent;
        $this->percentageUsed = $budget->amount > 0 ? round(($amountSpent / $budget->amount) * 100, 1) : 0;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->budget->user_id);
    }
}
