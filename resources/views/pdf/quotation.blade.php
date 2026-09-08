<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>عرض سعر {{ $request->number }}</title>
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
    <h1>عرض سعر #{{ $request->number }} — الإصدار {{ $version }}</h1>
    <div class="meta">
        <div><strong>العنوان:</strong> {{ $request->title }}</div>
        <div><strong>العميل:</strong> {{ $request->client?->name }}</div>
        @if($notes)
            <div><strong>ملاحظات:</strong> {{ $notes }}</div>
        @endif
    </div>
    <div class="amount">المبلغ: {{ number_format($amount, 2) }} USD</div>
</body>
</html>
