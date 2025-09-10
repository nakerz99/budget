<?php

namespace App\Services;

use App\Models\User;
use App\Models\Category;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Bill;
use App\Models\SavingsGoal;
use Carbon\Carbon;

class HouseholdDataService
{
    /**
     * Create household data for a user
     */
    public function createHouseholdData(User $user)
    {
        // Create household categories
        $categories = $this->createCategories($user);
        
        // Create accounts
        $accounts = $this->createAccounts($user);
        
        // Create 6 months of transaction history
        $this->createTransactionHistory($user, $categories, $accounts);
        
        // Create monthly bills
        $this->createBills($user, $categories);
        
        // Create budgets
        $this->createBudgets($user, $categories);
        
        // Create savings goals
        $this->createSavingsGoals($user, $accounts);
        
        return true;
    }

    private function createCategories($user)
    {
        $categoriesData = [
            // Income categories
            'Salary' => ['type' => 'income', 'color' => '#10B981', 'icon' => 'briefcase'],
            'Freelance' => ['type' => 'income', 'color' => '#06B6D4', 'icon' => 'laptop'],
            'Business Income' => ['type' => 'income', 'color' => '#8B5CF6', 'icon' => 'store'],
            'Other Income' => ['type' => 'income', 'color' => '#EC4899', 'icon' => 'coins'],
            
            // Expense categories - Household
            'Groceries' => ['type' => 'expense', 'color' => '#EF4444', 'icon' => 'shopping-cart'],
            'Electricity' => ['type' => 'expense', 'color' => '#F59E0B', 'icon' => 'lightbulb'],
            'Water' => ['type' => 'expense', 'color' => '#3B82F6', 'icon' => 'water'],
            'Internet' => ['type' => 'expense', 'color' => '#8B5CF6', 'icon' => 'wifi'],
            'Rent/Housing' => ['type' => 'expense', 'color' => '#6366F1', 'icon' => 'home'],
            'Gas/LPG' => ['type' => 'expense', 'color' => '#F97316', 'icon' => 'fire'],
            
            // Daily living expenses
            'Food & Dining' => ['type' => 'expense', 'color' => '#DC2626', 'icon' => 'utensils'],
            'Transportation' => ['type' => 'expense', 'color' => '#059669', 'icon' => 'car'],
            'Healthcare' => ['type' => 'expense', 'color' => '#DC2626', 'icon' => 'heart'],
            'Personal Care' => ['type' => 'expense', 'color' => '#EC4899', 'icon' => 'user'],
            'Clothing' => ['type' => 'expense', 'color' => '#7C3AED', 'icon' => 'shirt'],
            'Household Items' => ['type' => 'expense', 'color' => '#2563EB', 'icon' => 'couch'],
            
            // Family expenses
            'School/Education' => ['type' => 'expense', 'color' => '#0891B2', 'icon' => 'graduation-cap'],
            'Baby Needs' => ['type' => 'expense', 'color' => '#F472B6', 'icon' => 'baby'],
            'Pet Care' => ['type' => 'expense', 'color' => '#84CC16', 'icon' => 'paw'],
            'Helper/Maid' => ['type' => 'expense', 'color' => '#6B7280', 'icon' => 'hands-helping'],
            
            // Others
            'Entertainment' => ['type' => 'expense', 'color' => '#A855F7', 'icon' => 'gamepad'],
            'Subscriptions' => ['type' => 'expense', 'color' => '#6366F1', 'icon' => 'tv'],
            'Insurance' => ['type' => 'expense', 'color' => '#0EA5E9', 'icon' => 'shield-alt'],
            'Loans/Credit' => ['type' => 'expense', 'color' => '#EF4444', 'icon' => 'credit-card'],
            'Savings' => ['type' => 'expense', 'color' => '#10B981', 'icon' => 'piggy-bank'],
            'Emergency' => ['type' => 'expense', 'color' => '#F59E0B', 'icon' => 'exclamation-triangle'],
        ];

        $categories = [];
        foreach ($categoriesData as $name => $data) {
            $categories[$name] = Category::create([
                'user_id' => $user->id,
                'name' => $name,
                'type' => $data['type'],
                'color' => $data['color'],
                'icon' => $data['icon'],
                'is_active' => true,
            ]);
        }

        return $categories;
    }

    private function createAccounts($user)
    {
        $accountsData = [
            ['name' => 'BPI Savings', 'type' => 'savings', 'balance' => 0, 'color' => '#DC2626'],
            ['name' => 'BDO Checking', 'type' => 'checking', 'balance' => 0, 'color' => '#2563EB'],
            ['name' => 'Cash on Hand', 'type' => 'cash', 'balance' => 0, 'color' => '#10B981'],
            ['name' => 'GCash', 'type' => 'wallet', 'balance' => 0, 'color' => '#0EA5E9'],
            ['name' => 'Emergency Fund', 'type' => 'savings', 'balance' => 0, 'color' => '#F59E0B'],
        ];

        $accounts = [];
        foreach ($accountsData as $data) {
            $accounts[$data['name']] = Account::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'type' => $data['type'],
                'balance' => $data['balance'],
                'color' => $data['color'],
                'is_active' => true,
            ]);
        }

        return $accounts;
    }

    private function createTransactionHistory($user, $categories, $accounts)
    {
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        
        for ($month = 0; $month < 6; $month++) {
            $currentMonth = $startDate->copy()->addMonths($month);
            
            // Monthly income
            $this->createMonthlyIncome($user, $categories, $accounts, $currentMonth);
            
            // Fixed monthly expenses
            $this->createFixedMonthlyExpenses($user, $categories, $accounts, $currentMonth);
            
            // Daily expenses throughout the month
            $this->createDailyExpenses($user, $categories, $accounts, $currentMonth);
            
            // Variable monthly expenses
            $this->createVariableExpenses($user, $categories, $accounts, $currentMonth);
        }
    }

    private function createMonthlyIncome($user, $categories, $accounts, $month)
    {
        // Main salary - 5th and 20th
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accounts['BDO Checking']->id,
            'category_id' => $categories['Salary']->id,
            'amount' => 50000,
            'type' => 'income',
            'description' => 'Monthly Salary - 1st half',
            'transaction_date' => $month->copy()->day(5),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accounts['BDO Checking']->id,
            'category_id' => $categories['Salary']->id,
            'amount' => 50000,
            'type' => 'income',
            'description' => 'Monthly Salary - 2nd half',
            'transaction_date' => $month->copy()->day(20),
        ]);

        // Occasional freelance income
        if (rand(1, 3) == 1) {
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $accounts['GCash']->id,
                'category_id' => $categories['Freelance']->id,
                'amount' => rand(5000, 15000),
                'type' => 'income',
                'description' => 'Freelance project payment',
                'transaction_date' => $month->copy()->day(rand(10, 25)),
            ]);
        }
    }

    private function createFixedMonthlyExpenses($user, $categories, $accounts, $month)
    {
        // Rent/Housing
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accounts['BDO Checking']->id,
            'category_id' => $categories['Rent/Housing']->id,
            'amount' => 15000,
            'type' => 'expense',
            'description' => 'Monthly Rent',
            'transaction_date' => $month->copy()->day(1),
        ]);

        // Electricity
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accounts['BDO Checking']->id,
            'category_id' => $categories['Electricity']->id,
            'amount' => rand(3500, 5500),
            'type' => 'expense',
            'description' => 'Meralco Bill',
            'transaction_date' => $month->copy()->day(20),
        ]);

        // Water
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accounts['Cash on Hand']->id,
            'category_id' => $categories['Water']->id,
            'amount' => rand(300, 500),
            'type' => 'expense',
            'description' => 'Water Bill',
            'transaction_date' => $month->copy()->day(15),
        ]);

        // Internet
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accounts['BDO Checking']->id,
            'category_id' => $categories['Internet']->id,
            'amount' => 1899,
            'type' => 'expense',
            'description' => 'PLDT Fibr',
            'transaction_date' => $month->copy()->day(25),
        ]);

        // Helper/Maid
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accounts['Cash on Hand']->id,
            'category_id' => $categories['Helper/Maid']->id,
            'amount' => 5000,
            'type' => 'expense',
            'description' => 'Helper Salary',
            'transaction_date' => $month->copy()->day(30),
        ]);

        // Insurance
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accounts['BDO Checking']->id,
            'category_id' => $categories['Insurance']->id,
            'amount' => 2500,
            'type' => 'expense',
            'description' => 'Life Insurance Premium',
            'transaction_date' => $month->copy()->day(10),
        ]);

        // Savings
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $accounts['BPI Savings']->id,
            'category_id' => $categories['Savings']->id,
            'amount' => 10000,
            'type' => 'expense',
            'description' => 'Monthly Savings',
            'transaction_date' => $month->copy()->day(5),
        ]);
    }

    private function createDailyExpenses($user, $categories, $accounts, $month)
    {
        $daysInMonth = $month->daysInMonth;
        
        // Groceries - weekly
        for ($week = 1; $week <= 4; $week++) {
            $day = ($week - 1) * 7 + rand(1, 7);
            if ($day <= $daysInMonth) {
                Transaction::create([
                    'user_id' => $user->id,
                    'account_id' => $accounts['Cash on Hand']->id,
                    'category_id' => $categories['Groceries']->id,
                    'amount' => rand(2500, 4000),
                    'type' => 'expense',
                    'description' => 'Weekly Grocery - SM/Puregold',
                    'transaction_date' => $month->copy()->day($day),
                ]);
            }
        }

        // Daily food expenses
        for ($day = 1; $day <= $daysInMonth; $day++) {
            // Breakfast/Lunch/Dinner randomly
            if (rand(1, 3) <= 2) { // 66% chance of eating out
                $meals = ['Breakfast', 'Lunch', 'Dinner', 'Snacks', 'Food Delivery'];
                $places = ['Jollibee', 'McDo', 'KFC', 'Chowking', 'Mang Inasal', '7-Eleven', 'Ministop', 'GrabFood', 'FoodPanda'];
                
                Transaction::create([
                    'user_id' => $user->id,
                    'account_id' => rand(1, 2) == 1 ? $accounts['Cash on Hand']->id : $accounts['GCash']->id,
                    'category_id' => $categories['Food & Dining']->id,
                    'amount' => rand(150, 500),
                    'type' => 'expense',
                    'description' => $meals[array_rand($meals)] . ' - ' . $places[array_rand($places)],
                    'transaction_date' => $month->copy()->day($day),
                ]);
            }

            // Transportation
            if (rand(1, 7) <= 5) { // Weekdays mostly
                $transport = ['Grab', 'Angkas', 'Jeep', 'Tricycle', 'Gas', 'Parking'];
                Transaction::create([
                    'user_id' => $user->id,
                    'account_id' => rand(1, 2) == 1 ? $accounts['Cash on Hand']->id : $accounts['GCash']->id,
                    'category_id' => $categories['Transportation']->id,
                    'amount' => rand(50, 300),
                    'type' => 'expense',
                    'description' => $transport[array_rand($transport)],
                    'transaction_date' => $month->copy()->day($day),
                ]);
            }
        }
    }

    private function createVariableExpenses($user, $categories, $accounts, $month)
    {
        // Personal Care - 2-3 times a month
        $personalCareItems = ['Shampoo/Conditioner', 'Soap/Body Wash', 'Toothpaste', 'Skincare', 'Haircut', 'Salon'];
        for ($i = 0; $i < rand(2, 3); $i++) {
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $accounts['Cash on Hand']->id,
                'category_id' => $categories['Personal Care']->id,
                'amount' => rand(200, 1500),
                'type' => 'expense',
                'description' => $personalCareItems[array_rand($personalCareItems)] . ' - Watsons/Mercury',
                'transaction_date' => $month->copy()->day(rand(1, 28)),
            ]);
        }

        // Healthcare - occasional
        if (rand(1, 2) == 1) {
            $healthItems = ['Medicine', 'Doctor Consultation', 'Lab Tests', 'Vitamins'];
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $accounts['BDO Checking']->id,
                'category_id' => $categories['Healthcare']->id,
                'amount' => rand(500, 3000),
                'type' => 'expense',
                'description' => $healthItems[array_rand($healthItems)] . ' - Mercury/Hospital',
                'transaction_date' => $month->copy()->day(rand(1, 28)),
            ]);
        }

        // Household Items
        $householdItems = ['Cleaning Supplies', 'Kitchen Items', 'Bedroom Items', 'Bathroom Items', 'Light Bulbs'];
        for ($i = 0; $i < rand(1, 3); $i++) {
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $accounts['Cash on Hand']->id,
                'category_id' => $categories['Household Items']->id,
                'amount' => rand(300, 2000),
                'type' => 'expense',
                'description' => $householdItems[array_rand($householdItems)] . ' - ACE/Handyman',
                'transaction_date' => $month->copy()->day(rand(1, 28)),
            ]);
        }

        // Entertainment
        if (rand(1, 3) <= 2) {
            $entertainment = ['Netflix', 'Movie Tickets', 'Mall', 'Restaurant', 'Coffee Shop'];
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => rand(1, 2) == 1 ? $accounts['Cash on Hand']->id : $accounts['GCash']->id,
                'category_id' => $categories['Entertainment']->id,
                'amount' => rand(200, 2000),
                'type' => 'expense',
                'description' => $entertainment[array_rand($entertainment)],
                'transaction_date' => $month->copy()->day(rand(1, 28)),
            ]);
        }

        // School/Education (if applicable)
        if ($month->day == 1 && in_array($month->month, [6, 7, 8, 1])) {
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $accounts['BDO Checking']->id,
                'category_id' => $categories['School/Education']->id,
                'amount' => rand(10000, 25000),
                'type' => 'expense',
                'description' => 'Tuition Fee / School Supplies',
                'transaction_date' => $month->copy()->day(rand(1, 15)),
            ]);
        }

        // Gas/LPG
        if (rand(1, 2) == 1) {
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $accounts['Cash on Hand']->id,
                'category_id' => $categories['Gas/LPG']->id,
                'amount' => rand(600, 800),
                'type' => 'expense',
                'description' => 'LPG Tank Refill - Petron/Solane',
                'transaction_date' => $month->copy()->day(rand(1, 28)),
            ]);
        }
    }

    private function createBills($user, $categories)
    {
        $bills = [
            [
                'name' => 'Meralco (Electricity)',
                'category_id' => $categories['Electricity']->id,
                'amount' => 4500,
                'due_date' => Carbon::now()->day(20),
                'frequency' => 'monthly',
            ],
            [
                'name' => 'Manila Water',
                'category_id' => $categories['Water']->id,
                'amount' => 400,
                'due_date' => Carbon::now()->day(15),
                'frequency' => 'monthly',
            ],
            [
                'name' => 'PLDT Internet',
                'category_id' => $categories['Internet']->id,
                'amount' => 1899,
                'due_date' => Carbon::now()->day(25),
                'frequency' => 'monthly',
            ],
            [
                'name' => 'House Rent',
                'category_id' => $categories['Rent/Housing']->id,
                'amount' => 15000,
                'due_date' => Carbon::now()->day(1),
                'frequency' => 'monthly',
            ],
            [
                'name' => 'Life Insurance',
                'category_id' => $categories['Insurance']->id,
                'amount' => 2500,
                'due_date' => Carbon::now()->day(10),
                'frequency' => 'monthly',
            ],
            [
                'name' => 'Netflix Subscription',
                'category_id' => $categories['Subscriptions']->id,
                'amount' => 549,
                'due_date' => Carbon::now()->day(5),
                'frequency' => 'monthly',
            ],
        ];

        foreach ($bills as $billData) {
            Bill::create([
                'user_id' => $user->id,
                'category_id' => $billData['category_id'],
                'name' => $billData['name'],
                'amount' => $billData['amount'],
                'due_date' => $billData['due_date'],
                'frequency' => $billData['frequency'],
                'is_paid' => false,
                'is_recurring' => true,
                'notes' => 'Auto-generated household bill',
            ]);
        }
    }

    private function createBudgets($user, $categories)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        $budgets = [
            'Groceries' => 15000,
            'Food & Dining' => 10000,
            'Transportation' => 5000,
            'Electricity' => 5000,
            'Water' => 500,
            'Internet' => 2000,
            'Personal Care' => 3000,
            'Healthcare' => 3000,
            'Household Items' => 3000,
            'Entertainment' => 3000,
            'School/Education' => 5000,
            'Gas/LPG' => 1000,
        ];

        foreach ($budgets as $categoryName => $amount) {
            if (isset($categories[$categoryName])) {
                Budget::create([
                    'user_id' => $user->id,
                    'category_id' => $categories[$categoryName]->id,
                    'amount' => $amount,
                    'month_year' => $currentMonth,
                    'spent' => 0,
                    'rollover' => false,
                ]);
            }
        }
    }

    private function createSavingsGoals($user, $accounts)
    {
        $goals = [
            [
                'name' => 'Emergency Fund (6 months expenses)',
                'target_amount' => 300000,
                'current_amount' => 50000,
                'target_date' => Carbon::now()->addYear(),
                'account_id' => $accounts['Emergency Fund']->id,
                'color' => '#F59E0B',
            ],
            [
                'name' => 'House Down Payment',
                'target_amount' => 500000,
                'current_amount' => 25000,
                'target_date' => Carbon::now()->addYears(2),
                'account_id' => $accounts['BPI Savings']->id,
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Kids Education Fund',
                'target_amount' => 200000,
                'current_amount' => 10000,
                'target_date' => Carbon::now()->addYears(3),
                'account_id' => $accounts['BPI Savings']->id,
                'color' => '#10B981',
            ],
            [
                'name' => 'Family Vacation',
                'target_amount' => 50000,
                'current_amount' => 5000,
                'target_date' => Carbon::now()->addMonths(6),
                'account_id' => $accounts['BDO Checking']->id,
                'color' => '#8B5CF6',
            ],
        ];

        foreach ($goals as $goalData) {
            SavingsGoal::create([
                'user_id' => $user->id,
                'account_id' => $goalData['account_id'],
                'name' => $goalData['name'],
                'target_amount' => $goalData['target_amount'],
                'current_amount' => $goalData['current_amount'],
                'target_date' => $goalData['target_date'],
                'color' => $goalData['color'],
                'is_completed' => false,
            ]);
        }
    }
}
