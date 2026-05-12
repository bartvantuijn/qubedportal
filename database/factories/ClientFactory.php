<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'billing_street' => fake()->streetAddress(),
            'billing_postcode' => fake()->postcode(),
            'billing_city' => fake()->city(),
            'invoice_language' => fake()->randomElement(['nl', 'en']),
        ];
    }
}
