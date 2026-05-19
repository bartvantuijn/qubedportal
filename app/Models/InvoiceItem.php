<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item): void {
            $item->quantity = max(1, (int) $item->quantity);
            $item->unit_price = round((float) $item->unit_price, 2);
            $item->total = round($item->unit_price * $item->quantity, 2);
        });

        static::saved(function (InvoiceItem $item): void {
            $invoice = $item->invoice;

            $invoice?->recalculateTotals();
            $invoice?->save();
        });

        static::deleted(function (InvoiceItem $item): void {
            $invoice = $item->invoice;

            $invoice?->recalculateTotals();
            $invoice?->save();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
