<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_totals_are_recalculated_when_items_are_created(): void
    {
        $client = Client::create([
            'name' => 'Test Client',
            'email' => 'test@example.com',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'TEST-' . uniqid(),
            'status' => 'draft',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'tax_rate' => 21,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Development',
            'unit_price' => 100,
            'quantity' => 2,
        ]);

        $invoice->refresh();

        $this->assertEquals(200.00, (float) $invoice->subtotal);
        $this->assertEquals(42.00, (float) $invoice->tax_amount);
        $this->assertEquals(242.00, (float) $invoice->total);
    }
}
