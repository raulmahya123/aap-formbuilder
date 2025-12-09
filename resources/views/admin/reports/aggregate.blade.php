{{-- resources/views/admin/reports/aggregate.blade.php --}}
@extends('layouts.app')
@section('title','Rekap')

@push('styles')
<style>
  /* ===== Grid & kartu ringkas ===== */
  .cards-grid{display:grid;gap:.75rem}
  @media (min-width:768px){.cards-grid{grid-template-columns:repeat(6,minmax(0,1fr))}}
  .stat-card{padding:1rem;border:1px solid #e5e7eb;border-radius:.75rem;background:#fff}
  .chart-wrap{border:1px solid #e5e7eb;border-radius:.75rem;background:#fff;overflow:hidden}

  /* ===== Tinggi chart ===== */
  .chart-card-bar{height:360px}
  .chart-card-mini{height:170px}
  .chart-card-donut{height:auto}
  .chart-card-bar canvas,
  .chart-card-mini canvas{width:100%!important;height:100%!important;display:block}

  .chart-card-donut .chart-canvas{height:200px}
  .chart-card-donut .chart-canvas canvas{width:100%!important;height:100%!important;display:block}

  .chart-wrap.chart-card-mini,
  .chart-wrap.chart-card_donut{overflow:visible}

  .chart-card-mini .chart-head,
  .chart-card-donut .chart-head{display:flex;align-items:center;gap:.375rem;margin-bottom:.25rem;line-height:1.1}
  .chart-card-mini .chart-title,
  .chart-card-donut .chart-title{
    font-weight:600;font-size:11px;color:#111827;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden
  }

  .dark .stat-card,.dark .chart-wrap{background:#0f141a;border-color:#263241}
</style>
@endpush

@push('scripts')
@once
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endonce
@endpush

@section('content')
@php
  use Illuminate\Support\Carbon;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Log;

  // ===== cek khusus super admin =====
  $u = Auth::user();
  $isSuperAdmin = $u && (
      (method_exists($u, 'hasRole') && $u->hasRole('super_admin')) ||
      (($u->role ?? $u->role_key ?? null) === 'super_admin')
  );

  // ====== SELALU gunakan Asia/Jakarta untuk semua perhitungan tanggal di view ======
  $tz = 'Asia/Jakarta';

  // Normalisasi nilai default agar tidak pernah ikut UTC
  $scopeNow = $scope ?? 'month';
  $today    = now($tz);
  $dateObj  = isset($date) && $date ? Carbon::parse($date, $tz) : $today->copy();
  $yearVal  = isset($year) && $year ? (int)$year : (int)$today->year;
  $weekVal  = isset($week) && $week ? (int)$week : (int)$today->isoWeek;
  $monthVal = isset($month) && $month ? (int)$month : (int)$today->month;

  // pastikan $period sudah siap dari controller; kalau tidak, fallback yang aman
  $periodSafe = $period ?? match($scopeNow){
      'day'   => $dateObj->toDateString(),
      'week'  => "Minggu {$weekVal}, {$yearVal}",
      'year'  => (string)$yearVal,
      default => sprintf('%02d/%d',$monthVal,$yearVal)
  };

  // ============= LOG KONTEKS REKAP =============
  Log::info('AGG VIEW: load aggregate page', [
      'user_id'   => optional($u)->id,
      'scope'     => $scopeNow,
      'period'    => $periodSafe,
      'site_id'   => $siteId ?? null,
      'date'      => request('date'),
      'week'      => request('week'),
      'month'     => request('month'),
      'year'      => request('year'),
      'groups'    => isset($groups) ? $groups->count() : null,
  ]);
@endphp

<h1 class="text-2xl font-bold mb-4 text-maroon-700">Rekap — {{ $periodSafe }}</h1>

{{-- ========================= Filter ========================= --}}
<form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-3"
  x-data="{ scope: '{{ $scopeNow }}' }">
  <input type="hidden" name="scope" :value="scope">
  {{-- kirimkan timezone agar controller bisa ikutkan --}}
  <input type="hidden" name="tz" value="{{ $tz }}">

  <select name="site_id" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
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
  <input type="date" name="date" value="{{ $dateObj->setTimezone($tz)->toDateString() }}"
         class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
         x-show="scope==='day'" :disabled="scope!=='day'">

  {{-- WEEK --}}
  <div class="flex gap-2" x-show="scope==='week'">
    <input type="number" name="week" value="{{ $weekVal }}"
           class="border rounded-lg px-3 py-2 w-24 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
           :disabled="scope!=='week'">
    <input type="number" name="year" value="{{ $yearVal }}"
           class="border rounded-lg px-3 py-2 w-28 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
           :disabled="scope!=='week'">
  </div>

  {{-- MONTH --}}
  <div class="flex gap-2" x-show="scope==='month'">
    <select name="month" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
            :disabled="scope!=='month'">
      @for($m=1;$m<=12;$m++)
        <option value="{{ $m }}" @selected($monthVal===$m)>{{ $m }}</option>
      @endfor
    </select>
    <input type="number" name="year" value="{{ $yearVal }}"
           class="border rounded-lg px-3 py-2 w-28 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
           :disabled="scope!=='month'">
  </div>

  {{-- YEAR --}}
  <input type="number" name="year" value="{{ $yearVal }}"
         class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
         x-show="scope==='year'" :disabled="scope!=='year'">

  <button class="px-4 py-2 bg-maroon-600 hover:bg-maroon-700 text-white rounded-lg md:col-span-1">Terapkan</button>
</form>

{{-- ========================= Stat Cards ========================= --}}
@php
  $grandTotal = 0;
  foreach ($groups as $g) {
    foreach (($data[$g->code] ?? []) as $r) {
      $on  = (float)($r['on_time'] ?? 0);
      $lt  = (float)($r['late'] ?? 0);
      $ttl = (float)($r['total'] ?? ($on + $lt));
      $grandTotal += $ttl;
    }
  }
@endphp
<div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
  <div class="stat-card"><div class="text-xs">Total Groups</div><div class="text-xl font-bold">{{ $groups->count() }}</div></div>
  <div class="stat-card"><div class="text-xs">Indicators</div><div class="text-xl font-bold">{{ collect($groups)->flatMap(fn($g)=>$g->indicators)->count() }}</div></div>
  <div class="stat-card"><div class="text-xs">Scope</div><div class="text-xl font-bold uppercase">{{ $scopeNow }}</div></div>
  <div class="stat-card"><div class="text-xs">Periode</div><div class="text-sm font-semibold">{{ $periodSafe }}</div></div>
  <div class="stat-card"><div class="text-xs">Grand Total</div><div class="text-xl font-bold">{{ number_format($grandTotal, 0, ',', '.') }}</div></div>
  <div class="stat-card"><div class="text-xs">Site</div><div class="text-sm font-semibold">{{ optional($sites->firstWhere('id',$siteId))->code ?? 'Semua' }}</div></div>
</div>

@php
/* ===== Helpers ===== */
$toFloat = function($raw){
  if ($raw === null) return 0.0;
  $s = trim((string)$raw);
  if ($s === '' || $s === '-') return 0.0;
  $s = preg_replace('/[^0-9,.\-]/', '', $s);
  $s = preg_replace('/(?<=\d)[,.](?=\d{3}(\D|$))/', '', $s);
  if (str_contains($s, ',') && !str_contains($s, '.')) $s = str_replace(',', '.', $s);
  if (substr_count($s, '.')>1){ $p=strrpos($s,'.'); $s=str_replace('.','',substr($s,0,$p)).substr($s,$p); }
  return is_numeric($s) ? (float)$s : 0.0;
};

$isLaggingInd = fn($ind)=>
  (bool)($ind->is_lagging ?? false) ||
  strtolower((string)($ind->type ?? $ind->category ?? ''))==='lagging' ||
  str_contains(strtolower((string)($ind->slug ?? $ind->code ?? '')),'lag');
$isLaggingGroup = fn($g)=> str_contains(strtolower((string)($g->name ?? '').' '.(string)($g->code ?? '')), 'lag');
$isBase = fn($ind)=>
  str_contains(strtolower((string)($ind->name ?? '')), 'deskripsi') ||
  str_contains(strtolower((string)($ind->name ?? '')), 'base') ||
  in_array(strtolower((string)($ind->type ?? $ind->category ?? '')), ['base','description','deskripsi']);
$makeThresholdLabel = function($thrRaw, $thrFloat) {
  if ($thrRaw === null || trim((string)$thrRaw) === '') return '0';
  if (is_string($thrRaw) && preg_match('/[%$]|(?:\bRp\b)|(?:\bIDR\b)|[A-Za-z]/', $thrRaw)) return trim($thrRaw);
  return fmod((float)$thrFloat,1.0)==0.0
    ? number_format((float)$thrFloat,0,',','.')
    : number_format((float)$thrFloat,2,',','.');
};

/** ====== Label default per scope ====== */
$makeScopeLabels = function($scope, $month = null) use ($tz) {
  $m = (int)($month ?: now($tz)->month);
  switch ($scope) {
    case 'year':  return range(1,12);
    case 'month': return [$m];
    case 'week':  return [1,2,3,4,5];
    default:      return ['Total'];
  }
};
$buckets = $buckets ?? null;
$series  = $series  ?? null;
@endphp

{{-- ========================= BAGIAN 1 — LAGGING (mini bar) ========================= --}}
@php $printedLagHeader=false; @endphp
@foreach($groups as $g)
  @php
    $rowsAll = collect($data[$g->code] ?? []);
    $rows    = $isLaggingGroup($g) ? $rowsAll : $rowsAll->filter(fn($r)=>$isLaggingInd($r['indicator']));
  @endphp

  @if($rows->isNotEmpty())
    @if(!$printedLagHeader)
      <div class="mb-2 text-sm font-semibold text-maroon-700">Lagging Indicators</div>
      @php $printedLagHeader=true; @endphp
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3 mb-6">
      @foreach($rows as $r)
        @php
          $ind     = $r['indicator'];
          $onVal   = $toFloat($r['on_time'] ?? 0);
          $lateVal = $toFloat($r['late'] ?? 0);
          $total   = $toFloat($r['total'] ?? ($onVal + $lateVal));
          $cid     = 'lagmini_'.$g->code.'_'.$ind->code;

          $currScope = $scopeNow;
          $lbls = $buckets ?: $makeScopeLabels($currScope, $monthVal);
          $lenLabels = count($lbls);

          $vals = $series[$g->code][$ind->code] ?? null;
          if (!is_array($vals) || empty($vals)) {
            if ($currScope === 'year') {
              $vals   = array_fill(0, 12, 0);
              $anchor = max(0, min(11, (int)$monthVal - 1));
              $vals[$anchor] = $total;
              $lenLabels = 12; $lbls = range(1,12);
            } elseif ($currScope === 'week') {
              $vals = array_fill(0, 5, 0); $vals[4] = $total;
              $lenLabels = 5; $lbls = [1,2,3,4,5];
            } elseif ($currScope === 'month') {
              $vals = [ $total ];
              $lenLabels = 1; $lbls = [$monthVal];
            } else {
              $vals = [ $total ];
              $lenLabels = 1;
            }
          } else {
            $vals = array_values($vals);
            if (count($vals) < $lenLabels) $vals = array_pad($vals, $lenLabels, 0);
            if (count($vals) > $lenLabels) $vals = array_slice($vals, 0, $lenLabels);
          }

          $thrRaw = $r['threshold'] ?? null;
          $thrNum = ($thrRaw===null) ? null : $toFloat($thrRaw);
          $thrSeries = is_numeric($thrNum) ? array_fill(0, $lenLabels, (float)$thrNum) : null;

          $maxData  = max(array_map(fn($v)=> (is_numeric($v)? (float)$v : 0), $vals ?: [0]));
          $chartMax = max($maxData, (float)($thrNum ?? 0), 1) * 1.15;

          $palette = ['#0ea5e9','#10b981','#8b5cf6','#f59e0b','#14b8a6','#22c55e'];
          $barColors = [];
          foreach ($vals as $i => $v) {
            $vv = is_numeric($v) ? (float)$v : 0;
            $barColors[] = (is_numeric($thrNum) && $vv > (float)$thrNum) ? '#ef4444' : $palette[$i % count($palette)];
          }
        @endphp

        <div class="chart-wrap chart-card-mini p-2 pb-3 rounded-lg ring-1 ring-slate-200/70 hover:shadow-sm transition-shadow">
          <div class="chart-head"><div class="chart-title">{{ $ind->name }}</div></div>
          <canvas id="{{ $cid }}"></canvas>
        </div>

        @push('scripts')
        <script>
          (function(){
            var el=document.getElementById(@js($cid)); if(!el) return;

            var scopeNow = @json($scopeNow);
            var isYear   = scopeNow === 'year';
            var isWeek   = scopeNow === 'week';
            var isMonth  = scopeNow === 'month';
            var rawLabels = @json(array_values($lbls));

            var monthShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Des'];
            var weekdayShort = ['Sen','Sel','Rab','Kam','Jum'];

            var labels = rawLabels.map(function(v,i){
              if (isYear)  return monthShort[i] || String(v);
              if (isWeek)  return weekdayShort[i] || String(v);
              if (isMonth) {
                var n = parseInt(String(v),10);
                if (!isNaN(n) && n>=1 && n<=12) return monthShort[n-1];
              }
              return String(v);
            });

            var vals      = @json(array_values($vals));
            var barColors = @json($barColors);
            var thrSeries = @json($thrSeries);
            var thrValue  = @json($thrNum);
            var suggested = @json($chartMax);

            var thresholdLabel = {
              id: 'thresholdLabel',
              afterDatasetsDraw(chart){
                if (thrValue == null) return;
                var y = chart.scales.y.getPixelForValue(thrValue);
                var left = chart.chartArea.left;
                var ctx = chart.ctx;
                ctx.save();
                ctx.fillStyle = '#f59e0b';
                ctx.font = '600 10px system-ui,-apple-system,Segoe UI,Roboto,Arial';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                var txt = new Intl.NumberFormat('id-ID').format(thrValue);
                ctx.fillText('Target: '+txt, left + 2, y);
                ctx.restore();
              }
            };

            var valueLabels = {
              id: 'valueLabels',
              afterDatasetsDraw(chart){
                var {ctx, chartArea:{top}} = chart;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                ctx.font = '600 10px system-ui,-apple-system,Segoe UI,Roboto,Arial';
                ctx.fillStyle = '#111827';
                var meta = chart.getDatasetMeta(0);
                meta.data.forEach(function(elm, i){
                  var v = chart.data.datasets[0].data[i];
                  if (v==null || Number(v)===0) return;
                  var txt = new Intl.NumberFormat('id-ID').format(v);
                  var x = elm.x, y = elm.y - 4;
                  if (y < top + 8) y = top + 8;
                  ctx.fillText(txt, x, y);
                });
                ctx.restore();
              }
            };

            new Chart(el,{
              type:'bar',
              data:{
                labels: labels,
                datasets:[
                  {
                    label:'Total',
                    data: vals,
                    backgroundColor: barColors,
                    borderWidth:0,
                    borderRadius: 6,
                    categoryPercentage:.6,
                    barPercentage:.8,
                    maxBarThickness:18
                  },
                  (thrSeries ? {
                    type:'line',
                    label:'Target',
                    data: thrSeries,
                    borderColor:'#f59e0b',
                    borderWidth:1.5,
                    borderDash:[4,4],
                    pointRadius:0,
                    fill:false,
                    tension:0
                  } : null)
                ].filter(Boolean)
              },
              options:{
                responsive:true, maintainAspectRatio:false,
                layout:{ padding:{ top:4, right:8, bottom:22, left:8 }},
                plugins:{
                  legend:{display:false},
                  tooltip:{
                    mode:'index', intersect:false,
                    callbacks:{
                      label:function(c){
                        var val = c.parsed.y ?? c.parsed;
                        var base = c.dataset.label+': '+new Intl.NumberFormat('id-ID').format(val);
                        if (c.datasetIndex===0 && thrValue!=null) {
                          base += ' (Target: '+new Intl.NumberFormat('id-ID').format(thrValue)+')';
                        }
                        return base;
                      }
                    }
                  }
                },
                scales:{
                  x:{
                    grid:{display:false},
                    ticks:{
                      display:true,
                      autoSkip: !(isYear || isWeek || isMonth),
                      maxRotation:0, minRotation:0, font:{size:10}, padding:2,
                      maxTicksLimit: (isYear?12:(isWeek?5:1))
                    }
                  },
                  y:{grid:{display:false}, ticks:{display:false}, beginAtZero:true, suggestedMax: suggested}
                }
              },
              plugins:[valueLabels, thresholdLabel]
            });
          })();
        </script>
        @endpush
      @endforeach
    </div>
  @endif
@endforeach

{{-- ========================= BAGIAN 2 — OPERATIONAL / LEADING ========================= --}}
@foreach($groups as $g)
  @continue($isLaggingGroup($g))
  @php
    $rows = collect($data[$g->code] ?? []);
    $leadRows = $rows->reject(fn($r)=>$isLaggingInd($r['indicator']));
    $isBaseGroup = str_contains(strtolower($g->code.$g->name),'base') || str_contains(strtolower($g->name),'deskripsi');
  @endphp

  @if($leadRows->isNotEmpty())
    <div class="mb-2 text-sm font-semibold text-maroon-700">
      Operational / Leading — {{ $g->name }}
      @if($isBaseGroup) <span class="opacity-70">(Base Metrics)</span> @endif
    </div>

    @if($isBaseGroup)
      {{-- ========== BASE METRICS → DONUT ========== --}}
      <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3 mb-8">
        @foreach($leadRows as $r)
          @php
            $ind     = $r['indicator'];
            $onVal   = $toFloat($r['on_time'] ?? 0);
            $lateVal = $toFloat($r['late'] ?? 0);
            $total   = $toFloat($r['total'] ?? ($onVal + $lateVal));

            $thrRaw  = $r['threshold'] ?? null;
            $thrNum  = $thrRaw===null ? null : $toFloat($thrRaw);
            $hasThr  = is_numeric($thrNum) && $thrNum > 0;

            $achieved = $hasThr ? min($total, $thrNum) : $total;
            $remain   = $hasThr ? max($thrNum - $total, 0) : 0;
            $pct      = $hasThr ? max(0, min(100, ($total/$thrNum)*100)) : null;

            $thrDisp  = $hasThr ? (fmod((float)$thrNum,1.0)==0.0 ? number_format((float)$thrNum,0,',','.') : number_format((float)$thrNum,2,',','.')) : '-';

            $did = 'donut_base_'.$g->code.'_'.$ind->code;
          @endphp

          <div class="chart-wrap chart-card-donut p-3 pb-4 rounded-lg ring-1 ring-slate-200/70 hover:shadow-sm transition-shadow">
            <div class="chart-head"><div class="chart-title">{{ $ind->name }}</div></div>
            <div class="chart-canvas"><canvas id="{{ $did }}"></canvas></div>
            <div class="mt-1 text-[10px] text-slate-500">Target: {{ $thrDisp }}</div>
          </div>

          @push('scripts')
          <script>
          (function(){
            var el=document.getElementById('{{ $did }}'); if(!el) return;
            var ctx = el.getContext('2d');

            var gradYes = ctx.createLinearGradient(0,0,0,el.height);
            gradYes.addColorStop(0, '#10b981');
            gradYes.addColorStop(1, '#059669');

            var centerText={
              id:'centerText_{{ $did }}',
              beforeDraw:function(chart){
                var area=chart.chartArea; if(!area) return;
                var ctx=chart.ctx, cx=(area.left+area.right)/2, cy=(area.top+area.bottom)/2;
                ctx.save();
                ctx.textAlign='center'; ctx.textBaseline='middle';
                ctx.font='700 14px system-ui, -apple-system, Segoe UI, Roboto, Arial';
                ctx.fillStyle='#111827';
                ctx.fillText('{{ number_format($total,0,',','.') }}', cx, cy - 4);
                @if($hasThr)
                  ctx.font='600 11px system-ui, -apple-system, Segoe UI, Roboto, Arial';
                  ctx.fillStyle='#6b7280';
                  ctx.fillText('{{ number_format($pct,0,',','.') }}%', cx, cy + 12);
                @endif
                ctx.restore();
              }
            };

            new Chart(el,{
              type:'doughnut',
              data:{
                labels:[ @if($hasThr) 'Tercapai','Sisa Target' @else 'Total' @endif ],
                datasets:[{
                  data:[{{ $achieved }}, {{ $remain }}],
                  backgroundColor:[gradYes, '#e5e7eb'],
                  borderWidth:0
                }]
              },
              options:{
                responsive:true, maintainAspectRatio:false,
                cutout:'68%',
                plugins:{
                  legend:{display:false},
                  tooltip:{callbacks:{ label:function(c){
                    var v=c.raw ?? 0; return c.label+': '+new Intl.NumberFormat('id-ID').format(v);
                  }}}
                }
              },
              plugins:[centerText]
            });
          })();
          </script>
          @endpush
        @endforeach
      </div>
    @else
      @php
        $cid    = 'grp_'.$g->code;
        $labels = $leadRows->map(fn($r)=>$r['indicator']->name)->values();
        $totals = $leadRows->map(function($r) use ($toFloat){
          $on  = $toFloat($r['on_time'] ?? 0);
          $lt  = $toFloat($r['late'] ?? 0);
          return $toFloat($r['total'] ?? ($on + $lt));
        })->values();
        $thrLines = $leadRows->map(function($r) use ($toFloat){
          $raw = $r['threshold'] ?? null;
          if ($raw===null) return null;
          $num = $toFloat($raw);
          return is_numeric($num) ? (float)$num : null;
        })->values();

        $palette = ['#0ea5e9','#10b981','#8b5cf6','#f59e0b','#14b8a6','#22c55e'];
        $barColors = [];
        foreach ($totals as $i=>$v) {
          $thr = $thrLines[$i] ?? null;
          $barColors[] = ($thr !== null && $v > $thr) ? '#ef4444' : $palette[$i % count($palette)];
        }
      @endphp

      <div class="chart-wrap chart-card-bar p-4 mb-8">
        <canvas id="{{ $cid }}"></canvas>
      </div>

      @push('scripts')
      <script>
      (function(){
        var el=document.getElementById('{{ $cid }}'); if(!el) return;

        var vals = {!! json_encode($totals) !!};
        var thr  = {!! json_encode($thrLines) !!};
        var colors = {!! json_encode($barColors) !!};

        var maxData = Math.max.apply(null, vals.map(function(v){return (v==null?0:v);}));
        var maxThr  = Math.max.apply(null, thr.map(function(v){return (v==null?0:v);}));
        var suggested = Math.max(maxData, maxThr, 1) * 1.15;

        var valueLabels = {
          id: 'valueLabels',
          afterDatasetsDraw(chart){
            var {ctx, chartArea:{top}} = chart;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            ctx.font = '600 12px system-ui,-apple-system,Segoe UI,Roboto,Arial';
            ctx.fillStyle = '#111827';
            var meta = chart.getDatasetMeta(0);
            meta.data.forEach(function(elm, i){
              var v = chart.data.datasets[0].data[i];
              if (v==null || Number(v)===0) return;
              var txt = new Intl.NumberFormat('id-ID').format(v);
              var x = elm.x, y = elm.y - 6;
              if (y < top + 10) y = top + 10;
              ctx.fillText(txt, x, y);
            });
            ctx.restore();
          }
        };

        var thresholdLabel = {
          id:'thresholdLabel',
          afterDatasetsDraw(chart){
            if (!thr.some(function(v){return v!=null;})) return;
            var arr = thr.filter(function(v){return v!=null;});
            var avg = arr.reduce(function(a,b){return a+b;},0) / arr.length;
            var y = chart.scales.y.getPixelForValue(avg);
            var left = chart.chartArea.left;
            var ctx = chart.ctx;
            ctx.save();
            ctx.fillStyle = '#f59e0b';
            ctx.font = '600 10px system-ui,-apple-system,Segoe UI,Roboto,Arial';
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText('Target', left + 2, y);
            ctx.restore();
          }
        };

        new Chart(el,{
          type:'bar',
          data:{
            labels:{!! json_encode($labels) !!},
            datasets:[
              {label:'Total', data:vals, backgroundColor:colors, borderWidth:0, borderRadius:6},
              {type:'line', label:'Target', data:thr, borderColor:'#f59e0b', borderWidth:2, borderDash:[5,5], pointRadius:0, fill:false, tension:0}
            ]
          },
          options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{
              legend:{position:'bottom'},
              tooltip:{
                mode:'index', intersect:false,
                callbacks:{label:function(c){
                  var v = c.parsed.y ?? c.parsed;
                  return c.dataset.label+': '+new Intl.NumberFormat('id-ID').format(v);
                }}
              }
            },
            scales:{ x:{grid:{display:false}}, y:{beginAtZero:true, suggestedMax: suggested} }
          },
          plugins:[valueLabels, thresholdLabel]
        });
      })();
      </script>
      @endpush
    @endif
  @endif
@endforeach

{{-- ========================= BAGIAN 3 — DETAIL TABEL (Total saja) ========================= --}}
@foreach($groups as $g)
  <div class="mb-6 chart-wrap overflow-hidden">
    <div class="px-3 py-2 font-semibold bg-maroon-700 text-white">{{ $g->name }}</div>
    <table class="min-w-full">
      <thead class="bg-maroon-700 text-white">
        <tr>
          <th class="px-3 py-2 text-left w-10">#</th>
          <th class="px-3 py-2 text-left">Indicator</th>
          <th class="px-3 py-2 text-right w-40">Total</th>
          <th class="px-3 py-2 text-right w-28">Threshold</th>
          <th class="px-3 py-2 text-left w-24">Unit</th>
          @if($isSuperAdmin)
            <th class="px-3 py-2 text-center w-32">Aksi</th>
          @endif
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach(($data[$g->code] ?? []) as $row)
          @php
            $ind=$row['indicator'];
            $onVal=$toFloat($row['on_time'] ?? 0);
            $lateVal=$toFloat($row['late'] ?? 0);
            $totalVal=$toFloat($row['total'] ?? ($onVal+$lateVal));
            $isBaseLocal=$isBase($ind);
            $thrNum = null;

            // route param untuk edit total (override) – dipakai di kolom Total & tombol "Edit Total"
            $editTotalParams = [
                'indicator_id' => $ind->id,
                'group_code'   => $g->code,
                'scope'        => $scopeNow,
                'date'         => request('date'),
                'week'         => request('week'),
                'month'        => request('month'),
                'year'         => request('year'),
                'site_id'      => $siteId,
            ];

            if($isBaseLocal){
              $thrDisp='-'; $isOver=false;
            } else {
              $thrRaw=$row['threshold'] ?? null;
              $thrNum=$thrRaw===null ? null : $toFloat($thrRaw);
              $thrDisp=($thrRaw===null || trim((string)$thrRaw)==='')
                ? '0'
                : (fmod((float)$thrNum,1.0)==0.0 ? number_format((float)$thrNum,0,',','.') : number_format((float)$thrNum,2,',','.'));
              $isOver=($thrNum !== null) && ($totalVal > $thrNum);
            }

            // ============= LOG PER BARIS =============
            Log::info('AGG VIEW: row total', [
                'group_code'      => $g->code,
                'group_name'      => $g->name,
                'indicator_id'    => $ind->id,
                'indicator_code'  => $ind->code ?? null,
                'indicator_name'  => $ind->name,
                'scope'           => $scopeNow,
                'site_id'         => $siteId ?? null,
                'date'            => request('date'),
                'week'            => request('week'),
                'month'           => request('month'),
                'year'            => request('year'),
                'on_time'         => $onVal,
                'late'            => $lateVal,
                'totalVal'        => $totalVal,
                'raw_row'         => $row,
                'threshold_raw'   => $row['threshold'] ?? null,
                'threshold_num'   => $thrNum,
                'is_base'         => $isBaseLocal,
                'is_over_target'  => $isOver ?? null,
            ]);
          @endphp
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-2">{{ $ind->order_index }}</td>
            <td class="px-3 py-2">
              <div class="font-medium">{{ $ind->name }}</div>
              @if($ind->is_derived)
                <div class="text-xs text-gray-500 font-mono">= {{ $ind->formula }}</div>
              @endif
            </td>

            {{-- TOTAL: kalau super_admin, angka bisa diklik untuk ubah total --}}
            <td class="px-3 py-2 text-right font-bold {{ (!$isBaseLocal && $isOver) ? 'text-rose-600' : '' }}">
              @if($isSuperAdmin)
                <a href="{{ route('admin.report-totals.edit', $editTotalParams) }}"
                   class="underline decoration-dotted underline-offset-2 hover:text-maroon-700">
                  {{ number_format($totalVal, fmod($totalVal,1.0)==0.0 ? 0 : 2, ',', '.') }}
                </a>
              @else
                {{ number_format($totalVal, fmod($totalVal,1.0)==0.0 ? 0 : 2, ',', '.') }}
              @endif
            </td>

            <td class="px-3 py-2 text-right font-mono">{{ $thrDisp }}</td>
            <td class="px-3 py-2">{{ trim((string)($ind->unit ?? '')) ?: '-' }}</td>

            @if($isSuperAdmin)
              <td class="px-3 py-2 text-center">
                <div class="flex flex-col items-center gap-1">

                  {{-- Edit definisi indikator --}}
                  <a href="{{ route('admin.indicators.edit', $ind->id) }}"
                     class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border border-maroon-500 text-maroon-700 hover:bg-maroon-50">
                    Edit Indikator
                  </a>

                  {{-- Edit / override total agregat (link eksplisit) --}}
                  <a href="{{ route('admin.report-totals.edit', $editTotalParams) }}"
                     class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border border-slate-300 text-slate-700 hover:bg-slate-50">
                    Edit Total
                  </a>

                </div>
              </td>
            @endif
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endforeach
@endsection
