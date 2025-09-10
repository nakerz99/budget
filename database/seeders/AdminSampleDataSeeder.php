<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\HouseholdDataService;

class AdminSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds for admin user
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Adding household data for admin user...');

        $admin = User::where('username', 'admin')->first();

        if (!$admin) {
            $this->command->error('Admin user not found. Please run AdminUserSeeder first.');
            return;
        }

        // Clear existing data for admin to avoid duplicates if run multiple times
        \App\Models\Category::where('user_id', $admin->id)->delete();
        \App\Models\Account::where('user_id', $admin->id)->delete();
        \App\Models\Transaction::where('user_id', $admin->id)->delete();
        \App\Models\Budget::where('user_id', $admin->id)->delete();
        \App\Models\Bill::where('user_id', $admin->id)->delete();
        \App\Models\SavingsGoal::where('user_id', $admin->id)->delete();

        // Create household data for admin user
        $householdService = new HouseholdDataService();
        $householdService->createHouseholdData($admin);

        $this->command->info('Admin household data created successfully!');
        $this->command->info('Admin now has the same data as household users');
    }
}
