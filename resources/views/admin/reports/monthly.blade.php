@extends('layouts.app')
@section('title','Rekap Bulanan')

@section('content')
<h1 class="text-2xl font-bold mb-4">Rekap Bulanan — {{ $period }}</h1>

{{-- Filter Bulanan --}}
<form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
  <select name="site_id" class="border rounded px-3 py-2">
    <option value="">Semua Site</option>
    @foreach($sites as $s)
      <option value="{{ $s->id }}" @selected($siteId==$s->id)>{{ $s->code }} — {{ $s->name }}</option>
    @endforeach
  </select>
  <select name="month" class="border rounded px-3 py-2">
    @for($m=1;$m<=12;$m++) 
      <option value="{{ $m }}" @selected($m==$month)>{{ $m }}</option> 
    @endfor
  </select>
  <input type="number" name="year" value="{{ $year }}" class="border rounded px-3 py-2">
  <button class="px-4 py-2 bg-gray-800 text-white rounded">Terapkan</button>
</form>

{{-- Grafik per group --}}
@if(!empty($charts))
  <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach($charts as $gCode => $chart)
      <div class="bg-white dark:bg-coal-900 border rounded p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="font-semibold">{{ $chart['group_name'] }}</div>
          <div class="text-xs text-gray-500">Total indikator: {{ count($chart['labels']) }}</div>
        </div>
        <canvas id="chart_{{ $gCode }}" class="w-full !h-56 md:!h-64 lg:!h-72"></canvas>
      </div>
    @endforeach
  </div>
@endif

{{-- Tabel data --}}
@foreach($groups as $g)
  <div class="mb-6">
    <div class="px-3 py-2 font-semibold bg-gray-100 rounded-t">{{ $g->name }}</div>
    <div class="bg-white border rounded-b">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left w-10">#</th>
            <th class="px-3 py-2 text-left">Indicator</th>
            <th class="px-3 py-2 text-right w-48">Total Bulan Ini</th>
            <th class="px-3 py-2 text-left w-24">Unit</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @foreach($data[$g->code] ?? [] as $row)
            <tr>
              <td class="px-3 py-2">{{ $row['indicator']->order_index }}</td>
              <td class="px-3 py-2">
                <div class="font-medium">{{ $row['indicator']->name }}</div>
                @if($row['indicator']->is_derived)
                  <div class="text-xs text-gray-500 font-mono">= {{ $row['indicator']->formula }}</div>
                @endif
              </td>
              <td class="px-3 py-2 text-right font-semibold">
                {{ number_format($row['value'], $row['indicator']->data_type==='int' ? 0 : 2) }}
              </td>
              <td class="px-3 py-2">{{ $row['indicator']->unit ?? '-' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const charts = @json($charts);
  Object.entries(charts).forEach(([gCode, cfg])=>{
    const el = document.getElementById('chart_'+gCode);
    if (!el) return;
    new Chart(el,{
      type:'bar',
      data:{ labels:cfg.labels, datasets:[{ data:cfg.values, backgroundColor:'rgba(123,28,28,.4)', borderColor:'rgba(123,28,28,1)' }]},
      options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}} }
    });
  });
})();
</script>
@endpush
