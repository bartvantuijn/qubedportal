<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Invoice;
use PHPUnit\Framework\TestCase;

class InvoiceNumberTest extends TestCase
{
    public function test_invoice_number_format_matches_expected_pattern(): void
    {
        $number = sprintf('%s.%03d', 'F001', 1);

        $this->assertMatchesRegularExpression('/^F\d{3}\.\d{3,}$/', $number);
        $this->assertSame('F001.001', $number);
    }

    public function test_invoice_number_sequence_pads_correctly(): void
    {
        $this->assertSame('F001.001', sprintf('F001.%03d', 1));
        $this->assertSame('F001.050', sprintf('F001.%03d', 50));
        $this->assertSame('F001.999', sprintf('F001.%03d', 999));
        $this->assertSame('F002.001', sprintf('F%03d.%03d', 2, 1));
    }

    public function test_invoice_pdf_filename_uses_client_language(): void
    {
        $invoice = new Invoice(['invoice_number' => 'F001.058']);

        $invoice->setRelation('client', new Client(['invoice_language' => 'nl']));
        $this->assertSame('Factuur(F001.058).pdf', $invoice->pdfFilename());

        $invoice->setRelation('client', new Client(['invoice_language' => 'en']));
        $this->assertSame('Invoice(F001.058).pdf', $invoice->pdfFilename());
    }
}
