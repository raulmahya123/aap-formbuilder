<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('pdf-title', 'Dokumen Mandala')</title>
    <style>
        @page { margin: 32mm 16mm 39mm; }
        * { box-sizing: border-box; }
        html { padding: 0; }
        body { padding: 0; }
        body { color: #18181B; font: 9pt/1.45 'DejaVu Sans', sans-serif; }
        /* Dompdf positions fixed elements from the page's content origin. */
        .pdf-letterhead { position: fixed; top: -32mm; left: -16mm; z-index: -1000; width: 210mm; height: 297mm; }
        .pdf-content { position: relative; z-index: 1; }
        .pdf-eyebrow { margin: 0 0 3mm; color: #713F12; font-size: 8pt; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
        .pdf-title { margin: 0 0 2mm; color: #18181B; font-size: 17pt; line-height: 1.2; font-weight: 700; }
        .pdf-subtitle { margin: 0 0 5mm; color: #52525B; font-size: 9pt; }
        .pdf-meta { margin: 0 0 5mm; color: #52525B; font-size: 8pt; }
        .pdf-section-title { margin: 6mm 0 2mm; padding-bottom: 1.5mm; border-bottom: 1px solid #D4D4D8; color: #18181B; font-size: 11pt; font-weight: 700; page-break-after: avoid; }
        .pdf-table { width: 100%; margin: 0 0 4mm; border-collapse: collapse; table-layout: fixed; }
        .pdf-table thead { display: table-header-group; }
        .pdf-table th, .pdf-table td { padding: 1.6mm 1.8mm; border: 1px solid #D4D4D8; text-align: left; vertical-align: top; overflow-wrap: anywhere; word-break: break-word; }
        .pdf-table th { background: #F4F4F5; color: #18181B; font-size: 7.5pt; font-weight: 700; }
        .pdf-table td { font-size: 8pt; }
        .pdf-table tr { page-break-inside: avoid; }
        .pdf-table--compact th, .pdf-table--compact td { padding: 1mm 1.2mm; font-size: 7pt; }
        .pdf-label { width: 30%; font-weight: 700; }
        .pdf-muted { color: #52525B; }
        .pdf-section-copy { white-space: pre-wrap; overflow-wrap: anywhere; }
        .pdf-empty { padding: 4mm; border: 1px solid #D4D4D8; color: #52525B; }
        .pdf-signature-grid { width: 100%; margin-top: 10mm; table-layout: fixed; border-collapse: collapse; }
        .pdf-signature-grid td { width: 50%; padding: 0 6mm; text-align: center; vertical-align: bottom; }
        .pdf-signature-space { height: 22mm; }
        .pdf-signature-line { padding-top: 1.5mm; border-top: 1px solid #52525B; font-weight: 700; }
        @stack('pdf-styles')
    </style>
</head>
<body>
    @php
        $letterheadPath = public_path('assets/images/andalan-letterhead.jpg');
        $letterheadSource = ($format ?? null) === 'print'
            ? asset('assets/images/andalan-letterhead.jpg')
            : 'data:image/jpeg;base64,'.base64_encode(file_get_contents($letterheadPath));
    @endphp
    <img class="pdf-letterhead" src="{{ $letterheadSource }}" alt="">
    <main class="pdf-content">@yield('pdf-content')</main>
</body>
</html>
