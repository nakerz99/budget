<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'account_id', 'name', 'target_amount', 'current_amount',
        'target_date', 'color', 'is_completed',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
        'is_completed' => 'boolean',
    ];

    /**
     * Validation rules for savings goals
     */
    public static function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0.01',
            'current_amount' => 'nullable|numeric|min:0',
            'target_date' => 'required|date|after:today',
            'account_id' => 'required|exists:accounts,id',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($goal) {
            // Ensure target amount is positive
            if ($goal->target_amount <= 0) {
                throw new \InvalidArgumentException('Target amount must be positive');
            }

            // Ensure target date is in the future
            if ($goal->target_date <= now()) {
                throw new \InvalidArgumentException('Target date must be in the future');
            }

            // Set default color if not provided
            if (!$goal->color) {
                $goal->color = '#8B5CF6';
            }
        });

        static::updating(function ($goal) {
            // Check if goal is completed
            if ($goal->current_amount >= $goal->target_amount) {
                $goal->is_completed = true;
            }
        });
    }

    /**
     * Get the progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->target_amount == 0) {
            return 0;
        }
        return round(($this->current_amount / $this->target_amount) * 100, 2);
    }

    /**
     * Get the remaining amount needed
     */
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Get the days remaining until target date
     */
    public function getDaysRemainingAttribute()
    {
        return max(0, now()->diffInDays($this->target_date, false));
    }

    /**
     * Check if goal is completed
     */
    public function isCompleted()
    {
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Add amount to current savings
     */
    public function addAmount($amount)
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        $this->increment('current_amount', $amount);

        // Check if goal is now completed
        if ($this->current_amount >= $this->target_amount) {
            $this->update(['is_completed' => true]);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
