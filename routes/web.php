<?php

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/invoices/{invoice}/preview', function (Invoice $invoice) {
    $invoice->load('items.product', 'client');
    $setting = Setting::singleton();

    return Pdf::loadView('pdf.invoice', [
        'invoice' => $invoice,
        'settings' => $setting->get('invoice', config('invoice')),
    ])
        ->setPaper('a4')
        ->stream($invoice->pdfFilename());
})->middleware('auth')->name('invoices.preview');
