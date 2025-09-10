<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'full_name',
        'pin',
        'currency',
        'timezone',
        'is_admin',
        'is_approved',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pin',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_admin' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];
    
    /**
     * The attributes that should have default values.
     *
     * @var array
     */
    protected $attributes = [
        'currency' => 'PHP',
        'timezone' => 'Asia/Manila',
    ];

    /**
     * Get the categories for the user.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the accounts for the user.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the budgets for the user.
     */
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get the savings goals for the user.
     */
    public function savingsGoals()
    {
        return $this->hasMany(SavingsGoal::class);
    }

    /**
     * Get the bills for the user.
     */
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get the approval request for the user.
     */
    public function approvalRequest()
    {
        return $this->hasOne(UserApprovalRequest::class);
    }

    /**
     * Get users approved by this admin.
     */
    public function approvedUsers()
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    /**
     * Authenticate user with username and PIN.
     */
    public static function authenticate($username, $pin)
    {
        $user = static::where('username', $username)
                     ->where('is_approved', true)
                     ->first();

        if ($user && $user->pin === $pin) {
            return $user;
        }

        return null;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Check if user is approved.
     */
    public function isApproved()
    {
        return $this->is_approved;
    }
}
