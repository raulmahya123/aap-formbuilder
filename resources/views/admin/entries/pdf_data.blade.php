<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>{{ $entry->form->title }} — Data</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    :root{ --text:#111; --muted:#666; --line:#1f1f1f; --soft:#e5e5e5; }
    *{ font-family:'Poppins', Arial, sans-serif; }
    html,body{ margin:0; padding:0; color:var(--text); font-size:12px; line-height:1.55; }

    @page{ size:A4; margin:18mm 16mm 18mm 16mm; }
    @media print{ a{ color:inherit; text-decoration:none; } }

    /* ===== CONTAINER (rapih tengah) ===== */
    .container{
      max-width: 720px;   /* atur lebar dokumen */
      margin: 0 auto;     /* center horizontal */
    }

    /* ===== HEADER ===== */
    .letterhead{ width:100%; border-bottom:2px solid var(--line); padding-bottom:6px; margin-bottom:14px; }
    .letterhead td{ vertical-align:middle; }
    .logo{ height:70px; }
    .company-name{ font-size:16px; font-weight:600; line-height:1.2; }
    .company-name span{ color:#b22222; font-weight:500; font-style:italic; }
    .company-info{ font-size:11px; color:#333; }
    .company-right{ text-align:right; font-size:11px; color:#333; }

    /* ===== TITLE + META ===== */
    h1{ font-size:18px; font-weight:600; margin:0 0 8px; }
    .meta{ margin:0 0 12px; }
    .meta-row{ display:flex; }
    .meta b{ min-width:60px; display:inline-block; color:#222; }
    .dash{ padding:0 4px; color:#aaa; }
    .divider{ height:1px; background:var(--soft); margin:12px 0 14px; }

    /* ===== DATA ===== */
    .data{ width:100%; border-collapse:separate; border-spacing:0 6px; }
    .data .label{ width:160px; padding-right:12px; font-weight:600; vertical-align:top; }
    .data .value{ vertical-align:top; }

    /* ===== SIGNATURE ===== */
    .sign{ margin-top:36px; }
    .sign-date{ color:var(--muted); margin-bottom:24px; text-align:right; }
    .sign-grid{ width:100%; table-layout:fixed; }
    .sign-grid td{ width:50%; text-align:center; padding:0 40px; vertical-align:bottom; }
    .sign-title{ font-weight:500; margin-bottom:60px; }
    .sign-line{ width:70%; height:1px; background:var(--line); margin:0 auto 6px; opacity:.7; }
    .sign-name{ font-weight:600; text-decoration:underline; }
    .sign-role{ color:var(--muted); font-size:11px; }
  </style>
</head>
<body>
<div class="container">

  <!-- HEADER -->
  <table class="letterhead" role="presentation">
    <tr>
      <td style="width:90px">
        <img src="{{ public_path('assets/images/foto.png') }}" alt="Logo" class="logo">
      </td>
      <td>
        <div class="company-name">PT. Andalan <span>Artha Primanusa</span></div>
        <div class="company-info">
          Jl. Taman Pluit Kencana No 2 Blok N, Kav No 2<br>
          Pluit, Penjaringan, Jakarta 14440
        </div>
      </td>
      <td class="company-right">
        Tel: 021 66691319 <br>
        headoffice@pt-aap.com
      </td>
    </tr>
  </table>

  <!-- TITLE + META -->
  <h1>
    {{ $entry->form->title }}
    <span style="font-size:12px;font-weight:400;color:#666;">#{{ $entry->id }}</span>
  </h1>

  <div class="meta">
    <div class="meta-row"><b>User</b><span class="dash">:</span> {{ $entry->user->name }} ({{ $entry->user->email }})</div>
    <div class="meta-row"><b>Tanggal</b><span class="dash">:</span> {{ $entry->created_at->format('d/m/Y H:i') }}</div>
    <div class="meta-row"><b>Status</b><span class="dash">:</span> {{ strtoupper($entry->status) }}</div>
  </div>

  <div class="divider"></div>

  <!-- DATA -->
  <table class="data" role="presentation">
    <tbody>
    @foreach($entry->data as $k => $v)
      @php
        $label = ucfirst(str_replace('_',' ',$k));
        $val = is_array($v)
          ? implode(', ', array_map(fn($x) => is_scalar($x) ? $x : json_encode($x, JSON_UNESCAPED_UNICODE), $v))
          : (is_bool($v) ? ($v ? 'Ya' : 'Tidak') : (string)$v);
      @endphp
      <tr>
        <td class="label">{{ $label }}</td>
        <td class="value">{{ trim($val) !== '' ? $val : '—' }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>

  <!-- SIGNATURE -->
  @php
    $latestApproval = $entry->approvals()->with('actor')->latest()->first();
  @endphp

  <div class="sign">
    <div class="sign-date">Jakarta, {{ \Carbon\Carbon::now()->format('d M Y') }}</div>
    <table class="sign-grid" role="presentation">
      <tr>
        <td>
          <div class="sign-title">Diajukan Oleh,</div>
          <div class="sign-line"></div>
          <div class="sign-name">{{ $entry->user->name }}</div>
          <div class="sign-role">{{ $entry->user->email }}</div>
        </td>
        <td>
          <div class="sign-title">Disetujui Oleh,</div>
          <div class="sign-line"></div>
          <div class="sign-name">{{ data_get($latestApproval, 'actor.name', ' ') }}</div>
          <div class="sign-role">{{ strtoupper(data_get($latestApproval, 'action', 'Pemberi Persetujuan')) }}</div>
        </td>
      </tr>
    </table>
  </div>

</div>
</body>
</html>
