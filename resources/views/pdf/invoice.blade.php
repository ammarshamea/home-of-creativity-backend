<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1a1a1a; margin: 40px; }
        .brand { color: #0f766e; font-size: 24px; font-weight: bold; }
        h1 { font-size: 20px; margin-top: 24px; }
        .meta { margin: 16px 0; line-height: 1.6; }
        .amount { font-size: 22px; font-weight: bold; color: #0f766e; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="brand">Home of Creativity</div>
    <h1>Invoice {{ $invoiceNumber }}</h1>
    <div class="meta">
        <div><strong>Request:</strong> {{ $request->number }}</div>
        <div><strong>Client:</strong> {{ $request->client?->name }}</div>
        <div><strong>Payment method:</strong> {{ match ($paymentMethod) {
            'receipt' => 'Bank transfer / receipt',
            'cash' => 'Cash',
            default => ucfirst($paymentMethod),
        } }}</div>
        <div><strong>Date:</strong> {{ now()->format('Y-m-d') }}</div>
    </div>
    <div class="amount">Amount: {{ number_format($amount, 2) }} USD</div>
</body>
</html>
