<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(['checking', 'savings', 'credit_card', 'cash']),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'is_active' => true,
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
