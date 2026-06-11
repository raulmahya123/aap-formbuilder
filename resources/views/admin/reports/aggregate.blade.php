{{-- resources/views/admin/reports/aggregate.blade.php --}}
@extends('layouts.app')
@section('title', 'Rekap')

@push('styles')
<style>
  .report-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 6px 24px rgba(0, 0, 0, .05);
  }

  .report-chart {
    position: relative;
    height: 320px;
  }

  .indicator-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    padding: 14px;
    transition: border-color .15s ease, box-shadow .15s ease;
  }

  .indicator-card:hover {
    border-color: #cbb291;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
  }

  .kpi-meter {
    position: relative;
    height: 12px;
    border-radius: 999px;
    background: #f1f5f9;
    overflow: visible;
  }

  .kpi-meter__bar {
    height: 100%;
    min-width: 4px;
    border-radius: inherit;
    background: #bb9974;
  }

  .kpi-meter__bar.is-over {
    background: #dc2626;
  }

  .kpi-meter__threshold {
    position: absolute;
    top: -7px;
    bottom: -7px;
    width: 2px;
    border-radius: 999px;
    background: #f59e0b;
    transform: translateX(-1px);
  }

  .kpi-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }

  .kpi-meta > div {
    min-width: 0;
    border-radius: 8px;
    background: #f8fafc;
    padding: 8px 10px;
  }
</style>
@endpush

@section('content')
@php
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

$u = Auth::user();
$isSuperAdmin = $u && (
    (method_exists($u, 'hasRole') && $u->hasRole('super_admin')) ||
    (($u->role ?? $u->role_key ?? null) === 'super_admin')
);

$tz = 'Asia/Jakarta';
$scopeNow = $scope ?? 'month';
$today = now($tz);
$dateObj = isset($date) && $date ? Carbon::parse($date, $tz) : $today->copy();
$yearVal = isset($year) && $year ? (int) $year : (int) $today->year;
$weekVal = isset($week) && $week ? (int) $week : (int) $today->isoWeek;
$monthVal = isset($month) && $month ? (int) $month : (int) $today->month;
$selectedSite = $siteId ? $sites->firstWhere('id', (int) $siteId) : null;

$periodSafe = $period ?? match($scopeNow) {
    'day' => $dateObj->toDateString(),
    'week' => "Minggu {$weekVal}, {$yearVal}",
    'year' => (string) $yearVal,
    default => sprintf('%02d/%d', $monthVal, $yearVal),
};

$parseThreshold = function ($raw) {
    if ($raw === null) return null;
    $value = trim((string) $raw);
    if ($value === '' || $value === '-') return null;
    $value = preg_replace('/[^0-9,.\-]/', '', $value);
    if ($value === '' || $value === '-' || $value === null) return null;

    if (preg_match('/^-?\d{1,3}([.,]\d{3})+$/', $value)) {
        $value = str_replace(['.', ','], '', $value);
    } elseif (str_contains($value, ',') && str_contains($value, '.')) {
        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');
        $decimal = $lastComma > $lastDot ? ',' : '.';
        $thousand = $decimal === ',' ? '.' : ',';
        $value = str_replace($thousand, '', $value);
        $value = str_replace($decimal, '.', $value);
    } elseif (str_contains($value, ',')) {
        $value = str_replace(',', '.', $value);
    }

    return is_numeric($value) ? (float) $value : null;
};

$fmt = fn($value) => number_format((float) $value, fmod((float) $value, 1.0) === 0.0 ? 0 : 2, ',', '.');
$fmtThreshold = function ($raw) use ($parseThreshold, $fmt) {
    $num = $parseThreshold($raw);
    if ($num === null) return '-';
    $rawText = trim((string) $raw);
    if ($rawText !== '' && preg_match('/[%$]|(?:\bRp\b)|(?:\bIDR\b)/i', $rawText)) return $rawText;
    return $fmt($num);
};

$groupPayload = [];
$indicatorPayload = [];
$grandTotal = 0;
$totalIndicators = 0;
$overTargetCount = 0;

foreach ($groups as $g) {
    $rows = collect($data[$g->code] ?? []);
    $labels = [];
    $values = [];
    $thresholds = [];
    $units = [];
    $colors = [];

    foreach ($rows as $row) {
        $ind = $row['indicator'];
        $value = (float) ($row['total'] ?? $row['value'] ?? 0);
        $threshold = $parseThreshold($row['threshold'] ?? null);
        $isOver = $threshold !== null && $value > $threshold;

        $labels[] = $ind->name;
        $values[] = $value;
        $thresholds[] = $threshold;
        $units[] = trim((string) ($ind->unit ?? '')) ?: '';
        $colors[] = $isOver ? '#dc2626' : '#bb9974';
        $indicatorPayload['indicator_'.$ind->id] = [
            'name' => $ind->name,
            'value' => $value,
            'threshold' => $threshold,
            'thresholdLabel' => $fmtThreshold($row['threshold'] ?? null),
            'unit' => trim((string) ($ind->unit ?? '')) ?: '',
            'color' => $isOver ? '#dc2626' : '#bb9974',
        ];

        $grandTotal += $value;
        $totalIndicators++;
        if ($isOver) $overTargetCount++;
    }

    $groupPayload[$g->code] = [
        'name' => $g->name,
        'labels' => $labels,
        'values' => $values,
        'thresholds' => $thresholds,
        'units' => $units,
        'colors' => $colors,
    ];
}
@endphp

<div class="space-y-5">
  <div class="report-card p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-maroon-700">HSE / KPI Report</p>
        <h1 class="mt-1 text-2xl font-serif font-semibold text-coal-900">Rekap {{ $periodSafe }}</h1>
        <p class="mt-1 text-sm text-coal-500">Rumus derived dihitung otomatis dari kode indikator, dan threshold dipakai konsisten di grafik serta tabel.</p>
      </div>

      <form method="get" class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-6" x-data="{ scope: '{{ $scopeNow }}' }">
        <input type="hidden" name="scope" :value="scope">
        <select name="site_id" class="px-3 py-2 border rounded-lg bg-white">
          <option value="">Semua Site</option>
          @foreach($sites as $s)
            <option value="{{ $s->id }}" @selected(($siteId ?? null) == $s->id)>{{ $s->code }} - {{ $s->name }}</option>
          @endforeach
        </select>

        <select x-model="scope" class="px-3 py-2 border rounded-lg bg-white">
          <option value="day">Harian</option>
          <option value="week">Mingguan</option>
          <option value="month">Bulanan</option>
          <option value="year">Tahunan</option>
        </select>

        <input type="date" name="date" value="{{ $dateObj->toDateString() }}" class="px-3 py-2 border rounded-lg" x-show="scope === 'day'">

        <div class="flex gap-2" x-show="scope === 'week'">
          <input type="number" name="week" value="{{ $weekVal }}" class="w-20 px-3 py-2 border rounded-lg">
          <input type="number" name="year" value="{{ $yearVal }}" class="w-24 px-3 py-2 border rounded-lg">
        </div>

        <div class="flex gap-2" x-show="scope === 'month'">
          <select name="month" class="px-3 py-2 border rounded-lg bg-white">
            @for($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" @selected($monthVal == $m)>{{ $m }}</option>
            @endfor
          </select>
          <input type="number" name="year" value="{{ $yearVal }}" class="w-24 px-3 py-2 border rounded-lg">
        </div>

        <input type="number" name="year" value="{{ $yearVal }}" class="px-3 py-2 border rounded-lg" x-show="scope === 'year'">

        <button class="px-4 py-2 font-semibold text-white rounded-lg bg-maroon-700 hover:bg-maroon-800">Terapkan</button>
      </form>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
    <div class="report-card p-4">
      <div class="text-xs text-coal-500">Groups</div>
      <div class="mt-1 text-2xl font-bold">{{ $groups->count() }}</div>
    </div>
    <div class="report-card p-4">
      <div class="text-xs text-coal-500">Indicators</div>
      <div class="mt-1 text-2xl font-bold">{{ $totalIndicators }}</div>
    </div>
    <div class="report-card p-4">
      <div class="text-xs text-coal-500">Over Threshold</div>
      <div class="mt-1 text-2xl font-bold {{ $overTargetCount ? 'text-rose-600' : 'text-emerald-600' }}">{{ $overTargetCount }}</div>
    </div>
    <div class="report-card p-4">
      <div class="text-xs text-coal-500">Grand Total</div>
      <div class="mt-1 text-2xl font-bold">{{ number_format($grandTotal, 0, ',', '.') }}</div>
    </div>
    <div class="report-card p-4">
      <div class="text-xs text-coal-500">Site</div>
      <div class="mt-1 text-sm font-semibold">{{ $selectedSite ? $selectedSite->code.' - '.$selectedSite->name : 'Semua Site' }}</div>
    </div>
  </div>

  @foreach($groups as $g)
    @php $rows = collect($data[$g->code] ?? []); @endphp
    @continue($rows->isEmpty())

    <section class="report-card overflow-hidden">
      <div class="flex flex-col gap-3 border-b px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="font-semibold text-coal-900">{{ $g->name }}</h2>
          <p class="text-xs text-coal-500">{{ $rows->count() }} indikator</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
          <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-[#bb9974]"></span>Normal</span>
          <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-rose-600"></span>Lewat threshold</span>
          <span class="inline-flex items-center gap-1"><span class="h-0.5 w-5 bg-amber-500"></span>Threshold</span>
        </div>
      </div>

      <div class="grid gap-4 p-4">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
          @foreach($rows as $row)
            @php
              $ind = $row['indicator'];
              $value = (float) ($row['total'] ?? $row['value'] ?? 0);
              $threshold = $parseThreshold($row['threshold'] ?? null);
              $isOver = $threshold !== null && $value > $threshold;
              $meterMax = max(1, $value, $threshold ?? 0);
              $valuePct = min(100, max(0, ($value / $meterMax) * 100));
              $thresholdPct = $threshold === null ? null : min(100, max(0, ($threshold / $meterMax) * 100));
              $unitText = trim((string) ($ind->unit ?? ''));
            @endphp
            <div class="indicator-card">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="truncate text-[15px] font-semibold text-coal-900" title="{{ $ind->name }}">{{ $ind->name }}</div>
                  @if($ind->is_derived && $ind->formula)
                    <div class="mt-0.5 truncate font-mono text-[11px] text-coal-500" title="{{ $ind->formula }}">= {{ $ind->formula }}</div>
                  @endif
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $isOver ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">
                  {{ $threshold === null ? 'No target' : ($isOver ? 'Lewat' : 'Aman') }}
                </span>
              </div>
              <div class="kpi-meta mt-3 text-xs">
                <div>
                  <div class="text-coal-500">Total</div>
                  <div class="mt-0.5 truncate text-lg font-bold leading-none {{ $isOver ? 'text-rose-600' : 'text-coal-900' }}">
                    {{ $fmt($value) }}@if($unitText) <span class="text-xs font-semibold text-coal-500">{{ $unitText }}</span>@endif
                  </div>
                </div>
                <div>
                  <div class="text-coal-500">Threshold</div>
                  <div class="mt-0.5 truncate text-lg font-bold leading-none text-coal-900">{{ $fmtThreshold($row['threshold'] ?? null) }}</div>
                </div>
              </div>

              <div class="mt-4 py-1">
                <div class="kpi-meter" aria-label="Total {{ $fmt($value) }} threshold {{ $fmtThreshold($row['threshold'] ?? null) }}">
                  <div class="kpi-meter__bar {{ $isOver ? 'is-over' : '' }}" style="width: {{ $valuePct }}%"></div>
                  @if($thresholdPct !== null)
                    <span class="kpi-meter__threshold" style="left: {{ $thresholdPct }}%" data-label="{{ $fmtThreshold($row['threshold'] ?? null) }}"></span>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-coal-500">
              <tr>
                <th class="px-3 py-2 text-left">Indicator</th>
                <th class="px-3 py-2 text-right">Total</th>
                <th class="px-3 py-2 text-right">Threshold</th>
                <th class="px-3 py-2 text-left">Unit</th>
                <th class="px-3 py-2 text-left">Status</th>
                @if($isSuperAdmin)
                  <th class="px-3 py-2 text-center">Aksi</th>
                @endif
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach($rows as $row)
                @php
                  $ind = $row['indicator'];
                  $value = (float) ($row['total'] ?? $row['value'] ?? 0);
                  $threshold = $parseThreshold($row['threshold'] ?? null);
                  $isOver = $threshold !== null && $value > $threshold;
                  $editTotalParams = [
                    'indicator_id' => $ind->id,
                    'group_code' => $g->code,
                    'scope' => $scopeNow,
                    'date' => request('date'),
                    'week' => request('week'),
                    'month' => request('month'),
                    'year' => request('year'),
                    'site_id' => $siteId,
                  ];
                @endphp
                <tr class="hover:bg-gray-50">
                  <td class="px-3 py-2">
                    <div class="font-medium text-coal-900">{{ $ind->name }}</div>
                    @if($ind->is_derived && $ind->formula)
                      <div class="mt-0.5 font-mono text-xs text-coal-500">= {{ $ind->formula }}</div>
                    @endif
                  </td>
                  <td class="px-3 py-2 text-right font-bold {{ $isOver ? 'text-rose-600' : 'text-coal-900' }}">
                    @if($isSuperAdmin && $siteId)
                      <a href="{{ route('admin.report-totals.edit', $editTotalParams) }}" class="underline decoration-dotted underline-offset-2 hover:text-maroon-700">{{ $fmt($value) }}</a>
                    @else
                      {{ $fmt($value) }}
                    @endif
                  </td>
                  <td class="px-3 py-2 text-right font-mono">{{ $fmtThreshold($row['threshold'] ?? null) }}</td>
                  <td class="px-3 py-2">{{ trim((string) ($ind->unit ?? '')) ?: '-' }}</td>
                  <td class="px-3 py-2">
                    @if($threshold === null)
                      <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">Tanpa threshold</span>
                    @elseif($isOver)
                      <span class="rounded-full bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700">Lewat</span>
                    @else
                      <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Aman</span>
                    @endif
                  </td>
                  @if($isSuperAdmin)
                    <td class="px-3 py-2 text-center">
                      <div class="flex flex-col items-center gap-1">
                        <a href="{{ route('admin.indicators.edit', $ind->id) }}" class="rounded-md border border-maroon-500 px-2.5 py-1 text-xs font-semibold text-maroon-700 hover:bg-maroon-50">Edit Indikator</a>
                        @if($siteId)
                          <a href="{{ route('admin.report-totals.edit', $editTotalParams) }}" class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit Total</a>
                        @endif
                      </div>
                    </td>
                  @endif
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>
  @endforeach
</div>
@endsection
