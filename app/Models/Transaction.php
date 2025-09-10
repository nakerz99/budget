<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'account_id', 'category_id', 'amount', 'type', 'description',
        'transaction_date', 'location', 'receipt_path', 'is_recurring', 'recurring_data',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'recurring_data' => 'array',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    /**
     * Validation rules for transactions
     */
    public static function rules()
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense,transfer',
            'description' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date|before_or_equal:today',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'location' => 'nullable|string|max:255',
            'is_recurring' => 'boolean',
            'recurring_data' => 'nullable|array',
        ];
    }

    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Ensure amount is positive
            if ($transaction->amount <= 0) {
                throw new \InvalidArgumentException('Transaction amount must be positive');
            }

            // Validate transaction type
            if (!in_array($transaction->type, ['income', 'expense', 'transfer'])) {
                throw new \InvalidArgumentException('Invalid transaction type');
            }
        });

        static::created(function ($transaction) {
            // Update account balance
            $transaction->updateAccountBalance();
        });

        static::updated(function ($transaction) {
            // Update account balance if amount changed
            if ($transaction->isDirty('amount') || $transaction->isDirty('type')) {
                $transaction->updateAccountBalance();
            }
        });

        static::deleted(function ($transaction) {
            // Revert account balance
            $transaction->revertAccountBalance();
        });
    }

    /**
     * Update account balance based on transaction
     */
    public function updateAccountBalance()
    {
        $account = $this->account;
        
        if ($this->type === 'income') {
            $account->increment('balance', $this->amount);
        } elseif ($this->type === 'expense') {
            $account->decrement('balance', $this->amount);
        }
        // Transfer doesn't change balance (handled by two transactions)
    }

    /**
     * Revert account balance when transaction is deleted
     */
    public function revertAccountBalance()
    {
        $account = $this->account;
        
        if ($this->type === 'income') {
            $account->decrement('balance', $this->amount);
        } elseif ($this->type === 'expense') {
            $account->increment('balance', $this->amount);
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
