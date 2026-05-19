<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
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
            'invoice_number' => sprintf('F001.%03d', fake()->unique()->numberBetween(1, 999)),
            'status' => fake()->randomElement(['draft', 'sent', 'paid', 'overdue', 'cancelled']),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 0,
            'tax_rate' => 21,
            'tax_amount' => 0,
            'total' => 0,
        ];
    }
}
