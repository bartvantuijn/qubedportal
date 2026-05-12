<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 50, 250);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'invoice_id' => Invoice::factory(),
            'product_id' => Product::factory(),
            'title' => ucfirst(fake()->words(3, true)),
            'subtitle' => fake()->optional()->sentence(),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total' => round($unitPrice * $quantity, 2),
        ];
    }
}
