<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Entry #{{ $entry->id }} — Data</title>
  <style>
    * { font-family: DejaVu Sans, Arial, sans-serif; }
    body { font-size: 12px; color: #111; }
    h1 { margin: 0 0 10px; font-size: 18px; }
    .meta { margin-bottom: 12px; color: #555; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
    th { background: #f5f5f5; text-align: left; width: 30%; }
  </style>
</head>
<body>
  <h1>Entry #{{ $entry->id }} — Data</h1>
  <div class="meta">
    <div><strong>Form:</strong> {{ $entry->form->title }}</div>
    <div><strong>User:</strong> {{ $entry->user->name }} ({{ $entry->user->email }})</div>
    <div><strong>Tanggal:</strong> {{ $entry->created_at->format('d/m/Y H:i') }}</div>
    <div><strong>Status:</strong> {{ strtoupper($entry->status) }}</div>
  </div>

  <table>
    <tbody>
    @foreach($entry->data as $k => $v)
      <tr>
        <th>{{ ucfirst(str_replace('_',' ',$k)) }}</th>
        <td>
          @php
            $val = is_array($v) ? implode(', ', array_map(fn($x) => is_scalar($x) ? $x : json_encode($x, JSON_UNESCAPED_UNICODE), $v)) : $v;
          @endphp
          {{ $val }}
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</body>
</html>
