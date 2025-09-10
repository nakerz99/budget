<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\HouseholdDataService;

class HouseholdDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Creating household data seeder...');

        // Create household user
        $user = User::create([
            'username' => 'household',
            'full_name' => 'Household Manager',
            'pin' => '123456',
            'is_admin' => false,
            'is_approved' => true,
            'approved_at' => now(),
            'currency' => 'PHP',
            'timezone' => 'Asia/Manila',
        ]);

        $this->command->info('Created user: household (PIN: 123456)');

        // Create household data using the service
        $householdService = new HouseholdDataService();
        $householdService->createHouseholdData($user);

        $this->command->info('Household data seeded successfully!');
        $this->command->info('Login with username: household, PIN: 123456');
    }

}
