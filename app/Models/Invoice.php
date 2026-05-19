<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Generate the next invoice number in F001.NNN style.
     */
    public static function nextInvoiceNumber(): string
    {
        $latest = self::query()
            ->where('invoice_number', 'like', 'F___.___')
            ->pluck('invoice_number')
            ->map(function (string $invoiceNumber): array {
                [$series, $number] = explode('.', substr($invoiceNumber, 1), 2);

                return [(int) $series, (int) $number];
            })
            ->sortBy(fn (array $number): int => $number[0] * 1000 + $number[1])
            ->last() ?? [1, 0];

        [$series, $number] = $latest;
        $number++;

        if ($number > 999) {
            $series++;
            $number = 1;
        }

        return sprintf('F%03d.%03d', $series, $number);
    }

    public function pdfFilename(): string
    {
        $label = $this->client->invoice_language === 'en' ? 'Invoice' : 'Factuur';

        return $label . '(' . $this->invoice_number . ').pdf';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = round($this->items()->sum('total'), 2);
        $taxAmount = round($subtotal * (float) $this->tax_rate / 100, 2);

        $this->forceFill([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => round($subtotal + $taxAmount, 2),
        ]);
    }
}
