<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'amount', 'month_year', 'spent', 'rollover',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent' => 'decimal:2',
        'month_year' => 'date',
        'rollover' => 'boolean',
    ];

    /**
     * Validation rules for budgets
     */
    public static function rules()
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'month_year' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'rollover' => 'boolean',
        ];
    }

    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($budget) {
            // Ensure amount is positive
            if ($budget->amount <= 0) {
                throw new \InvalidArgumentException('Budget amount must be positive');
            }

            // Ensure month_year is first day of month
            $budget->month_year = \Carbon\Carbon::parse($budget->month_year)->startOfMonth();
        });
    }

    /**
     * Get the remaining budget amount
     */
    public function getRemainingAttribute()
    {
        return $this->amount - $this->spent;
    }

    /**
     * Get the budget utilization percentage
     */
    public function getUtilizationPercentageAttribute()
    {
        if ($this->amount == 0) {
            return 0;
        }
        return round(($this->spent / $this->amount) * 100, 2);
    }

    /**
     * Check if budget is exceeded
     */
    public function isExceeded()
    {
        return $this->spent > $this->amount;
    }

    /**
     * Check if budget is close to limit (80% or more)
     */
    public function isNearLimit()
    {
        return $this->utilization_percentage >= 80;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
