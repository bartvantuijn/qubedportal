<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucwords(fake()->words(2, true)),
            'price' => fake()->randomFloat(2, 25, 500),
            'frequency' => fake()->randomElement(['daily', 'monthly', 'yearly', 'none']),
            'start' => fake()->dateTimeBetween('-1 years'),
            'end' => null,
            'description' => fake()->sentence(),
        ];
    }
}
