<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly Invoice $invoice) {}

    public function envelope(): Envelope
    {
        $invoice = $this->invoice->loadMissing('client');
        $label = $invoice->client->invoice_language === 'en' ? 'Invoice' : 'Factuur';

        return new Envelope(
            subject: $label . ' ' . $invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        $setting = Setting::singleton();
        $invoice = $this->invoice->loadMissing('client');

        return new Content(
            view: 'mail.invoice',
            with: [
                'invoice' => $invoice,
                'settings' => $setting->get('invoice', config('invoice')),
            ],
        );
    }

    public function attachments(): array
    {
        $setting = Setting::singleton();
        $invoice = $this->invoice->loadMissing('items.product', 'client');

        return [
            Attachment::fromData(
                fn () => Pdf::loadView('pdf.invoice', [
                    'invoice' => $invoice,
                    'settings' => $setting->get('invoice', config('invoice')),
                ])->setPaper('a4')->output(),
                $invoice->pdfFilename(),
            )->withMime('application/pdf'),
        ];
    }
}
