{{-- resources/views/admin/reports/aggregate.blade.php --}}
@extends('layouts.app')
@section('title', 'Rekap')

@section('content')
@php
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
$u = Auth::user();
$isSuperAdmin = $u?->isSuperAdmin() ?? false;
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
$categoryCharts = [
    'lagging' => ['title' => 'Lagging Indicators', 'rows' => [], 'total' => 0, 'over' => 0],
    'leading' => ['title' => 'Leading Indicators', 'rows' => [], 'total' => 0, 'over' => 0],
    'base' => ['title' => 'Deskripsi (Base Metrics)', 'rows' => [], 'total' => 0, 'over' => 0],
];
$detailRows = [];
$grandTotal = 0;
$totalIndicators = 0;
$overTargetCount = 0;
foreach ($groups as $g) {
    $rows = collect($data[$g->code] ?? []);
    foreach ($rows as $row) {
        $ind = $row['indicator'];
        $value = (float) ($row['total'] ?? $row['value'] ?? 0);
        $threshold = $parseThreshold($row['threshold'] ?? null);
        $isOver = $threshold !== null && $value > $threshold;
        $groupCode = strtoupper((string) ($g->code ?? ''));
        $haystack = strtolower(trim(($groupCode.' '.($g->name ?? '').' '.($ind->name ?? '').' '.($ind->code ?? ''))));
        $category = match (true) {
            $groupCode === 'BASE' || str_contains($haystack, 'base') || str_contains($haystack, 'deskripsi') || str_contains($haystack, 'description') => 'base',
            $groupCode === 'FATAL_LTI' || $groupCode === 'LAG' || str_contains($haystack, 'fatality') || str_contains($haystack, 'lagging') || str_contains($haystack, 'lost time') || str_contains($haystack, 'injury') || str_contains($haystack, 'damage') || str_contains($haystack, 'accident') || str_contains($haystack, 'near miss') => 'lagging',
            default => 'leading',
        };
        $meterMax = max(1, $value, $threshold ?? 0);
        $chartRow = [
            'group' => $g,
            'indicator' => $ind,
            'value' => $value,
            'threshold' => $threshold,
            'thresholdLabel' => $fmtThreshold($row['threshold'] ?? null),
            'unit' => trim((string) ($ind->unit ?? '')),
            'weight' => $ind->weight,
            'isOver' => $isOver,
            'valuePct' => min(100, max(0, ($value / $meterMax) * 100)),
            'thresholdPct' => $threshold === null ? null : min(100, max(0, ($threshold / $meterMax) * 100)),
        ];
        $categoryCharts[$category]['rows'][] = $chartRow;
        $categoryCharts[$category]['total'] += $value;
        if ($isOver) $categoryCharts[$category]['over']++;
        $detailRows[] = $chartRow;
        $grandTotal += $value;
        $totalIndicators++;
        if ($isOver) $overTargetCount++;
    }
}
$categoryChartPayload = [];
foreach ($categoryCharts as $key => $chart) {
    $categoryChartPayload[$key] = [
        'title' => $chart['title'],
        'rows' => collect($chart['rows'])->map(fn($item) => [
            'id' => $item['indicator']->id,
            'label' => $item['indicator']->name,
            'formula' => $item['indicator']->is_derived ? (string) $item['indicator']->formula : '',
            'value' => (float) $item['value'],
            'threshold' => $item['threshold'],
            'thresholdLabel' => $item['thresholdLabel'],
            'unit' => $item['unit'],
            'weight' => $item['weight'],
            'isOver' => $item['isOver'],
        ])->values(),
    ];
}
@endphp
<x-ui.page>
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
        <x-ui.page-title>Rekap {{ $periodSafe }}</x-ui.page-title>
        <x-ui.page-description>Rekap kinerja operasional berdasarkan site dan periode yang dipilih.</x-ui.page-description>
      </div></div>
  <x-ui.page-actions></x-ui.page-actions>

<x-slot:filters>
<x-ui.table-toolbar x-data="{ scope: '{{ $scopeNow }}' }">
        <input type="hidden" name="scope" :value="scope">
        <div class="ui-filter-field"><label class="ui-filter-field__label" for="aggregate-site">Site</label><select id="aggregate-site" name="site_id" class="w-full ui-input">
          <option value="">Semua Site</option>
          @foreach($sites as $s)
            <option value="{{ $s->id }}" @selected(($siteId ?? null) == $s->id)>{{ $s->code }} - {{ $s->name }}</option>
          @endforeach
        </select></div>
        <div class="ui-filter-field"><label class="ui-filter-field__label" for="aggregate-scope">Periode</label><select id="aggregate-scope" x-model="scope" class="w-full ui-input">
          <option value="day">Harian</option>
          <option value="week">Mingguan</option>
          <option value="month">Bulanan</option>
          <option value="year">Tahunan</option>
        </select></div>
        <div class="ui-filter-field" x-show="scope === 'day'"><label class="ui-filter-field__label" for="aggregate-date">Tanggal</label><input id="aggregate-date" type="date" name="date" value="{{ $dateObj->toDateString() }}" class="w-full ui-input"></div>
        <div class="ui-filter-field" x-show="scope === 'week'"><label class="ui-filter-field__label" for="aggregate-week">Minggu</label><input id="aggregate-week" type="number" name="week" value="{{ $weekVal }}" min="1" max="53" class="w-full ui-input"></div>
        <div class="ui-filter-field" x-show="scope === 'week'"><label class="ui-filter-field__label" for="aggregate-week-year">Tahun</label><input id="aggregate-week-year" type="number" name="year" value="{{ $yearVal }}" class="w-full ui-input"></div>
        <div class="ui-filter-field" x-show="scope === 'month'"><label class="ui-filter-field__label" for="aggregate-month">Bulan</label><select id="aggregate-month" name="month" class="w-full ui-input">
            @for($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" @selected($monthVal == $m)>{{ $m }}</option>
            @endfor
          </select></div>
        <div class="ui-filter-field" x-show="scope === 'month'"><label class="ui-filter-field__label" for="aggregate-month-year">Tahun</label><input id="aggregate-month-year" type="number" name="year" value="{{ $yearVal }}" class="w-full ui-input"></div>
        <div class="ui-filter-field" x-show="scope === 'year'"><label class="ui-filter-field__label" for="aggregate-year">Tahun</label><input id="aggregate-year" type="number" name="year" value="{{ $yearVal }}" class="w-full ui-input"></div>

<x-slot:actions>
<x-ui.button variant="primary" >Terapkan</x-ui.button>

</x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>

<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5"><x-ui.metric-card label="Grup" :value="$groups->count()" /><x-ui.metric-card label="Indikator" :value="$totalIndicators" /><x-ui.metric-card label="Melewati Threshold" :value="$overTargetCount" icon="alert-triangle" /><x-ui.metric-card label="Grand Total" :value="$grandTotal" /><x-ui.metric-card label="Site" :value="$selectedSite ? $selectedSite->code.' — '.$selectedSite->name : 'Semua site'" /></div>
  <div class="grid gap-4 xl:grid-cols-3">
    @foreach($categoryCharts as $key => $chart)
      @php
        $rows = collect($chart['rows']);
        $maxTotal = max(1, (float) $rows->max('value'), (float) $rows->max('threshold'));
      @endphp
      <section class="report-card overflow-hidden">
        <div class="flex items-start justify-between gap-3 border-b px-4 py-3">
          <div>
            <x-ui.section-title class="font-semibold text-coal-900">{{ $chart['title'] }}</x-ui.section-title>
            <p class="text-xs text-coal-500">{{ $rows->count() }} indikator dalam satu grafik</p>
          </div>
          <div class="text-right">
            <div class="text-xs text-coal-500">Over</div>
            <div class="text-lg font-bold {{ $chart['over'] ? 'text-rose-600' : 'text-emerald-600' }}">{{ $chart['over'] }}</div>
          </div>
        </div>
        <div class="p-4">
          @if($rows->isNotEmpty())
            <div class="category-chart">
              @foreach($rows as $item)
                @php
                  $ind = $item['indicator'];
                  $value = (float) $item['value'];
                  $threshold = $item['threshold'];
                @endphp
                <div class="mini-chart-card">
                  <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                      <div class="truncate text-xs font-semibold text-coal-900" title="{{ $ind->name }}">{{ $ind->name }}</div>
                      @if($ind->is_derived && $ind->formula)
                        <div class="truncate font-mono text-[10px] text-coal-500" title="{{ $ind->formula }}">= {{ $ind->formula }}</div>
                      @endif
                    </div>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $item['isOver'] ? 'ui-status ui-status--danger' : 'ui-status ui-status--success' }}">
                      {{ $threshold === null ? 'No target' : ($item['isOver'] ? 'Lewat' : 'Aman') }}
                    </span>
                  </div>
                  <div class="mt-2 flex items-center justify-between text-[11px]">
                    <span class="font-bold {{ $item['isOver'] ? 'text-rose-600' : 'text-coal-900' }}">Total {{ $fmt($value) }}</span>
                    <span class="font-semibold text-amber-700">Th {{ $item['thresholdLabel'] }}</span>
                  </div>
                  @if($item['weight'] !== null)
                    <div class="mt-1 text-[11px] font-semibold text-coal-500">Bobot {{ rtrim(rtrim(number_format((float) $item['weight'], 2, ',', '.'), '0'), ',') }}%</div>
                  @endif
                  <div class="mini-chart mt-2">
                    <canvas id="indicator_chart_{{ $ind->id }}" aria-label="{{ $ind->name }}"></canvas>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="rounded-lg border border-dashed border-gray-200 p-4 text-center text-sm text-coal-500">Belum ada indikator.</div>
          @endif
        </div>
      </section>
    @endforeach
  </div>
  <section class="report-card overflow-hidden">
    <div class="flex flex-col gap-2 border-b px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <x-ui.section-title class="font-semibold text-coal-900">Detail Indikator</x-ui.section-title>
        <p class="text-xs text-coal-500">Data lengkap tetap tersedia tanpa membuat grafik memanjang.</p>
      </div>
      <div class="flex items-center gap-3 text-xs">
        <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-[#1D4ED8]"></span>Normal</span>
        <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-rose-600"></span>Lewat threshold</span>
        <span class="inline-flex items-center gap-1"><span class="h-3 w-0.5 bg-amber-500"></span>Threshold</span>
      </div>
    </div>
    <div class="overflow-x-auto">
      <x-ui.table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs normal-case text-coal-500">
          <tr>
            <th class="px-3 py-2 text-left">Kategori</th>
            <th class="px-3 py-2 text-left">Indicator</th>
            <th class="px-3 py-2 text-right">Total</th>
            <th class="px-3 py-2 text-right">Threshold</th>
            <th class="px-3 py-2 text-right">Bobot</th>
            <th class="px-3 py-2 text-left">Unit</th>
            <th class="px-3 py-2 text-left">Status</th>
            @if($isSuperAdmin)
              <th class="px-3 py-2 text-center">Aksi</th>
            @endif
          </tr>
        </thead>
        <tbody class="divide-y">
          @foreach($detailRows as $item)
            @php
              $g = $item['group'];
              $ind = $item['indicator'];
              $value = (float) $item['value'];
              $threshold = $item['threshold'];
              $isOver = $item['isOver'];
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
              <td class="px-3 py-2 text-xs font-semibold text-coal-500">{{ $g->name }}</td>
              <td class="px-3 py-2">
                <div class="font-medium text-coal-900">{{ $ind->name }}</div>
                @if($ind->is_derived && $ind->formula)
                  <div class="mt-1 font-mono text-xs text-coal-500">= {{ $ind->formula }}</div>
                @endif
              </td>
              <td class="px-3 py-2 text-right font-bold {{ $isOver ? 'text-rose-600' : 'text-coal-900' }}">
                @if($isSuperAdmin && $siteId)
                  <a href="{{ route('admin.report-totals.edit', $editTotalParams) }}" class="underline decoration-dotted underline-offset-2 hover:text-maroon-700">{{ $fmt($value) }}</a>
                @else
                  {{ $fmt($value) }}
                @endif
              </td>
              <td class="px-3 py-2 text-right font-mono">{{ $item['thresholdLabel'] }}</td>
              <td class="px-3 py-2 text-right font-mono">{{ $item['weight'] !== null ? rtrim(rtrim(number_format((float) $item['weight'], 2, ',', '.'), '0'), ',') . '%' : '-' }}</td>
              <td class="px-3 py-2">{{ $item['unit'] ?: '-' }}</td>
              <td class="px-3 py-2">
                @if($threshold === null)
                  <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">Tanpa threshold</span>
                @elseif($isOver)
                  <x-ui.badge variant="danger" class="rounded-full px-2 py-1 text-xs font-semibold">Lewat</x-ui.badge>
                @else
                  <x-ui.badge variant="success" class="rounded-full px-2 py-1 text-xs font-semibold">Aman</x-ui.badge>
                @endif
              </td>
              @if($isSuperAdmin)
                <td class="ui-action-cell px-3 py-2 text-center"><x-ui.table-actions>
                  <div class="flex flex-row items-center gap-2">
                    <x-ui.icon-button data-modal-form action="edit" label="Edit indikator" href="{{ route('admin.indicators.edit', $ind->id) }}" />
                    @if($siteId)
                      <x-ui.icon-button action="edit" label="Edit total" href="{{ route('admin.report-totals.edit', $editTotalParams) }}" />
                    @endif
                  </div>
                </x-ui.table-actions></td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </x-ui.table>
    </div>
  </section>
</x-ui.page>
@endsection
@push('scripts')
<script>
window.addEventListener('DOMContentLoaded', function () {
  const charts = @json($categoryChartPayload);
  const nf = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
  function shortText(ctx, text, maxWidth) {
    const value = String(text || '');
    if (ctx.measureText(value).width <= maxWidth) return value;
    let out = value;
    while (out.length > 4 && ctx.measureText(out + '...').width > maxWidth) {
      out = out.slice(0, -1);
    }
    return out + '...';
  }
  function drawIndicatorChart(canvas, row) {
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    const width = Math.max(150, Math.round(rect.width));
    const height = Math.max(92, Math.round(rect.height));
    const ctx = canvas.getContext('2d');
    canvas.width = Math.round(width * dpr);
    canvas.height = Math.round(height * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, width, height);
    const value = Number(row.value || 0);
    const threshold = row.threshold === null || row.threshold === undefined ? null : Number(row.threshold);
    const maxValue = Math.max(1, value, Number.isFinite(threshold) ? threshold : 0);
    const left = 30;
    const right = 10;
    const top = 12;
    const bottom = 22;
    const plotW = Math.max(80, width - left - right);
    const plotH = Math.max(48, height - top - bottom);
    const x1 = left;
    const x2 = left + plotW;
    const yAt = (value) => top + plotH - (Math.max(0, Number(value || 0)) / maxValue) * plotH;
    ctx.strokeStyle = 'rgba(15, 23, 42, .10)';
    ctx.lineWidth = 1;
    ctx.fillStyle = '#64748b';
    ctx.font = '500 9px system-ui,-apple-system,Segoe UI,Roboto,Arial';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    for (let i = 0; i <= 2; i++) {
      const gridValue = (maxValue / 2) * i;
      const y = yAt(gridValue);
      ctx.beginPath();
      ctx.moveTo(left, y);
      ctx.lineTo(left + plotW, y);
      ctx.stroke();
      ctx.fillText(nf.format(gridValue), left - 5, y);
    }
    ctx.strokeStyle = '#cbd5e1';
    ctx.beginPath();
    ctx.moveTo(left, top);
    ctx.lineTo(left, top + plotH);
    ctx.lineTo(left + plotW, top + plotH);
    ctx.stroke();
    drawFlatLine(value, row.isOver ? '#dc2626' : '#1D4ED8', false, 3);
    if (threshold !== null && Number.isFinite(threshold)) {
      drawFlatLine(threshold, '#f59e0b', true, 2);
    }
    ctx.font = '600 9px system-ui,-apple-system,Segoe UI,Roboto,Arial';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'top';
    ctx.fillStyle = row.isOver ? '#dc2626' : '#6b4f34';
    ctx.fillText('Total', x1, height - 14);
    ctx.fillStyle = '#92400e';
    ctx.fillText('Threshold', x1 + 48, height - 14);
    function drawFlatLine(lineValue, color, dashed, lineWidth) {
      const y = yAt(lineValue);
      ctx.save();
      ctx.strokeStyle = color;
      ctx.lineWidth = lineWidth;
      ctx.lineCap = 'round';
      if (dashed) ctx.setLineDash([6, 5]);
      ctx.beginPath();
      ctx.moveTo(x1, y);
      ctx.lineTo(x2, y);
      ctx.stroke();
      ctx.restore();
    }
  }
  function roundRect(ctx, x, y, width, height, radius) {
    const r = Math.min(radius, width / 2, height / 2);
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + width, y, x + width, y + height, r);
    ctx.arcTo(x + width, y + height, x, y + height, r);
    ctx.arcTo(x, y + height, x, y, r);
    ctx.arcTo(x, y, x + width, y, r);
    ctx.closePath();
  }
  function renderCharts() {
    Object.entries(charts).forEach(([key, config]) => {
      const rows = Array.isArray(config.rows) ? config.rows : [];
      rows.forEach((row) => {
        const canvas = document.getElementById('indicator_chart_' + row.id);
        if (canvas) drawIndicatorChart(canvas, row);
      });
    });
  }
  renderCharts();
  window.addEventListener('resize', renderCharts);
});
</script>
@endpush
