{{-- resources/views/admin/reports/aggregate.blade.php --}}
@extends('layouts.app')
@section('title','Rekap')

@push('styles')
<style>
  /* ===== Layout kecil rapi, nggak “melorot” ===== */
  .cards-grid{display:grid;gap:.75rem}
  @media (min-width:768px){.cards-grid{grid-template-columns:repeat(6,minmax(0,1fr))}}
  .stat-card{padding:1rem;border:1px solid #e5e7eb;border-radius:.5rem;background:#fff}
  .chart-wrap{border:1px solid #e5e7eb;border-radius:.5rem;background:#fff;overflow:hidden}

  /* ===== Tinggi pasti utk chart (biar nggak auto memanjang) ===== */
  .chart-card-bar{height:360px}
  .chart-card-donut{height:260px}
  .chart-card-bar canvas,
  .chart-card-donut canvas{width:100%!important;height:100%!important;display:block}

  /* ===== Progress mini utk kartu threshold ===== */
  .progress-bar{width:100%;height:10px;border-radius:9999px;overflow:hidden;background:#e5e7eb}
  .progress-fill{height:100%;border-radius:9999px}

  /* Dark mode (optional) */
  .dark .stat-card,.dark .chart-wrap{background:#0f141a;border-color:#263241}
  .dark .progress-bar{background:#263241}
</style>
@endpush

{{-- MUAT Chart.js SEBELUM SEMUA SCRIPT LAIN --}}
@push('scripts')
@once
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endonce
@endpush

@section('content')
<h1 class="text-2xl font-bold mb-4 text-maroon-700">Rekap — {{ $period }}</h1>

{{-- ========================= Filter ========================= --}}
<form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-3"
  x-data="{ scope: '{{ $scope ?? 'month' }}' }">
  <input type="hidden" name="scope" :value="scope">

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
  <input type="date" name="date" value="{{ $date ?? now()->toDateString() }}"
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
    <select name="month" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
            :disabled="scope!=='month'">
      @for($m=1;$m<=12;$m++)
        <option value="{{ $m }}" @selected(($month ?? now()->month)===$m)>{{ $m }}</option>
      @endfor
    </select>
    <input type="number" name="year" value="{{ $year ?? now()->year }}"
           class="border rounded-lg px-3 py-2 w-28 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
           :disabled="scope!=='month'">
  </div>

  {{-- YEAR --}}
  <input type="number" name="year" value="{{ $year ?? now()->year }}"
         class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
         x-show="scope==='year'" :disabled="scope!=='year'">

  <button class="px-4 py-2 bg-maroon-600 hover:bg-maroon-700 text-white rounded-lg md:col-span-1">Terapkan</button>
</form>

{{-- ========================= Stat Cards ========================= --}}
<div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
  <div class="stat-card">
    <div class="text-xs">Total Groups</div>
    <div class="text-xl font-bold">{{ $groups->count() }}</div>
  </div>
  <div class="stat-card">
    <div class="text-xs">Indicators</div>
    <div class="text-xl font-bold">{{ collect($groups)->flatMap(fn($g)=>$g->indicators)->count() }}</div>
  </div>
  <div class="stat-card">
    <div class="text-xs">Scope</div>
    <div class="text-xl font-bold uppercase">{{ $scope }}</div>
  </div>
  <div class="stat-card">
    <div class="text-xs">Periode</div>
    <div class="text-sm font-semibold">{{ $period }}</div>
  </div>
  <div class="stat-card">
    <div class="text-xs">On-time Total</div>
    <div class="text-xl font-bold" style="color:#059669">{{ number_format($totalOntime ?? 0, 0, ',', '.') }}</div>
  </div>
  <div class="stat-card">
    <div class="text-xs">Late Total</div>
    <div class="text-xl font-bold" style="color:#e11d48">{{ number_format($totalLate ?? 0, 0, ',', '.') }}</div>
  </div>
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
@endphp

{{-- ========================= BAGIAN 1 — LAGGING INDICATORS (cards) ========================= --}}
@php $anyLagCards=false; @endphp
@foreach($groups as $g)
  @php
    $rowsAll = collect($data[$g->code] ?? []);
    $rows    = $isLaggingGroup($g) ? $rowsAll : $rowsAll->filter(fn($r)=>$isLaggingInd($r['indicator']));
  @endphp

  @if($rows->isNotEmpty())
    @if(!$anyLagCards)
      <div class="mb-2 text-sm font-semibold text-maroon-700">Lagging Indicators</div>
      @php $anyLagCards=true; @endphp
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3 mb-6">
      @foreach($rows as $r)
        @php
          $ind   = $r['indicator'];
          $unit  = trim((string)($ind->unit ?? ''));
          $on    = $toFloat($r['on_time'] ?? 0);
          $late  = $toFloat($r['late'] ?? 0);
          $total = $toFloat($r['total'] ?? ($on + $late));

          $thrRaw   = $r['threshold'] ?? null;
          $thrFloat = $thrRaw===null ? null : $toFloat($thrRaw);
          $hasThr   = is_numeric($thrFloat) && $thrFloat>0;
          $thrLabel = $makeThresholdLabel($thrRaw,$thrFloat);

          $pct   = ($hasThr && $thrFloat>0) ? max(0,min(100,($total/$thrFloat)*100)) : null;
          $meet  = $hasThr ? ($total >= $thrFloat) : null;
        @endphp

        <div class="stat-card">
          {{-- Header: Nama + badge meet target --}}
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="text-xs">{{ $ind->name }}</div>
              <div class="text-xl font-bold">
                {{ number_format($total, fmod($total,1.0)==0.0?0:2, ',', '.') }}{{ $unit ? ' '.$unit : '' }}
              </div>
            </div>
            @if($hasThr)
              <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0
                {{ $meet ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200' }}">
                {{ $meet ? '≥' : '<' }} target
              </span>
            @endif
          </div>

          {{-- Body: % of Threshold + progress + target --}}
          <div class="mt-2">
            <div class="text-xs text-gray-600">% of Threshold</div>
            <div class="text-lg font-semibold leading-tight">
              {{ $hasThr ? number_format($pct,0,',','.') . '%' : '—' }}
            </div>

            @if($hasThr)
              <div class="mt-2 progress-bar">
                <div class="progress-fill {{ $meet ? 'bg-emerald-500' : 'bg-rose-500' }}"
                     style="width: {{ (float)$pct }}%"></div>
              </div>
            @endif

            @if(!($thrRaw===null || trim((string)$thrRaw)===''))
              <div class="mt-1 text-[11px] text-gray-600">
                Target: <span class="font-semibold">{{ $thrLabel }}</span>
              </div>
            @endif
          </div>
        </div>
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
      Operational / Leading Indicators — {{ $g->name }}
    </div>

    @if($isBaseGroup)
      {{-- Donut (per indikator base) --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @foreach($leadRows as $r)
          @php
            $did = 'donut_'.$r['indicator']->code;
            $onVal   = $toFloat($r['on_time'] ?? 0);
            $lateVal = $toFloat($r['late'] ?? 0);
            $total   = $onVal + $lateVal;
          @endphp

          <div class="chart-wrap chart-card-donut p-4">
            <div class="mb-2 font-semibold text-sm">{{ $r['indicator']->name }}</div>
            <canvas id="{{ $did }}"></canvas>
          </div>

          @push('scripts')
          <script>
          (function(){
            var el=document.getElementById('{{ $did }}'); if(!el) return;
            var centerText={
              id:'centerText_{{ $did }}',
              beforeDraw:function(chart){
                var area=chart.chartArea; if(!area) return;
                var ctx=chart.ctx, cx=(area.left+area.right)/2, cy=(area.top+area.bottom)/2;
                ctx.save(); ctx.textAlign='center'; ctx.textBaseline='middle';
                ctx.font='600 14px system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
                ctx.fillStyle='#111827';
                ctx.fillText('{{ number_format($total,0,',','.') }}', cx, cy);
                ctx.restore();
              }
            };
            new Chart(el,{
              type:'doughnut',
              data:{ labels:['On-time','Late'], datasets:[{ data:[{{ $onVal }},{{ $lateVal }}], backgroundColor:['#10b981','#ef4444'] }]},
              options:{ responsive:true, maintainAspectRatio:false, cutout:'65%',
                plugins:{ legend:{position:'bottom'},
                  tooltip:{callbacks:{label:function(c){return c.label+': '+new Intl.NumberFormat('id-ID').format(c.raw);}}}
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
      {{-- Stacked bar (non-base) --}}
      @php
        $cid    = 'grp_'.$g->code;
        $labels = $leadRows->map(fn($r)=>$r['indicator']->name)->values();
        $onList = $leadRows->map(fn($r)=>$toFloat($r['on_time'] ?? 0))->values();
        $ltList = $leadRows->map(fn($r)=>$toFloat($r['late'] ?? 0))->values();
      @endphp

      <div class="chart-wrap chart-card-bar p-4 mb-8">
        <canvas id="{{ $cid }}"></canvas>
      </div>

      @push('scripts')
      <script>
      (function(){
        var el=document.getElementById('{{ $cid }}'); if(!el) return;
        new Chart(el,{
          type:'bar',
          data:{
            labels:{!! json_encode($labels) !!},
            datasets:[
              {label:'On-time', data:{!! json_encode($onList) !!}, backgroundColor:'#10b981', borderWidth:0, stack:'s'},
              {label:'Late',    data:{!! json_encode($ltList) !!}, backgroundColor:'#ef4444', borderWidth:0, stack:'s'}
            ]
          },
          options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{
              legend:{position:'bottom'},
              tooltip:{mode:'index', intersect:false,
                callbacks:{label:function(c){return c.dataset.label+': '+new Intl.NumberFormat('id-ID').format(c.parsed.y ?? c.parsed);}}
              }
            },
            scales:{x:{stacked:true, grid:{display:false}}, y:{stacked:true, beginAtZero:true}}
          }
        });
      })();
      </script>
      @endpush
    @endif
  @endif
@endforeach

{{-- ========================= BAGIAN 3 — DETAIL TABEL ========================= --}}
@foreach($groups as $g)
  <div class="mb-6 chart-wrap overflow-hidden">
    <div class="px-3 py-2 font-semibold bg-maroon-700 text-white">{{ $g->name }}</div>
    <table class="min-w-full">
      <thead class="bg-maroon-700 text-white">
        <tr>
          <th class="px-3 py-2 text-left w-10">#</th>
          <th class="px-3 py-2 text-left">Indicator</th>
          <th class="px-3 py-2 text-right w-36">On-time</th>
          <th class="px-3 py-2 text-right w-36">Late</th>
          <th class="px-3 py-2 text-right w-40">Total</th>
          <th class="px-3 py-2 text-right w-28">Threshold</th>
          <th class="px-3 py-2 text-left w-24">Unit</th>
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
            if($isBaseLocal){ $thrDisp='-'; $isOver=false; }
            else{
              $thrRaw=$row['threshold'] ?? null;
              $thrNum=$thrRaw===null ? null : $toFloat($thrRaw);
              $thrDisp=$makeThresholdLabel($thrRaw,$thrNum);
              $isOver=($thrNum !== null) && ($totalVal > $thrNum);
            }
          @endphp
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-2">{{ $ind->order_index }}</td>
            <td class="px-3 py-2">
              <div class="font-medium">{{ $ind->name }}</div>
              @if($ind->is_derived)
                <div class="text-xs text-gray-500 font-mono">= {{ $ind->formula }}</div>
              @endif
            </td>
            <td class="px-3 py-2 text-right" style="color:#059669">{{ number_format($onVal,0,',','.') }}</td>
            <td class="px-3 py-2 text-right" style="color:#e11d48">{{ number_format($lateVal,0,',','.') }}</td>
            <td class="px-3 py-2 text-right font-bold {{ (!$isBaseLocal && $isOver) ? 'text-rose-600' : '' }}">
              {{ number_format($totalVal, fmod($totalVal,1.0)==0.0 ? 0 : 2, ',', '.') }}
            </td>
            <td class="px-3 py-2 text-right font-mono">{{ $thrDisp }}</td>
            <td class="px-3 py-2">{{ trim((string)($ind->unit ?? '')) ?: '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endforeach
@endsection
