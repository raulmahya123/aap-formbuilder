{{-- resources/views/admin/reports/aggregate.blade.php --}}
@extends('layouts.app')
@section('title','Rekap')

@section('content')
<h1 class="text-2xl font-bold mb-4 text-maroon-700">Rekap — {{ $period }}</h1>

{{-- =========================
     Filter (scope: day/week/month/year)
========================= --}}
<form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-3"
  x-data="{ scope: '{{ $scope ?? 'month' }}' }">
  <input type="hidden" name="scope" :value="scope">

  <select name="site_id" class="border rounded-lg px-3 py-2 md:grid-cols-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
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
    <select name="month"
      class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
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

{{-- =========================
     Stat Cards
========================= --}}
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

  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">On-time Total</div>
    <div class="text-xl font-bold text-emerald-600">
      {{ number_format($totalOntime ?? 0, 0, ',', '.') }}
    </div>
  </div>

  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">Late Total</div>
    <div class="text-xl font-bold text-rose-600">
      {{ number_format($totalLate ?? 0, 0, ',', '.') }}
    </div>
  </div>
</div>

@php
  // ===== Helpers
  $toFloat = function($raw) {
    if ($raw === null) return 0.0;
    $s = trim((string)$raw);
    if ($s === '' || $s === '-') return 0.0;
    $s = preg_replace('/[^0-9,.\-]/', '', $s);
    $s = preg_replace('/(?<=\d)[,.](?=\d{3}(\D|$))/', '', $s);
    if (str_contains($s, ',') && !str_contains($s, '.')) $s = str_replace(',', '.', $s);
    if (substr_count($s, '.') > 1) { $p = strrpos($s,'.'); $s = str_replace('.', '', substr($s,0,$p)).substr($s,$p); }
    return is_numeric($s) ? (float)$s : 0.0;
  };

  // indikator lagging
  $isLaggingInd = function($ind) {
    $t = strtolower((string)($ind->type ?? $ind->category ?? ''));
    $slug = strtolower((string)($ind->slug ?? $ind->code ?? ''));
    return (bool)($ind->is_lagging ?? false) || $t==='lagging' || str_contains($slug,'lag');
  };

  // group lagging → jika name/code mengandung "lag"
  $isLaggingGroup = function($g) {
    $s = strtolower((string)($g->name ?? '').' '.(string)($g->code ?? ''));
    return str_contains($s, 'lag'); // match "lag", "lagging"
  };

  // base/Deskripsi → tidak tampil threshold
  $isBase = function($ind) {
    $n = strtolower((string)($ind->name ?? ''));
    $t = strtolower((string)($ind->type ?? $ind->category ?? ''));
    return str_contains($n,'deskripsi') || str_contains($n,'base') || in_array($t, ['base','description','deskripsi']);
  };
@endphp

{{-- =========================
     BAGIAN 1 — LAGGING GROUPS (KOTAK-KOTAK SAJA)
========================= --}}
@php $anyLagCards = false; @endphp
@foreach($groups as $g)
  @php
    $rowsAll = collect($data[$g->code] ?? []);
    // Jika group memang lagging → ambil semua indikatornya ke kartu
    // Jika bukan, ambil hanya indikator yang ditandai lagging
    $rows = $isLaggingGroup($g) ? $rowsAll : $rowsAll->filter(fn($r) => $isLaggingInd($r['indicator']));
  @endphp

  @if($rows->isNotEmpty())
    @if(!$anyLagCards)
      <div class="mb-2 text-sm font-semibold text-maroon-700">Lagging Indicators</div>
      @php $anyLagCards = true; @endphp
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3 mb-6">
      @foreach($rows as $r)
        @php
          $ind   = $r['indicator'];
          $unit  = trim((string)($ind->unit ?? ''));
          $isPct = ($unit==='%' || preg_match('/percent|percentage|persen/i',$unit));

          $on    = $toFloat($r['on_time'] ?? 0);
          $late  = $toFloat($r['late'] ?? 0);
          $total = $toFloat($r['total'] ?? ($on + $late));

          $thrRaw = $r['threshold'] ?? null;
          $thr    = $thrRaw===null ? null : $toFloat($thrRaw);
          $hasThr = is_numeric($thr) && $thr>0;

          $meet   = $hasThr ? ($total >= $thr) : null;
          $pct    = $hasThr ? max(0, min(100, $thr==0?100:($total/$thr)*100)) : null;

          $fmt = fn($v)=> number_format($v, $isPct?2:0, ',', '.');
        @endphp

        <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
          <div class="text-xs">{{ $ind->name }}</div>
          <div class="text-xl font-bold">
            {{ $fmt($total) }}{{ $isPct ? '%' : ($unit ? ' '.$unit : '') }}
          </div>

          @if($hasThr)
            <div class="mt-2 text-[11px] text-gray-600 dark:text-gray-300">
              Threshold:
              <span class="font-semibold">{{ $fmt($thr) }}{{ $isPct ? '%' : ($unit ? ' '.$unit : '') }}</span>
              <span class="ml-2 px-1.5 py-0.5 rounded border
                {{ $meet ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                         : 'bg-rose-100 text-rose-700 border-rose-200' }}">
                {{ $meet ? '≥ threshold' : '< threshold' }}
              </span>
            </div>
            <div class="mt-2 w-full h-2.5 rounded-full bg-gray-200 dark:bg-coal-800 overflow-hidden">
              <div class="h-2.5 rounded-full {{ $meet ? 'bg-emerald-500' : 'bg-rose-500' }}"
                   style="width: {{ number_format($pct,2,'.','') }}%"></div>
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @endif
@endforeach

{{-- =========================
     BAGIAN 2 — OPERATIONAL / LEADING (GRAFIK GABUNGAN SAJA)
     * SKIP semua group yang terdeteksi lagging by name/code
========================= --}}
@foreach($groups as $g)
  @continue($isLaggingGroup($g)) {{-- <-- inilah kuncinya: JANGAN render chart untuk group Lagging --}}

  @php
    $rows = collect($data[$g->code] ?? []);
    // hanya indikator non-lagging
    $leadRows = $rows->reject(fn($r) => $isLaggingInd($r['indicator']));
  @endphp

  @if($leadRows->isNotEmpty())
    <div class="mb-2 text-sm font-semibold text-maroon-700">
      Operational / Leading Indicators — {{ $g->name }}
    </div>
    <div class="p-4 mb-8 border rounded-lg bg-white dark:bg-coal-900">
      @php
        $cid    = 'grp_'.$g->code;
        $labels = $leadRows->map(fn($r) => $r['indicator']->name)->values();
        $onList = $leadRows->map(fn($r) => $toFloat($r['on_time'] ?? 0))->values();
        $ltList = $leadRows->map(fn($r) => $toFloat($r['late'] ?? 0))->values();

        // Garis threshold hanya untuk indikator non-base
        $thrList= $leadRows->map(function($r) use($toFloat) {
          $ind = $r['indicator'];
          $n   = strtolower((string)($ind->name ?? ''));
          $t   = strtolower((string)($ind->type ?? $ind->category ?? ''));
          $isBase = str_contains($n,'deskripsi') || str_contains($n,'base') || in_array($t,['base','description','deskripsi']);
          if ($isBase) return null;
          $v = $toFloat($r['threshold'] ?? null);
          return $v>0 ? $v : null;
        })->values();
      @endphp
      <canvas id="{{ $cid }}" height="320"></canvas>
    </div>

    @push('scripts')
    @once
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce
    <script>
    (() => {
      const id = @json($cid);
      const ctx = document.getElementById(id);
      if (!ctx) return;

      const labels = @json($labels);
      const onData = @json($onList);
      const ltData = @json($ltList);
      const thrRaw = @json($thrList);

      const maxThr = Math.max(...thrRaw.filter(v => v!==null), 0);
      const thrData = thrRaw.map(v => (v===null ? null : v));

      new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            { label:'On-time', data:onData, backgroundColor:'#10b981', borderWidth:0, stack:'s' },
            { label:'Late',    data:ltData, backgroundColor:'#ef4444', borderWidth:0, stack:'s' },
            ...(maxThr>0 ? [{
              type:'line', label:'Threshold (non-base)', data:thrData,
              borderColor:'#f59e0b', borderWidth:2, pointRadius:2, spanGaps:true, yAxisID:'y'
            }] : [])
          ]
        },
        options: {
          responsive:true, maintainAspectRatio:false, animation:{duration:900, easing:'easeOutQuart'},
          plugins:{ legend:{position:'bottom'},
            tooltip:{ mode:'index', intersect:false,
              callbacks:{ label:(ctx)=>`${ctx.dataset.label}: ${new Intl.NumberFormat('id-ID').format(ctx.parsed.y ?? ctx.parsed)}` }
            }},
          scales:{ x:{stacked:true, grid:{display:false}}, y:{stacked:true, beginAtZero:true, grid:{color:'rgba(148,163,184,.25)'}} }
        }
      });
    })();
    </script>
    @endpush
  @endif
@endforeach

{{-- =========================
     BAGIAN 3 — TABEL DETAIL (threshold kosong untuk Base)
========================= --}}
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
          <th class="px-3 py-2 w-28 text-right">Threshold</th>
          <th class="px-3 py-2 w-24 text-left">Unit</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach(($data[$g->code] ?? []) as $row)
          @php
            $ind      = $row['indicator'];
            $unitRaw  = trim((string)($ind->unit ?? ''));
            $isPercent= ($unitRaw === '%') || preg_match('/percent|percentage|persen/i',$unitRaw);
            $unitOut  = $isPercent ? '%' : $unitRaw;

            $onVal    = $toFloat($row['on_time'] ?? 0);
            $lateVal  = $toFloat($row['late'] ?? 0);
            $totalVal = $toFloat($row['total'] ?? ($onVal + $lateVal));

            $n = strtolower((string)($ind->name ?? ''));
            $t = strtolower((string)($ind->type ?? $ind->category ?? ''));
            $isBaseLocal = str_contains($n,'deskripsi') || str_contains($n,'base') || in_array($t,['base','description','deskripsi']);

            if ($isBaseLocal) {
              $thrDisp = '-';
              $isOver  = false;
            } else {
              $thrRaw  = $row['threshold'] ?? null;
              $thrNum  = $thrRaw===null ? null : $toFloat($thrRaw);
              if ($thrRaw===null || trim((string)$thrRaw)==='') {
                $thrDisp = '0' . ($isPercent?'%':($unitOut? ' '.$unitOut : ''));
              } else {
                $fmtNum = fmod($thrNum,1.0)==0.0 ? number_format($thrNum,0,',','.') : number_format($thrNum,2,',','.');
                $thrDisp = $fmtNum . ($isPercent?'%':($unitOut? ' '.$unitOut : ''));
              }
              $isOver = ($thrNum !== null) && ($totalVal > $thrNum);
            }
          @endphp

          <tr class="hover:bg-gray-50 dark:hover:bg-coal-800 transition">
            <td class="px-3 py-2">{{ $ind->order_index }}</td>
            <td class="px-3 py-2">
              <div class="font-medium">{{ $ind->name }}</div>
              @if($ind->is_derived)
                <div class="text-xs text-gray-500 font-mono">= {{ $ind->formula }}</div>
              @endif
            </td>
            <td class="px-3 py-2 text-right font-semibold text-emerald-600 dark:text-emerald-400">
              {{ number_format($onVal,0,',','.') }}
            </td>
            <td class="px-3 py-2 text-right font-semibold text-rose-600 dark:text-rose-400">
              {{ number_format($lateVal,0,',','.') }}
            </td>
            <td class="px-3 py-2 text-right font-bold {{ (!$isBaseLocal && $isOver) ? 'text-rose-600 dark:text-rose-400' : '' }}">
              {{ fmod($totalVal,1.0)==0.0 ? number_format($totalVal,0,',','.') : number_format($totalVal,2,',','.') }}
            </td>
            <td class="px-3 py-2 align-top text-right text-sm text-gray-700 dark:text-gray-300 font-mono">
              {{ $thrDisp }}
            </td>
            <td class="px-3 py-2">
              {{ $isPercent ? '-' : ($unitOut ?: '-') }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endforeach
@endsection
