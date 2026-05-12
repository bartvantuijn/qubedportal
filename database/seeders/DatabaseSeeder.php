<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\License;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Setting::singleton()->set('invoice', config('invoice'));

        $products = Product::factory(3)->create();

        Client::factory(3)
            ->has(License::factory())
            ->has(
                Invoice::factory()
                    ->has(
                        InvoiceItem::factory(2)
                            ->state(function () use ($products): array {
                                $product = $products->random();

                                return [
                                    'product_id' => $product->id,
                                    'title' => $product->name,
                                    'unit_price' => $product->price,
                                ];
                            }),
                        'items',
                    ),
            )
            ->has(
                Subscription::factory()
                    ->state(function () use ($products): array {
                        $product = $products->random();

                        return [
                            'product_id' => $product->id,
                            'price' => $product->price,
                        ];
                    }),
            )
            ->create();

        Expense::factory(3)->create();
    }
}
