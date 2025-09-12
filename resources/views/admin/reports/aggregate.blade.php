@extends('layouts.app')
@section('title','Rekap')

@section('content')
<h1 class="text-2xl font-bold mb-4">Rekap — {{ $period }}</h1>

{{-- Filter (scope: day/week/month/year) --}}
<form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-3"
      x-data="{ scope: '{{ $scope ?? 'month' }}' }">
  <input type="hidden" name="scope" :value="scope">

  <select name="site_id" class="border rounded px-3 py-2 md:col-span-2">
    <option value="">Semua Site</option>
    @foreach($sites as $s)
      <option value="{{ $s->id }}" @selected(($siteId ?? null)==$s->id)>{{ $s->code }} — {{ $s->name }}</option>
    @endforeach
  </select>

  <select x-model="scope" class="border rounded px-3 py-2">
    <option value="day">Harian</option>
    <option value="week">Mingguan</option>
    <option value="month">Bulanan</option>
    <option value="year">Tahunan</option>
  </select>

  <input type="date" name="date" value="{{ $date ?? now()->toDateString() }}" class="border rounded px-3 py-2" x-show="scope==='day'">
  <div class="flex gap-2" x-show="scope==='week'">
    <input type="number" name="week" value="{{ $week ?? now()->isoWeek }}" class="border rounded px-3 py-2 w-24">
    <input type="number" name="year" value="{{ $year ?? now()->year }}" class="border rounded px-3 py-2 w-28">
  </div>
  <div class="flex gap-2" x-show="scope==='month'">
    <select name="month" class="border rounded px-3 py-2">
      @for($m=1;$m<=12;$m++) <option value="{{ $m }}" @selected(($month ?? now()->month)==$m)>{{ $m }}</option> @endfor
    </select>
    <input type="number" name="year" value="{{ $year ?? now()->year }}" class="border rounded px-3 py-2 w-28">
  </div>
  <input type="number" name="year" value="{{ $year ?? now()->year }}" class="border rounded px-3 py-2" x-show="scope==='year'">

  <button class="px-4 py-2 bg-gray-800 text-white rounded md:col-span-1">Terapkan</button>
</form>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
  <div class="p-4 border rounded bg-white dark:bg-coal-900"><div class="text-xs">Total Groups</div><div class="text-xl font-bold">{{ $groups->count() }}</div></div>
  <div class="p-4 border rounded bg-white dark:bg-coal-900"><div class="text-xs">Indicators</div><div class="text-xl font-bold">{{ collect($groups)->flatMap(fn($g)=>$g->indicators)->count() }}</div></div>
  <div class="p-4 border rounded bg-white dark:bg-coal-900"><div class="text-xs">Scope</div><div class="text-xl font-bold uppercase">{{ $scope }}</div></div>
  <div class="p-4 border rounded bg-white dark:bg-coal-900"><div class="text-xs">Periode</div><div class="text-sm font-semibold">{{ $period }}</div></div>
</div>

{{-- Charts: Top + Tren --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
  <div class="p-4 border rounded bg-white dark:bg-coal-900 lg:col-span-2">
    <div class="font-semibold mb-2">Trend (placeholder)</div>
    <canvas id="trendChart" class="w-full !h-72"></canvas>
  </div>
  <div class="p-4 border rounded bg-white dark:bg-coal-900">
    <div class="font-semibold mb-2">Top Indicators</div>
    <canvas id="topChart" class="w-full !h-72"></canvas>
  </div>
</div>

{{-- Per Group Charts --}}
@if(!empty($charts))
  <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach($charts as $gCode => $chart)
      <div class="p-4 border rounded bg-white dark:bg-coal-900">
        <div class="flex items-center justify-between mb-2">
          <div class="font-semibold">{{ $chart['group_name'] }}</div>
          <div class="text-xs text-gray-500">Total indikator: {{ count($chart['labels']) }}</div>
        </div>
        <canvas id="chart_{{ $gCode }}" class="w-full !h-56 md:!h-64 lg:!h-72"></canvas>
      </div>
    @endforeach
  </div>
@endif

{{-- Table --}}
@foreach($groups as $g)
  <div class="mb-6 border rounded bg-white dark:bg-coal-900">
    <div class="px-3 py-2 font-semibold bg-gray-100 dark:bg-coal-800">{{ $g->name }}</div>
    <table class="min-w-full">
      <thead class="bg-gray-50 dark:bg-coal-900">
        <tr>
          <th class="px-3 py-2 w-10">#</th>
          <th class="px-3 py-2">Indicator</th>
          <th class="px-3 py-2 text-right w-48">Total</th>
          <th class="px-3 py-2 w-24">Unit</th>
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
            <td class="px-3 py-2 text-right font-semibold">{{ number_format($row['value'], $row['indicator']->data_type==='int'?0:2) }}</td>
            <td class="px-3 py-2">{{ $row['indicator']->unit ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const payload = @json($charts);

  // Top chart
  const allRows = Object.values(payload).flatMap(g => g.labels.map((label,i)=>({label,val:g.values[i]})));
  const top = [...allRows].sort((a,b)=>b.val-a.val).slice(0,10);
  if (document.getElementById('topChart') && top.length){
    new Chart(document.getElementById('topChart'),{
      type:'bar',
      data:{ labels:top.map(r=>r.label), datasets:[{ data:top.map(r=>r.val), backgroundColor:'rgba(123,28,28,.5)' }]},
      options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}}
    });
  }

  // Trend chart (dummy data)
  if (document.getElementById('trendChart')){
    new Chart(document.getElementById('trendChart'),{
      type:'line',
      data:{ labels:Array.from({length:30},(_,i)=>i+1), datasets:[{ data:Array(30).fill(0), borderColor:'rgba(123,28,28,1)', fill:true, tension:.35 }]},
      options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}}
    });
  }

  // Per group
  Object.entries(payload).forEach(([code,cfg])=>{
    const el=document.getElementById('chart_'+code);
    if (!el) return;
    new Chart(el,{type:'bar', data:{labels:cfg.labels,datasets:[{data:cfg.values,backgroundColor:'rgba(31,41,55,.5)'}]}, options:{indexAxis:'y',responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}}});
  });
})();
</script>
@endpush
