<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'product_id' => Product::factory(),
            'price' => fake()->randomFloat(2, 25, 250),
            'frequency' => fake()->randomElement(['daily', 'monthly', 'yearly', 'none']),
            'start' => fake()->dateTimeBetween('-1 years'),
            'end' => null,
            'description' => fake()->sentence(),
        ];
    }
}
