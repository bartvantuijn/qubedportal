<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<License>
 */
class LicenseFactory extends Factory
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
            'domain' => fake()->unique()->domainName(),
            'key' => str()->random(20),
            'verified_at' => fake()->optional()->dateTimeBetween('-1 months'),
            'expires_at' => null,
        ];
    }
}
