@php
    $language = $invoice->client->invoice_language ?? 'nl';
    $labels = [
        'nl' => [
            'invoice' => 'Factuur',
            'invoice_date' => 'Factuurdatum',
            'due_date' => 'Vervaldatum',
            'invoice_number' => 'Factuurnummer',
            'description' => 'Omschrijving',
            'unit_price' => 'Bedrag',
            'quantity' => 'Aantal',
            'line_total' => 'Bedrag (excl. BTW)',
            'subtotal' => 'Totaal excl. BTW',
            'vat' => 'Totaal BTW :rate%',
            'total' => 'Te betalen',
            'footer' => 'Wij verzoeken u vriendelijk om de betaling binnen :days dagen te voldoen op rekening :iban ten name van ":company" onder vermelding van het factuurnummer.',
        ],
        'en' => [
            'invoice' => 'Invoice',
            'invoice_date' => 'Invoice date',
            'due_date' => 'Expiration date',
            'invoice_number' => 'Invoice number',
            'description' => 'Description',
            'unit_price' => 'Total',
            'quantity' => 'Amount',
            'line_total' => 'Total (excl. VAT)',
            'subtotal' => 'Total excl. VAT',
            'vat' => 'Total VAT :rate%',
            'total' => 'To be paid',
            'footer' => 'We kindly request that you make payment within :days days to account number :iban in the name of ":company", quoting the invoice number.',
        ],
    ][$language];

    $clientAddress = array_filter([
        $invoice->client->billing_street,
        trim(($invoice->client->billing_postcode ?? '') . ' ' . ($invoice->client->billing_city ?? '')),
    ]);
    $days = $invoice->issue_date->diffInDays($invoice->due_date);
@endphp

<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $labels['invoice'] }} {{ $invoice->invoice_number }}</title>
    <style>
        @font-face {
            font-family: Montserrat;
            font-style: normal;
            font-weight: normal;
            src: url('{{ public_path('fonts/Montserrat-Regular.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: Montserrat;
            font-style: normal;
            font-weight: bold;
            src: url('{{ public_path('fonts/Montserrat-Bold.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: Montserrat;
            font-style: italic;
            font-weight: normal;
            src: url('{{ public_path('fonts/Montserrat-Italic.ttf') }}') format('truetype');
        }
        @page { margin: 0; }
        body {
            background: #fff;
            color: #282828;
            font-family: Montserrat, DejaVu Sans, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.2;
            margin: 0;
        }
        .page {
            background: #fff;
            box-sizing: border-box;
            padding: 24mm 18mm 18mm;
            position: relative;
        }
        .logo {
            opacity: .3;
            width: 54mm;
        }
        .company {
            float: right;
            font-size: 11px;
            padding-top: 1mm;
            text-align: right;
            width: 58mm;
        }
        .company-name,
        .client-name,
        h1,
        th,
        .total-label {
            font-weight: 700;
        }
        .company table {
            border-collapse: collapse;
            margin-left: auto;
            margin-top: 7mm;
            text-align: left;
            width: auto;
        }
        .company td {
            padding: 0 0 .2mm 5mm;
            white-space: nowrap;
        }
        .company .spacer td {
            padding-top: 7mm;
        }
        .company .key {
            font-weight: 700;
            text-align: left;
        }
        .client {
            font-size: 14px;
            margin-top: 24mm;
            min-height: 32mm;
            width: 98mm;
        }
        h1 {
            font-size: 21px;
            margin: 0 0 6mm;
        }
        .meta {
            margin-bottom: 16mm;
            width: 80mm;
        }
        .meta td {
            padding: 0 0 .2mm;
        }
        .meta .label {
            width: 48mm;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            border-bottom: 1px solid #282828;
            border-top: 1px solid #282828;
            font-size: 15px;
            line-height: 1;
            padding: .6mm 0 .9mm;
            text-align: left;
        }
        td {
            padding: 1.8mm 0;
            vertical-align: top;
        }
        .right {
            text-align: right;
        }
        .items .description {
            width: 50%;
        }
        .items th {
            white-space: nowrap;
        }
        .item-title {
            font-style: italic;
        }
        .item-subtitle {
            display: block;
            font-size: 10px;
            font-style: italic;
        }
        .items tbody tr:last-child td {
            border-bottom: 1px solid #282828;
            padding-bottom: 5mm;
        }
        .totals {
            margin-left: auto;
            margin-top: 2mm;
            width: 68mm;
        }
        .totals td {
            padding: 1.2mm 0;
        }
        .footer {
            bottom: 18mm;
            font-size: 12px;
            left: 18mm;
            position: fixed;
            right: 18mm;
        }
        .bar {
            background: #262626;
            bottom: 0;
            height: 2mm;
            left: 0;
            position: fixed;
            right: 0;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="company">
            <span class="company-name">{{ $settings['company_name'] }}</span><br>
            {{ $settings['street'] ?? null }}<br>
            {{ trim(($settings['postcode'] ?? '') . ' ' . ($settings['city'] ?? '')) }}

            <table>
                @if ($settings['phone'] ?? null)
                    <tr>
                        <td class="key">T:</td>
                        <td>{{ $settings['phone'] }}</td>
                    </tr>
                @endif
                @if ($settings['email'] ?? null)
                    <tr>
                        <td class="key">E:</td>
                        <td>{{ $settings['email'] }}</td>
                    </tr>
                @endif
                @if ($settings['website'] ?? null)
                    <tr>
                        <td class="key">W:</td>
                        <td>{{ $settings['website'] }}</td>
                    </tr>
                @endif
                @if ($settings['kvk'] ?? null)
                    <tr class="spacer">
                        <td class="key">KVK:</td>
                        <td>{{ $settings['kvk'] }}</td>
                    </tr>
                @endif
                @if ($settings['vat_number'] ?? null)
                    <tr>
                        <td class="key">BTW:</td>
                        <td>{{ $settings['vat_number'] }}</td>
                    </tr>
                @endif
                @if ($settings['iban'] ?? null)
                    <tr>
                        <td class="key">IBAN:</td>
                        <td>{{ $settings['iban'] }}</td>
                    </tr>
                @endif
                @if ($settings['bic'] ?? null)
                    <tr>
                        <td class="key">BIC:</td>
                        <td>{{ $settings['bic'] }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <img class="logo" src="{{ public_path('images/logo-dark.svg') }}" alt="Qubed">

        <div class="client">
            <span class="client-name">{{ $invoice->client->name }}</span><br>
            @foreach ($clientAddress as $line)
                {{ $line }}<br>
            @endforeach
        </div>

        <h1>{{ $labels['invoice'] }}</h1>

        <table class="meta">
            <tr>
                <td class="label">{{ $labels['invoice_date'] }}:</td>
                <td>{{ $invoice->issue_date->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td>{{ $labels['due_date'] }}:</td>
                <td>{{ $invoice->due_date->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td>{{ $labels['invoice_number'] }}:</td>
                <td>{{ $invoice->invoice_number }}</td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th class="description">{{ $labels['description'] }}</th>
                    <th class="right">{{ $labels['unit_price'] }}</th>
                    <th class="right">{{ $labels['quantity'] }}</th>
                    <th class="right">{{ $labels['line_total'] }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td class="description">
                            <span class="item-title">{{ $item->title }}</span>
                            @if ($item->subtitle)
                                <span class="item-subtitle">{{ $item->subtitle }}</span>
                            @endif
                        </td>
                        <td class="right">&euro;{{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">&euro;{{ number_format((float) $item->total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>{{ $labels['subtotal'] }}</td>
                <td class="right">&euro;{{ number_format((float) $invoice->subtotal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>{{ str_replace(':rate', number_format((float) $invoice->tax_rate, 0), $labels['vat']) }}</td>
                <td class="right">&euro;{{ number_format((float) $invoice->tax_amount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="total-label">{{ $labels['total'] }}</td>
                <td class="right">&euro;{{ number_format((float) $invoice->total, 2, ',', '.') }}</td>
            </tr>
        </table>

        <div class="footer">
            {{ str_replace(
                [':days', ':iban', ':company'],
                [$days, $settings['iban'] ?? '', $settings['company_name'] ?? ''],
                $labels['footer'],
            ) }}
        </div>

        <div class="bar"></div>
    </div>
</body>
</html>
