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

  {{-- DAY --}}
  <input
    type="date" name="date" value="{{ $date ?? now()->toDateString() }}"
    class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
    x-show="scope==='day'" :disabled="scope!=='day'">

  {{-- WEEK --}}
  <div class="flex gap-2" x-show="scope==='week'">
    <input type="number" name="week" value="{{ $week ?? now()->isoWeek }}"
           class="border rounded-lg px-3 py-2 w-24 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
           :disabled="scope!=='week'">
    <input type="number" name="year" value="{{ $year ?? now()->year }}"
           class="border rounded-lg px-3 py-2 w-28 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
           :disabled="scope!=='week'">
  </div>

  {{-- MONTH --}}
  <div class="flex gap-2" x-show="scope==='month'">
    <select name="month"
            class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
            :disabled="scope!=='month'">
      @for($m=1;$m<=12;$m++)
        <option value="{{ $m }}" @selected(($month ?? now()->month)===$m)>{{ $m }}</option>
      @endfor
    </select>
    <input type="number" name="year" value={{ $year ?? now()->year }}
           class="border rounded-lg px-3 py-2 w-28 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
           :disabled="scope!=='month'">
  </div>

  {{-- YEAR --}}
  <input type="number" name="year" value="{{ $year ?? now()->year }}"
         class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
         x-show="scope==='year'" :disabled="scope!=='year'">

  <button class="px-4 py-2 bg-maroon-600 hover:bg-maroon-700 text-white rounded-lg md:col-span-1">Terapkan</button>
</form>
{{-- Stat Cards --}}
<div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
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

  {{-- 👇 Tambahan: Total On-time --}}
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">On-time Total</div>
    <div class="text-xl font-bold text-emerald-600">
      {{ number_format($totalOntime ?? 0, 0, ',', '.') }}
    </div>
  </div>

  {{-- 👇 Tambahan: Total Late --}}
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">Late Total</div>
    <div class="text-xl font-bold text-rose-600">
      {{ number_format($totalLate ?? 0, 0, ',', '.') }}
    </div>
  </div>
</div>


{{-- Charts: Top + Trend --}}
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
          <th class="px-3 py-2 text-right w-36">On-time</th>
          <th class="px-3 py-2 text-right w-36">Late</th>
          <th class="px-3 py-2 text-right w-40">Total</th>
          <th class="px-3 py-2 w-24 text-left">Unit</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach(($data[$g->code] ?? []) as $row)
          @php
            $ind = $row['indicator'];
            $fmt = $ind->data_type === 'int' ? 0 : 2;
          @endphp
          <tr class="hover:bg-gray-50 dark:hover:bg-coal-800 transition">
            <td class="px-3 py-2">{{ $ind->order_index }}</td>
            <td class="px-3 py-2">
              <div class="font-medium">{{ $ind->name }}</div>
              @if($ind->is_derived)
                <div class="text-xs text-gray-500 font-mono">= {{ $ind->formula }}</div>
              @endif
            </td>

            {{-- On-time --}}
            <td class="px-3 py-2 text-right font-semibold text-emerald-600 dark:text-emerald-400">
              {{ number_format($row['on_time'] ?? 0, $fmt, ',', '.') }}
            </td>

            {{-- Late --}}
            <td class="px-3 py-2 text-right font-semibold text-rose-600 dark:text-rose-400">
              {{ number_format($row['late'] ?? 0, $fmt, ',', '.') }}
            </td>

            {{-- Total --}}
            <td class="px-3 py-2 text-right font-bold">
              {{ number_format($row['total'] ?? 0, $fmt, ',', '.') }}
            </td>

            <td class="px-3 py-2">{{ $ind->unit ?? '-' }}</td>
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
  // ====== DATA DARI SERVER ======
  const payload      = @json($charts ? (object) $charts : (object) []);
  const trendLabels  = @json($trendLabels ?? []);
  const trendValues  = @json($trendValues ?? []);
  const datasetLabel = @json($trendLabel ?? 'Trend');

  // ====== THEME & UTIL (tanpa hardcode warna) ======
  const isDark  = document.documentElement.classList.contains('dark');
  const gridCol = isDark ? 'rgba(255,255,255,.12)' : 'rgba(0,0,0,.08)';
  const textCol = isDark ? '#e5e7eb' : '#1f2937';

  Chart.defaults.color = textCol;
  Chart.defaults.font.family = "Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Arial";
  Chart.defaults.borderColor = gridCol;

  // Generator warna algoritmis (golden-angle)
  const hueAt = (i)=> (i*137.508) % 360;
  const hsl = (h,s,l,a=1)=>`hsl(${h} ${s}% ${l}% / ${a})`;
  const dynColor = (i,a=0.95) => {
    const h = hueAt(i);
    const s = isDark ? 60 : 65;
    const l = isDark ? 55 : 45;
    return hsl(h, s, l, a);
  };

  // Soft glow / shadow ringan
  const Glow = {
    id:'softGlow',
    beforeDraw(c){ const x=c.ctx; x.save(); x.shadowColor=isDark?'rgba(0,0,0,.35)':'rgba(0,0,0,.15)'; x.shadowBlur=10; x.shadowOffsetY=4; },
    afterDraw(c){ c.ctx.restore(); }
  };
  Chart.register(Glow);

  // Formatter angka ID
  const nf0 = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
  const nf2 = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const baseOptions = (o={})=>({
    responsive:true, maintainAspectRatio:false,
    animation:{ duration:700, easing:'easeOutQuart' },
    scales:{
      x:{ grid:{ color:gridCol } },
      y:{ grid:{ color:gridCol }, ticks:{ callback:(v)=> nf0.format(v) } }
    },
    plugins:{
      legend:{ display:false },
      tooltip:{
        backgroundColor:isDark?'rgba(17,24,39,.95)':'rgba(255,255,255,.95)',
        titleColor:textCol, bodyColor:textCol, borderColor:gridCol, borderWidth:1,
        callbacks:{
          label(ctx){
            const ds   = ctx.dataset;
            const raw  = ctx.raw ?? 0;
            // unit bisa function (per bar) atau string (global dataset)
            const unit = (typeof ds.unit === 'function') ? ds.unit(ctx.dataIndex) : (ds.unit || '');
            const isInt = ds.allInt === true;
            const val = isInt ? nf0.format(raw) : nf2.format(raw);
            return unit ? `${val} ${unit}` : val;
          }
        }
      }
    },
    ...o
  });

  // Datalabel minimalis (tanpa plugin eksternal)
  const DataLabel = {
    id:'valueLabels',
    afterDatasetsDraw(chart){
      const {ctx, data} = chart;
      data.datasets.forEach((ds, di)=>{
        const meta = chart.getDatasetMeta(di);
        if (!meta || meta.hidden) return;
        meta.data.forEach((el, i)=>{
          const v = ds.data[i]; if (v == null) return;
          const unit = (typeof ds.unit === 'function') ? ds.unit(i) : (ds.unit || '');
          const label = (ds.allInt===true ? nf0.format(v) : nf2.format(v)) + (unit ? (' '+unit) : '');
          ctx.save();
          ctx.font = '600 11px ' + Chart.defaults.font.family;
          ctx.fillStyle = textCol;
          const horiz = chart.config.type==='bar' && chart.config.options.indexAxis==='y';
          ctx.textAlign = horiz ? 'left' : 'center';
          ctx.textBaseline = horiz ? 'middle' : 'bottom';
          let x = el.x, y = el.y;
          if (horiz) x = el.x + 8; else y = el.y - 6;
          ctx.fillText(label, x, y);
          ctx.restore();
        });
      });
    }
  };
  Chart.register(DataLabel);

  // ====== TOP INDICATORS (tanpa campur unit)
  const flat = Object.values(payload).flatMap(g => {
    const units = g.units || [];
    return (g.labels || []).map((label,i)=>({
      label, val: (g.values||[])[i] ?? 0, unit: units[i] || ''
    }));
  });
  // unit dominan supaya apple-to-apple
  const unitCount = flat.reduce((m,r)=>((m[r.unit]=(m[r.unit]||0)+1),m),{});
  const dominantUnit = Object.entries(unitCount).sort((a,b)=>b[1]-a[1])[0]?.[0] ?? '';
  const top = flat.filter(r=>r.unit===dominantUnit).sort((a,b)=>b.val-a.val).slice(0,10);

  if (document.getElementById('topChart') && top.length){
    new Chart(document.getElementById('topChart'),{
      type:'bar',
      data:{
        labels: top.map(r=>r.label),
        datasets:[{
          data: top.map(r=>r.val),
          backgroundColor: top.map((_,i)=>dynColor(i,.9)),
          borderColor: top.map((_,i)=>dynColor(i,1)),
          borderWidth:1, borderRadius:12, maxBarThickness:36,
          allInt: true, unit: dominantUnit || ''
        }]
      },
      options: baseOptions({
        scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true } }
      })
    });
  }

  // ====== TREND (pakai data controller)
  if (document.getElementById('trendChart') && trendLabels.length){
    const ctx = document.getElementById('trendChart').getContext('2d');
    const lineColor = dynColor(0, 1);
    const grad = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
    grad.addColorStop(0, dynColor(0, .28));
    grad.addColorStop(1, 'hsla(0 0% 0% / 0)');

    const firstNonNull = trendValues.find(v => v != null) ?? 0;
    const allInt = Number.isInteger(firstNonNull);

    new Chart(ctx,{
      type:'line',
      data:{ labels: trendLabels, datasets:[{
        label: datasetLabel,
        data: trendValues,
        borderColor: lineColor,
        backgroundColor: grad,
        fill:true, tension:.35, pointRadius:3.5, pointHoverRadius:6, borderWidth:2.2,
        allInt: allInt
      }]},
      options: baseOptions({
        scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true } }
      })
    });
  }

  // ====== PER-GROUP (horizontal bar, unit per bar)
  Object.entries(payload).forEach(([code,cfg])=>{
    const el = document.getElementById('chart_'+code); if(!el) return;
    const n = (cfg.labels||[]).length;
    const units = cfg.units || [];
    const allInt = cfg.all_int === true;

    new Chart(el,{
      type:'bar',
      data:{
        labels: cfg.labels || [],
        datasets:[{
          data: cfg.values || [],
          backgroundColor: Array.from({length:n}, (_,i)=>dynColor(i,.92)),
          borderColor:    Array.from({length:n}, (_,i)=>dynColor(i,1)),
          borderWidth:1, borderRadius:12, barPercentage:.8, categoryPercentage:.9,
          allInt: allInt,
          unit: (idx)=> units[idx] || ''
        }]
      },
      options: baseOptions({
        indexAxis:'y',
        scales:{ x:{ beginAtZero:true }, y:{ grid:{ display:false } } }
      })
    });
  });
})();
</script>
@endpush
