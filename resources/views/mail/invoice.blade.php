@php
    $language = $invoice->client->invoice_language === 'en' ? 'en' : 'nl';
    $labels = [
        'nl' => [
            'invoice' => 'Factuur',
            'greeting' => 'Beste',
            'message' => 'Bijgaand de factuur met factuurnummer :number.',
            'closing' => 'Met vriendelijke groet',
        ],
        'en' => [
            'invoice' => 'Invoice',
            'greeting' => 'Dear',
            'message' => 'Please find attached invoice :number.',
            'closing' => 'Kind regards',
        ],
    ][$language];
@endphp

<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $labels['invoice'] }} {{ $invoice->invoice_number }}</title>
</head>
<body>
<p>{{ $labels['greeting'] }} {{ $invoice->client->name }},</p>
<p>{{ str_replace(':number', $invoice->invoice_number, $labels['message']) }}</p>
<p>{{ $labels['closing'] }},<br>{{ $settings['company_name'] }}</p>
</body>
</html>
