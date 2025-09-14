@extends('layouts.app')
@section('title','Rekap')

@section('content')
<h1 class="text-2xl font-bold mb-4 text-maroon-700">Rekap — {{ $period }}</h1>

{{-- Filter (scope: day/week/month/year) --}}
<form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-3"
      x-data="{ scope: '{{ $scope ?? 'month' }}' }">
  <input type="hidden" name="scope" :value="scope">

  <select name="site_id" class="border rounded-lg px-3 py-2 md:col-span-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
    <option value="">Semua Site</option>
    @foreach($sites as $s)
      <option value="{{ $s->id }}" @selected(($siteId ?? null)===$s->id)>{{ $s->code }} — {{ $s->name }}</option>
    @endforeach
  </select>

  <select x-model="scope" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
    <option value="day">Harian</option>
    <option value="week">Mingguan</option>
    <option value="month">Bulanan</option>
    <option value="year">Tahunan</option>
  </select>

  <input type="date" name="date" value="{{ $date ?? now()->toDateString() }}" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400" x-show="scope==='day'">
  <div class="flex gap-2" x-show="scope==='week'">
    <input type="number" name="week" value="{{ $week ?? now()->isoWeek }}" class="border rounded-lg px-3 py-2 w-24 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
    <input type="number" name="year" value="{{ $year ?? now()->year }}" class="border rounded-lg px-3 py-2 w-28 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
  </div>
  <div class="flex gap-2" x-show="scope==='month'">
    <select name="month" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
      @for($m=1;$m<=12;$m++)
        <option value="{{ $m }}" @selected(($month ?? now()->month)===$m)>{{ $m }}</option>
      @endfor
    </select>
    <input type="number" name="year" value="{{ $year ?? now()->year }}" class="border rounded-lg px-3 py-2 w-28 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
  </div>
  <input type="number" name="year" value="{{ $year ?? now()->year }}" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400" x-show="scope==='year'">

  <button class="px-4 py-2 bg-maroon-600 hover:bg-maroon-700 text-white rounded-lg md:col-span-1">Terapkan</button>
</form>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">Total Groups</div>
    <div class="text-xl font-bold">{{ $groups->count() }}</div>
  </div>
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">Indicators</div>
    <div class="text-xl font-bold">{{ collect($groups)->flatMap(fn($g)=>$g->indicators)->count() }}</div>
  </div>
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">Scope</div>
    <div class="text-xl font-bold uppercase">{{ $scope }}</div>
  </div>
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">Periode</div>
    <div class="text-sm font-semibold">{{ $period }}</div>
  </div>
</div>

{{-- Charts: Top + Tren --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900 lg:col-span-2">
    <div class="font-semibold mb-2">Trend</div>
    <canvas id="trendChart" class="w-full !h-72"></canvas>
  </div>
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="font-semibold mb-2">Top Indicators</div>
    <canvas id="topChart" class="w-full !h-72"></canvas>
  </div>
</div>

{{-- Per Group Charts --}}
@if(!empty($charts))
  <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach($charts as $gCode => $chart)
      <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
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
  <div class="mb-6 border rounded-lg bg-white dark:bg-coal-900 overflow-hidden">
    <div class="px-3 py-2 font-semibold bg-maroon-700 text-white">{{ $g->name }}</div>
    <table class="min-w-full">
      <thead class="bg-maroon-700 text-white">
        <tr>
          <th class="px-3 py-2 w-10 text-left">#</th>
          <th class="px-3 py-2 text-left">Indicator</th>
          <th class="px-3 py-2 text-right w-48">Total</th>
          <th class="px-3 py-2 w-24 text-left">Unit</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach(($data[$g->code] ?? []) as $row)
          <tr class="hover:bg-gray-50 dark:hover:bg-coal-800 transition">
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
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  const payload = @json($charts ? (object) $charts : (object) []);

  // THEME & UTIL
  const isDark  = document.documentElement.classList.contains('dark');
  const gridCol = isDark ? 'rgba(255,255,255,.12)' : 'rgba(0,0,0,.08)';
  const textCol = isDark ? '#e5e7eb' : '#374151';

  Chart.defaults.color = textCol;
  Chart.defaults.font.family = "Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Arial";
  Chart.defaults.borderColor = gridCol;

  // MAROON PALETTE
  const PALETTE = [
    '#ba202e', // 500
    '#991a25', // 600
    '#7b1e2b', // 700
    '#611823', // 800
    '#320a0f', // 900
    '#d6737b', // 400
    '#e7a8ad', // 300
    '#fae9ea', // 100
  ];
  const rgba = (hex,a=1)=>{const n=parseInt(hex.replace('#',''),16);return `rgba(${(n>>16)&255}, ${(n>>8)&255}, ${n&255}, ${a})`};
  const colorsFor = n => Array.from({length:n},(_,i)=>PALETTE[i%PALETTE.length]);

  const baseOptions = (o={})=>({
    responsive:true, maintainAspectRatio:false,
    animation:{ duration:800, easing:'easeOutQuart' },
    scales:{ x:{ grid:{ color:gridCol } }, y:{ grid:{ color:gridCol } } },
    plugins:{
      legend:{ display:false },
      tooltip:{
        backgroundColor:isDark?'rgba(17,24,39,.95)':'rgba(255,255,255,.95)',
        titleColor:textCol, bodyColor:textCol, borderColor:gridCol, borderWidth:1
      }
    }, ...o
  });

  // Label nilai sederhana
  const DataLabelPlugin={ id:'valueLabels', afterDatasetsDraw(chart){
    const {ctx}=chart;
    chart.data.datasets.forEach((ds,di)=>{
      const meta=chart.getDatasetMeta(di); if(!meta||meta.hidden) return;
      meta.data.forEach((el,i)=>{
        const v=ds.data[i]; if(v==null) return;
        ctx.save(); ctx.font='600 11px '+Chart.defaults.font.family; ctx.fillStyle=textCol;
        let x=el.x,y=el.y; ctx.textAlign='center'; ctx.textBaseline='bottom';
        if(chart.config.type==='bar' && chart.config.options.indexAxis==='y'){ ctx.textAlign='left'; ctx.textBaseline='middle'; x=el.x+8; y=el.y; } else { y=el.y-6; }
        ctx.fillText(v,x,y); ctx.restore();
      });
    });
  }};
  Chart.register(DataLabelPlugin);

  // TOP CHART
  const allRows = Object.values(payload).flatMap(g => (g.labels||[]).map((label,i)=>({label, val:(g.values||[])[i] ?? 0})));
  const top = [...allRows].sort((a,b)=>b.val-a.val).slice(0,10);
  if (document.getElementById('topChart') && top.length){
    const cols=colorsFor(top.length);
    new Chart(document.getElementById('topChart'),{
      type:'bar',
      data:{ labels: top.map(r=>r.label), datasets:[{ data: top.map(r=>r.val), backgroundColor: cols.map(c=>rgba(c,.85)), borderColor: cols, borderWidth:1, borderRadius:10, maxBarThickness:36 }] },
      options: baseOptions({ scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true } } })
    });
  }

  // TREND CHART (maroon line + gradient)
  if (document.getElementById('trendChart')){
    const ctx=document.getElementById('trendChart').getContext('2d');
    const line='#7b1e2b'; // maroon-700
    const grad=ctx.createLinearGradient(0,0,0,ctx.canvas.height);
    grad.addColorStop(0, rgba(line,.35)); grad.addColorStop(1, rgba(line,0));

    const labels=Array.from({length:12},(_,i)=>`M${i+1}`);
    const values=labels.map(()=>Math.round(Math.random()*100));

    new Chart(ctx,{
      type:'line',
      data:{ labels, datasets:[{ data:values, borderColor:line, backgroundColor:grad, fill:true, tension:.35, pointRadius:3, pointHoverRadius:5, borderWidth:2 }] },
      options: baseOptions({ scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true } } })
    });
  }

  // PER GROUP (horizontal bars)
  Object.entries(payload).forEach(([code,cfg])=>{
    const el=document.getElementById('chart_'+code); if(!el) return;
    const n=(cfg.labels||[]).length; const cols=colorsFor(n);
    new Chart(el,{
      type:'bar',
      data:{ labels:cfg.labels||[], datasets:[{ data:cfg.values||[], backgroundColor:cols.map(c=>rgba(c,.85)), borderColor:cols, borderWidth:1, borderRadius:10, barPercentage:.8, categoryPercentage:.9 }] },
      options: baseOptions({ indexAxis:'y', scales:{ x:{ beginAtZero:true }, y:{ grid:{ display:false } } } })
    });
  });
})();
</script>
@endpush
