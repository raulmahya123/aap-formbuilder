<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; font-size: 12px; }
    h1 { font-size: 18px; margin: 0 0 8px; }
    table { width:100%; border-collapse: collapse; margin-top: 12px; }
    td, th { border:1px solid #ddd; padding:8px; }
  </style>
</head>
<body>
  <h1>{{ $form->title }} — Bukti Isian</h1>
  <div>Nomor entri: #{{ $entry->id }} | Tanggal: {{ $entry->created_at->format('d/m/Y H:i') }}</div>

  <table>
    <tbody>
      @foreach($data as $k=>$v)
        <tr>
          <th style="width:35%">{{ ucfirst(str_replace('_',' ',$k)) }}</th>
          <td>{{ is_array($v) ? json_encode($v) : $v }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
