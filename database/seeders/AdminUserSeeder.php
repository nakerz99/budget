<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        User::create([
            'username' => 'admin',
            'full_name' => 'System Administrator',
            'pin' => '123456', // Default admin PIN - should be changed in production
            'currency' => 'PHP',
            'timezone' => 'Asia/Manila',
            'is_admin' => true,
            'is_approved' => true,
            'approved_at' => now(),
        ]);
    }
}
