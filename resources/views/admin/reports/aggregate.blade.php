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

  {{-- On-time Total --}}
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">On-time Total</div>
    <div class="text-xl font-bold text-emerald-600">
      {{ number_format($totalOntime ?? 0, 0, ',', '.') }}
    </div>
  </div>

  {{-- Late Total --}}
  <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
    <div class="text-xs">Late Total</div>
    <div class="text-xl font-bold text-rose-600">
      {{ number_format($totalLate ?? 0, 0, ',', '.') }}
    </div>
  </div>
</div>

{{-- === METER: 1 indikator = 1 gauge semicircle kecil === --}}
@foreach($groups as $g)
  <div class="mb-2 text-sm font-semibold text-maroon-700">{{ $g->name }}</div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
    @foreach(($data[$g->code] ?? []) as $row)
      @php
        $ind      = $row['indicator'];
        $label    = $ind->name;
        $unit     = $ind->unit ?? '';
        $value    = is_numeric($row['total'] ?? null) ? (float)$row['total'] : 0.0;
        $thrRaw   = $row['threshold'] ?? null;
        $thrVal   = is_numeric($thrRaw) ? (float)$thrRaw : null;
      @endphp

      <div class="p-4 border rounded-lg bg-white dark:bg-coal-900">
        <div class="flex items-center justify-between mb-2">
          <div class="font-medium">{{ $label }}</div>
          <div class="text-xs text-gray-500">
            @if($thrVal !== null)
              @php
                $tBase   = number_format(max(0,$thrVal), ($ind->data_type==='int'?0:2), ',', '.');
                $tLabel  = trim((string)$unit) === '%' ? ($tBase.'%') : ($tBase.($unit ? ' '.$unit : ''));
              @endphp
              Target: {{ $tLabel }}
            @endif
          </div>
        </div>

        {{-- meter kecil: dirender oleh JS berdasarkan data-* --}}
        <div
          class="mini-gauge w-full"
          style="height: 120px"
          data-label="{{ $label }}"
          data-unit="{{ $unit }}"
          data-value="{{ $value }}"
          @if($thrVal !== null) data-threshold="{{ $thrVal }}" @endif
          data-dtype="{{ ($ind->data_type ?? 'int') === 'int' ? 'int' : 'float' }}"
        ></div>
      </div>
    @endforeach
  </div>
@endforeach

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
          <th class="px-3 py-2 w-28 text-right">Threshold</th>
          <th class="px-3 py-2 w-24 text-left">Unit</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach(($data[$g->code] ?? []) as $row)
          @php
            $ind      = $row['indicator'];
            $fmt      = $ind->data_type === 'int' ? 0 : 2;
            $onVal    = is_numeric($row['on_time'] ?? null) ? (float)$row['on_time'] : 0.0;
            $lateVal  = is_numeric($row['late'] ?? null)    ? (float)$row['late']    : 0.0;
            $totalVal = is_numeric($row['total'] ?? null)   ? (float)$row['total']   : ($onVal + $lateVal);
            $thrRaw   = $row['threshold'] ?? null;
            $thrVal   = is_numeric($thrRaw) ? (float)$thrRaw : null;
            $isOver   = $thrVal !== null && $totalVal > $thrVal;
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
              {{ number_format($onVal, $fmt, ',', '.') }}
            </td>
            <td class="px-3 py-2 text-right font-semibold text-rose-600 dark:text-rose-400">
              {{ number_format($lateVal, $fmt, ',', '.') }}
            </td>
            <td class="px-3 py-2 text-right font-bold {{ $isOver ? 'text-rose-600 dark:text-rose-400' : '' }}">
              {{ number_format($totalVal, $fmt, ',', '.') }}
            </td>

            {{-- Threshold: 0/0% fallback + tampilkan % bila unit persen --}}
            <td class="px-3 py-2 text-right text-sm text-gray-700 dark:text-gray-300">
              @php
                // fallback universal ke 0 bila null/empty/'-'
                $thrClean = ($thrVal === null ? 0 : max(0, $thrVal));
                $thrBase  = number_format($thrClean, $fmt, ',', '.');
                $thrDisp  = trim((string)$ind->unit) === '%' ? ($thrBase.'%') : $thrBase;
              @endphp
              {{ $thrDisp }}
            </td>

            {{-- Unit: jika persen, tampil '-' agar tidak dobel dengan % di angka --}}
            <td class="px-3 py-2">
              {{ trim((string)$ind->unit) === '%' ? '-' : ($ind->unit ?? '-') }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endforeach
@endsection

@push('scripts')
<script>
(() => {
  const isDark = document.documentElement.classList.contains('dark');
  const txt    = isDark ? '#e5e7eb' : '#1f2937';

  // ===== Utilities
  const clamp = (x,a,b)=> Math.max(a, Math.min(b,x));
  const fmtNum = (v, dtype='int') => {
    const nf0 = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
    const nf2 = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return dtype==='int' ? nf0.format(v) : nf2.format(v);
  };

  // rotate hue (simple RGB→HSL→RGB) to vary colors per card
  const rotHue = (hex, deg) => {
    const n = hex.replace('#','');
    const r = parseInt(n.slice(0,2),16)/255;
    const g = parseInt(n.slice(2,4),16)/255;
    const b = parseInt(n.slice(4,6),16)/255;
    const max=Math.max(r,g,b), min=Math.min(r,g,b);
    let h,s,l=(max+min)/2;
    if (max===min){ h=s=0; }
    else {
      const d=max-min;
      s = l>0.5 ? d/(2-max-min) : d/(max+min);
      switch(max){
        case r: h=(g-b)/d + (g<b?6:0); break;
        case g: h=(b-r)/d + 2; break;
        case b: h=(r-g)/d + 4; break;
      }
      h/=6;
    }
    h = (h*360 + deg) % 360; if (h<0) h+=360;

    // HSL->RGB
    const hue2rgb = (p,q,t)=>{ if(t<0)t+=1; if(t>1)t-=1;
      if(t<1/6)return p+(q-p)*6*t;
      if(t<1/2)return q;
      if(t<2/3)return p+(q-p)*(2/3-t)*6;
      return p; };
    let r2,g2,b2;
    if (s===0){ r2=g2=b2=l; }
    else {
      const q=l<0.5 ? l*(1+s) : l+s-l*s, p=2*l-q;
      r2=hue2rgb(p,q,(h/360)+1/3);
      g2=hue2rgb(p,q,(h/360));
      b2=hue2rgb(p,q,(h/360)-1/3);
    }
    const toHex = x=> ('0'+Math.round(x*255).toString(16)).slice(-2);
    return '#'+toHex(r2)+toHex(g2)+toHex(b2);
  };

  // Build one gauge SVG
  function renderGauge(el, idx) {
    const label = el.dataset.label || 'Indicator';
    const unit  = el.dataset.unit || '';
    const value = Number(el.dataset.value || 0);
    const thr   = (el.dataset.threshold !== undefined) ? Number(el.dataset.threshold) : null;
    const dtype = el.dataset.dtype || 'int';

    // size
    const W = Math.max(el.clientWidth || 260, 220);
    const H = Math.max(parseInt(getComputedStyle(el).height,10) || 120, 90);

    // scale based on threshold
    const min = 0;
    const max = (thr && thr>0) ? thr*1.2 : Math.max(value, 1) * 1.1;

    // zones from fractions (based on threshold)
    const fracs = [0.60, 0.80, 0.90, 1.00, 1.20];
    const edges = [min, ...(thr? fracs.map(f => Math.max(min, thr*f)) : [0.6,0.8,0.9,1.0].map(f => f*max)), max];

    // colorful zones (rotate palette by idx)
    const base = ['#ef4444','#f59e0b','#facc15','#84cc16','#22c55e'];
    const deg  = (idx*25) % 360;
    const zonesColor = base.map(c => rotHue(c, deg));

    // geometry
    const cx = W/2, cy = H-8;
    const R  = Math.min(W, H*2)/2 - 10;

    const toAng = v => -90 + 180 * ((v-min)/(max-min));
    const polar = (ang, r)=> {
      const rad = ang*Math.PI/180;
      return [cx + r*Math.cos(rad), cy + r*Math.sin(rad)];
    };
    const arcPath = (a0,a1,r) => {
      const [x0,y0] = polar(a0,r), [x1,y1] = polar(a1,r);
      const large = (Math.abs(a1-a0)>180)?1:0;
      return `M ${x0} ${y0} A ${r} ${r} 0 ${large} 1 ${x1} ${y1}`;
    };

    // compose segments
    let segs = '';
    for (let i=0;i<5;i++){
      const a0 = toAng(edges[i]);
      const a1 = toAng(edges[i+1]);
      segs += `<path d="${arcPath(a0,a1,R)}" stroke="${zonesColor[i]}" stroke-width="14" fill="none" />`;
    }

    // threshold tick
    let thrTick = '';
    if (!Number.isNaN(thr) && thr!==null) {
      const ath = toAng(clamp(thr,min,max));
      const rIn=R-16, rOut=R+2;
      const [x0,y0]=polar(ath,rIn), [x1,y1]=polar(ath,rOut);
      thrTick = `<line x1="${x0}" y1="${y0}" x2="${x1}" y2="${y1}" stroke="${isDark?'#fbbf24':'#d97706'}" stroke-width="2.4" stroke-dasharray="4 4"/>`;
    }

    // needle
    const ratio = clamp((value-min)/(max-min), 0, 1);
    const ang   = -90 + 180*ratio;
    const len   = R*0.76;
    const [nx,ny] = polar(ang,len);
    const meet  = (thr!==null && !Number.isNaN(thr)) ? (value >= thr) : null;
    const needleColor = (meet===false) ? (isDark?'#f87171':'#dc2626') : (isDark?'#cbd5e1':'#334155');

    const needle = `
      <line x1="${cx}" y1="${cy}" x2="${nx}" y2="${ny}"
            stroke="${needleColor}" stroke-width="4" stroke-linecap="round"/>
      <circle cx="${cx}" cy="${cy}" r="6.5" fill="${isDark?'#cbd5e1':'#334155'}"/>
    `;

    // labels small (Poor..Best) — compact
    const lbls = ['Poor','Average','Fair','Good','Best'];
    const labR = R-30;
    let labTxt = '';
    for (let i=0;i<5;i++){
      const aMid = (toAng(edges[i])+toAng(edges[i+1]))/2;
      const [tx,ty]=polar(aMid, labR);
      labTxt += `<text x="${tx}" y="${ty}" font-size="10" text-anchor="middle" dominant-baseline="middle" fill="${txt}" style="font-weight:600">${lbls[i]}</text>`;
    }

    // center values
    const valTxt  = fmtNum(value, dtype) + (unit ? ' '+unit : '');
    const thrTxt  = (thr!==null && !Number.isNaN(thr)) ? 'Threshold: '+fmtNum(thr, dtype)+(unit==='%' ? '%' : (unit? ' '+unit : '')) : '';
    const center = `
      <text x="${cx}" y="${cy-10}" text-anchor="middle" font-size="16" fill="${txt}" style="font-weight:700">${valTxt}</text>
      ${ thrTxt ? `<text x="${cx}" y="${cy+8}" text-anchor="middle" font-size="11" fill="${isDark?'#a3a3a3':'#6b7280'}">${thrTxt}</text>` : '' }
    `;

    // svg
    const svg = `
      <svg width="${W}" height="${H}" viewBox="0 0 ${W} ${H}" role="img" aria-label="Gauge ${label}">
        <defs>
          <filter id="sg${idx}" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="2" stdDeviation="2" flood-opacity=".18"/>
          </filter>
        </defs>
        <g filter="url(#sg${idx})">${segs}</g>
        ${thrTick}
        ${needle}
        ${labTxt}
        ${center}
      </svg>
      ${ meet!==null ? `<div style="margin-top:2px;font-size:11px;display:inline-flex;align-items:center;gap:6px;color:${meet? (isDark?'#10b981':'#059669'):(isDark?'#fca5a5':'#dc2626')}">
          <span style="display:inline-block;width:8px;height:8px;border-radius:9999px;background:${meet? (isDark?'#10b981':'#10b981'):(isDark?'#ef4444':'#ef4444')}"></span>
          ${meet? '≥ threshold' : '&lt; threshold'}
        </div>` : '' }
    `;

    el.innerHTML = svg;
  }

  // render all
  const items = Array.from(document.querySelectorAll('.mini-gauge'));
  items.forEach((el, i) => renderGauge(el, i));
})();
</script>
@endpush
