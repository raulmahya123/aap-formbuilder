@extends('layouts.app')

@section('content')
<div
  x-data="dash()"
  x-init="init()"
  class="bg-ivory-100 min-h-screen text-coal-800"
  id="dash"
  data-url-summary="{{ route('admin.dashboard.data.summary') }}"
  data-url-entries="{{ route('admin.dashboard.data.entries_by_day') }}"
  data-url-top="{{ route('admin.dashboard.data.top_forms') }}"
  data-url-group="{{ route('admin.dashboard.data.by_aggregate') }}"  {{-- NEW --}}
>
  <!-- HEADER -->
  <div class="max-w-7xl mx-auto p-4 sm:p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4 sm:mb-6">
      <div>
        <h1 class="text-2xl md:text-3xl font-serif tracking-tight">Dashboard</h1>
        <p class="text-coal-500 text-sm">Ringkasan aktivitas formulir &amp; entri.</p>
      </div>
      <div class="flex flex-col sm:flex-row w-full sm:w-auto gap-2">
        <a class="px-3 py-2 rounded-lg border border-maroon-600 text-maroon-700 hover:bg-maroon-50/60 transition text-center"
           :href="exportHref()">
          ⬇️ Export CSV
        </a>
      </div>
    </div>

    <!-- FILTERS TOOLBAR -->
    <form @submit.prevent="reloadAll()"
          class="rounded-2xl border bg-ivory-50 p-4 sm:p-5 shadow-soft mb-4 sm:mb-6">
      <div class="grid gap-4 sm:gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-6">
        <div class="lg:col-span-2">
          <label class="text-xs font-medium text-coal-600">Department</label>
          <select x-model="filters.department_id"
                  class="mt-1 border rounded-lg w-full px-3 py-2 bg-white">
            <option value="">— Semua —</option>
            @foreach($departments as $d)
              <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="lg:col-span-2">
          <label class="text-xs font-medium text-coal-600">Form</label>
          <select x-model="filters.form_id"
                  class="mt-1 border rounded-lg w-full px-3 py-2 bg-white">
            <option value="">— Semua —</option>
            @foreach($forms as $f)
              <option value="{{ $f->id }}">{{ $f->title }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-xs font-medium text-coal-600">Periode</label>
          <div class="grid grid-cols-2 gap-2 mt-1">
            <input type="date" x-model="filters.date_from"
                   class="border rounded-lg w-full px-3 py-2 bg-white">
            <input type="date" x-model="filters.date_to"
                   class="border rounded-lg w-full px-3 py-2 bg-white">
          </div>
        </div>

        <!-- NEW: Grouping -->
        <div>
          <label class="text-xs font-medium text-coal-600">Rekap Berdasarkan</label>
          <select x-model="groupBy" @change="reloadGroupOnly()"
                  class="mt-1 border rounded-lg w-full px-3 py-2 bg-white">
            <option value="department">Department</option>
            <option value="form">Form</option>
            <option value="document">Document (per Template)</option>
          </select>
        </div>
      </div>

      <div class="mt-4 flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-2">
        <button class="w-full sm:w-auto px-4 py-2 bg-maroon-700 text-ivory-50 rounded-lg hover:bg-maroon-600 transition">
          Terapkan
        </button>
        <button type="button" @click="resetFilters()"
                class="w-full sm:w-auto px-4 py-2 border rounded-lg hover:bg-ivory-50 transition">
          Reset
        </button>
        <span class="text-xs text-coal-500 sm:ml-auto" x-show="!loading">Terakhir diperbarui: <span x-text="lastUpdated"></span></span>
      </div>
    </form>

    <!-- PENGATURAN GRAFIK (custom) -->
    <div class="rounded-2xl border bg-ivory-50 p-4 sm:p-5 shadow-soft mb-4 sm:mb-6">
      <div class="grid gap-4 sm:gap-5 grid-cols-1 md:grid-cols-3 lg:grid-cols-6">
        <div>
          <label class="text-xs font-medium text-coal-600">Tipe Tren</label>
          <select x-model="chartOpts.trendType" @change="rebuildLineIfNeeded()"
                  class="mt-1 border rounded-lg w-full px-3 py-2 bg-white">
            <option value="line">Line</option>
            <option value="area">Area</option>
            <option value="bar">Bar</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-medium text-coal-600">Tipe Top</label>
          <select x-model="chartOpts.topType" @change="rebuildBarIfNeeded()"
                  class="mt-1 border rounded-lg w-full px-3 py-2 bg-white">
            <option value="bar">Bar</option>
            <option value="hbar">Horizontal Bar</option>
            <option value="doughnut">Doughnut</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-medium text-coal-600">Smoothing</label>
          <input type="range" min="0" max="0.6" step="0.05" x-model.number="chartOpts.smoothing"
                 @input="updateCharts()" class="mt-2 w-full">
          <div class="text-xs text-coal-500" x-text="chartOpts.smoothing.toFixed(2)"></div>
        </div>

        <div>
          <label class="text-xs font-medium text-coal-600">Stacked</label>
          <select x-model="chartOpts.stacked" @change="updateCharts()"
                  class="mt-1 border rounded-lg w-full px-3 py-2 bg-white">
            <option :value="false">Tidak</option>
            <option :value="true">Ya</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-medium text-coal-600">Tampilkan Nilai</label>
          <select x-model="chartOpts.showValues" @change="updateCharts()"
                  class="mt-1 border rounded-lg w-full px-3 py-2 bg-white">
            <option :value="false">Tidak</option>
            <option :value="true">Ya</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-medium text-coal-600">Top N</label>
          <input type="number" min="3" max="20" x-model.number="chartOpts.topN"
                 @change="reloadAll()" class="mt-1 border rounded-lg w-full px-3 py-2 bg-white">
        </div>
      </div>

      <div class="mt-4 flex gap-2">
        <button type="button" @click="exportChart('chartLine','entries-30hari')"
                class="px-3 py-2 rounded-lg border hover:bg-ivory-50">📤 Export Tren (PNG)</button>
        <button type="button" @click="exportChart('chartBar','top-forms')"
                class="px-3 py-2 rounded-lg border hover:bg-ivory-50">📤 Export Top (PNG)</button>
      </div>
    </div>

    <!-- KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-6 gap-3 sm:gap-4 mb-4 sm:mb-6">
      <template x-for="card in kpiCards" :key="card.key">
        <div class="p-4 bg-ivory-50 border rounded-2xl shadow-soft">
          <div class="flex items-start justify-between">
            <div>
              <div class="text-xs uppercase tracking-wider text-coal-600" x-text="card.label"></div>
              <div class="mt-2">
                <div class="h-8 w-32 rounded animate-pulse bg-ivory-200" x-show="loading"></div>
                <div class="text-2xl font-semibold" x-show="!loading" x-text="formatNumber(summary[card.key] ?? 0)"></div>
              </div>
            </div>
            <div class="p-2 rounded-lg bg-maroon-50 text-maroon-700">
              <span x-html="card.icon"></span>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- CHARTS -->
    <div class="grid lg:grid-cols-3 gap-4 sm:gap-6">
      <!-- Line -->
      <div class="bg-ivory-50 border rounded-2xl p-4 lg:col-span-2 shadow-soft min-w-0">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-semibold">Entries — 30 Hari</h2>
          <div class="text-xs text-coal-500" x-text="entriesByDay.labels?.length ? entriesByDay.labels[0] + ' — ' + entriesByDay.labels[entriesByDay.labels.length-1] : ''"></div>
        </div>
        <div class="relative h-56 sm:h-72 md:h-[320px] min-w-0">
          <div class="absolute inset-0 p-4" x-show="loading">
            <div class="h-full w-full rounded-xl bg-gradient-to-b from-ivory-100 to-ivory-50 animate-pulse"></div>
          </div>
          <canvas id="chartLine" class="w-full h-full"></canvas>
        </div>
      </div>

      <!-- Bar -->
      <div class="bg-ivory-50 border rounded-2xl p-4 shadow-soft min-w-0">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-semibold">Top Forms</h2>
          <span class="text-xs text-coal-500" x-show="!loading" x-text="top.labels?.length + ' item'"></span>
        </div>
        <div class="relative h-56 sm:h-72 md:h-[320px] min-w-0">
          <div class="absolute inset-0 p-4" x-show="loading">
            <div class="h-full w-full rounded-xl bg-gradient-to-b from-ivory-100 to-ivory-50 animate-pulse"></div>
          </div>
          <canvas id="chartBar" class="w-full h-full"></canvas>
        </div>
      </div>
    </div>

    <!-- TABEL REKAP -->
    <div class="bg-ivory-50 border rounded-2xl p-4 sm:p-5 mt-4 sm:mt-6 shadow-soft overflow-x-auto nice-scroll">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Rekap <span x-text="groupByLabel()"></span></h2>
        <div class="text-xs text-coal-500" x-show="!loading" x-text="rows.length + ' baris'"></div>
      </div>

      <template x-if="!loading && rows.length===0">
        <div class="p-8 text-center text-coal-500">
          Tidak ada data untuk filter saat ini.
        </div>
      </template>

      <div x-show="loading" class="space-y-2">
        <div class="h-10 rounded bg-ivory-200 animate-pulse"></div>
        <div class="h-10 rounded bg-ivory-200 animate-pulse"></div>
        <div class="h-10 rounded bg-ivory-200 animate-pulse"></div>
      </div>

      <div x-show="!loading">
        <table class="w-full text-sm min-w-[760px]">
          <thead class="bg-ivory-100 text-coal-700 sticky top-0">
            <tr>
              <template x-for="col in columns" :key="col.key">
                <th class="p-3" :class="col.align==='right' ? 'text-right' : 'text-left'" x-text="col.label"></th>
              </template>
            </tr>
          </thead>
          <tbody>
            <template x-for="row in rows" :key="row.__key">
              <tr class="border-t hover:bg-ivory-100">
                <template x-for="col in columns" :key="col.key">
                  <td class="p-3" :class="col.align==='right' ? 'text-right' : 'text-left'">
                    <span x-text="col.format ? col.format(row[col.key]) : row[col.key]"></span>
                  </td>
                </template>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
  <!-- Tailwind (CDN) - ganti ke Vite saat production -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        screens: { xs:'480px', sm:'640px', md:'768px', lg:'1024px', xl:'1280px' },
        extend: {
          fontFamily: {
            serif: ['Playfair Display','ui-serif','Georgia','serif'],
            sans: ['Inter','ui-sans-serif','system-ui','sans-serif']
          },
          colors: {
            maroon: {50:'#fdf4f5',100:'#fae9ea',200:'#f2cfd2',300:'#e7a8ad',400:'#d6737b',500:'#ba202e',600:'#991a25',700:'#7b1e2b',800:'#551219',900:'#320a0f',950:'#1b0508'},
            coal:   {50:'#f5f5f6',100:'#e7e7e9',200:'#cfcfd3',300:'#a8a8ad',400:'#73737b',500:'#3a3a40',600:'#2f2f34',700:'#252529',800:'#1b1b1f',900:'#121214',950:'#0a0a0b'},
            ivory:  {50:'#ffffff',100:'#fefefe',200:'#f9f9f7',300:'#f2f2ef',400:'#e8e8e2',500:'#deded3'}
          },
          boxShadow: { 'soft': '0 6px 24px rgba(0,0,0,0.06)' }
        }
      }
    }
  </script>
  <style>
    .nice-scroll::-webkit-scrollbar{height:8px;width:8px}
    .nice-scroll::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:8px}
    .nice-scroll::-webkit-scrollbar-track{background:transparent}
    #dash canvas { width:100% !important; height:100% !important; display:block; }
  </style>
@endpush

@push('scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
function dash(){
  const throttle = (fn, ms) => { let t=0,id; return (...a)=>{const n=Date.now(); if(n-t>=ms){t=n;fn(...a)} else {clearTimeout(id); id=setTimeout(()=>{t=Date.now();fn(...a)}, ms-(n-t));}}};
  const pure = (v) => Array.isArray(v) ? v.slice() : (v && typeof v==='object' ? JSON.parse(JSON.stringify(v)) : (v ?? null));

  return {
    // state
    loading: true,
    lastUpdated: '',
    filters: { department_id:'', form_id:'', date_from:'', date_to:'' },
    groupBy: 'department',

    // data
    summary: {},
    entriesByDay: { labels:[], series:[] },
    top: { labels:[], series:[] },

    // dynamic table
    columns: [],
    rows: [],

    // charts
    chartLine: null,
    chartBar: null,
    chartOpts: { trendType:'line', topType:'bar', smoothing:0.35, stacked:false, showValues:false, topN:10 },

    _onWinResize:null, _destroyed:false, _ctrl:null,

    kpiCards: [
      { key:'totalForms', label:'Total Forms', icon:'📄' },
      { key:'activeForms', label:'Active Forms', icon:'✅' },
      { key:'totalEntries', label:'Total Entries', icon:'🧾' },
      { key:'uniqueUsers', label:'Unique Users', icon:'👤' },
      { key:'totalDocuments', label:'Total Documents', icon:'📚' },
      { key:'totalTemplates', label:'Total Templates', icon:'📑' },
    ].map(c => ({...c, icon:`<span class='text-lg'>${c.icon}</span>`})),

    exportHref(){
      const p = new URLSearchParams({...this.filters, topN:this.chartOpts.topN});
      return `{{ route('admin.entries.export') }}?${p.toString()}`;
    },
    formatNumber(n){ return new Intl.NumberFormat('id-ID').format(n ?? 0); },
    chartPalette(){ return { line:'rgba(153,26,37,0.95)', fill:'rgba(186,32,46,0.10)', bar:'rgba(123,30,43,0.85)', grid:'rgba(58,58,64,0.15)', ticks:'#3a3a40' }; },
    groupByLabel(){ return this.groupBy==='department' ? 'per Department' : (this.groupBy==='form' ? 'per Form' : 'per Document (Template)'); },

    async init(){
      Chart.register(ChartDataLabels);
      await this.reloadAll();
      this.initCharts();
      this.bindWindowResize();
      requestAnimationFrame(()=>{ this.chartLine?.resize(); this.chartBar?.resize(); });
      window.addEventListener('beforeunload', () => this.destroy());
    },

    async reloadAll(){
      this.loading = true;
      try { this._ctrl?.abort(); } catch(_) {}
      this._ctrl = new AbortController();

      const el = document.getElementById('dash');
      const p = new URLSearchParams({...this.filters, topN:this.chartOpts.topN});

      const getJSON = (url, extra={}) => {
        const q = new URLSearchParams({...Object.fromEntries(p), ...extra});
        return fetch(url + '?' + q.toString(), { signal:this._ctrl.signal }).then(r=>{ if(!r.ok) throw new Error(r.statusText); return r.json();});
      };

      try{
        const [sum, ent, top, grp] = await Promise.all([
          getJSON(el.dataset.urlSummary),
          getJSON(el.dataset.urlEntries),
          getJSON(el.dataset.urlTop),
          getJSON(el.dataset.urlGroup, { group:this.groupBy }),
        ]);

        this.summary = sum ?? {};
        this.entriesByDay = { labels:Array.isArray(ent?.labels)?ent.labels:[], series:Array.isArray(ent?.series)?ent.series:[] };

        const labs = Array.isArray(top?.labels)?top.labels:[];
        const vals = Array.isArray(top?.series)?top.series:[];
        const N = Math.min(this.chartOpts.topN||10, labs.length);
        this.top = { labels: labs.slice(0,N), series: vals.slice(0,N) };

        // table
        this.columns = Array.isArray(grp?.columns) ? grp.columns : [];
        this.rows = Array.isArray(grp?.rows) ? grp.rows : [];

        this.lastUpdated = new Date().toLocaleString('id-ID',{hour12:false});

        this.rebuildLineIfNeeded(true);
        this.rebuildBarIfNeeded(true);
        this.updateCharts();
      }catch(e){
        if (e.name!=='AbortError') console.error(e);
      }finally{
        this.loading = false;
      }
    },

    async reloadGroupOnly(){
      // panggil hanya endpoint grouping agar cepat
      try { this._ctrl?.abort(); } catch(_) {}
      this._ctrl = new AbortController();
      const el = document.getElementById('dash');
      const p = new URLSearchParams({...this.filters, group:this.groupBy});
      try{
        const r = await fetch(el.dataset.urlGroup + '?' + p.toString(), { signal:this._ctrl.signal });
        const j = await r.json();
        this.columns = Array.isArray(j?.columns) ? j.columns : [];
        this.rows    = Array.isArray(j?.rows) ? j.rows : [];
      }catch(e){
        if (e.name!=='AbortError') console.error(e);
      }
    },

    /* ==== CHARTS ==== */
    _trendType(){ const tt=this.chartOpts.trendType; if(tt==='bar') return {type:'bar', fillArea:false}; if(tt==='area') return {type:'line', fillArea:true}; return {type:'line', fillArea:false}; },
    _trendData(fillArea){ const pal=this.chartPalette(); const {type}=this._trendType(); return { labels:pure(this.entriesByDay.labels), datasets:[{ label:'Entries', data:pure(this.entriesByDay.series), fill:(type==='line'&&fillArea), borderWidth:type==='line'?2:0, borderColor:pal.line, backgroundColor:type==='line'?(fillArea?pal.fill:pal.line):pal.bar, tension:this.chartOpts.smoothing, pointRadius:type==='line'?2:0, pointHoverRadius:type==='line'?4:0, borderRadius:type==='bar'?8:0 }]}; },
    _optsTrend(){ const pal=this.chartPalette(); const {type}=this._trendType(); const showVals=!!this.chartOpts.showValues; return { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{mode:'index',intersect:false}, datalabels:{ display:showVals, anchor:type==='bar'?'end':'top', align:type==='bar'?'end':'top', formatter:(v)=>this.formatNumber(v), clamp:true, clip:false }}, scales:{ x:{ grid:{color:pal.grid}, ticks:{color:pal.ticks}, stacked:this.chartOpts.stacked && type==='bar' }, y:{ grid:{color:pal.grid}, ticks:{color:pal.ticks}, stacked:this.chartOpts.stacked && type==='bar' } } }; },
    _topConfig(){ const cType=this.chartOpts.topType==='doughnut'?'doughnut':'bar'; const horizontal=(this.chartOpts.topType==='hbar'); const pal=this.chartPalette(); const data={ labels:pure(this.top.labels), datasets:[{ label:'Entries', data:pure(this.top.series), borderWidth:0, borderRadius:cType==='bar'?8:0, backgroundColor:cType==='doughnut'?this._doughnutColors(this.top.series.length):pal.bar }]}; const options=this._optsTop(horizontal, cType==='doughnut'); return { cType, cfg:{data, options} }; },
    _optsTop(horizontal=false, isDoughnut=false){ const pal=this.chartPalette(); const showVals=!!this.chartOpts.showValues; if(isDoughnut){ return { responsive:true, maintainAspectRatio:false, plugins:{ legend:{position:'bottom'}, tooltip:{mode:'index',intersect:false}, datalabels:{ display:showVals, formatter:(v,ctx)=>{ const total=(ctx.dataset.data||[]).reduce((a,b)=>a+(+b||0),0)||1; const pct=(v*100/total).toFixed(1); return `${this.formatNumber(v)} (${pct}%)`; } } } }; } return { indexAxis: horizontal ? 'y' : 'x', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{mode:'index',intersect:false}, datalabels:{ display:showVals, anchor:'end', align:horizontal?'right':'top', formatter:(v)=>this.formatNumber(v), clamp:true, clip:false }}, scales:{ x:{ grid:{display:!horizontal,color:pal.grid}, ticks:{color:pal.ticks} }, y:{ grid:{display:horizontal,color:pal.grid}, ticks:{color:pal.ticks} } } }; },
    _doughnutColors(n){ const base=[186,32,46]; const arr=[]; for(let i=0;i<n;i++){ const f=0.35+0.6*(i/Math.max(1,n-1)); arr.push(`rgba(${base[0]},${base[1]},${base[2]},${f.toFixed(2)})`);} return arr; },

    initCharts(){ const cvL=document.getElementById('chartLine'); const cvB=document.getElementById('chartBar'); Chart.getChart(cvL)?.destroy(); Chart.getChart(cvB)?.destroy(); const {type,fillArea}=this._trendType(); this.chartLine = Alpine.raw(new Chart(cvL.getContext('2d'), { type, data:this._trendData(fillArea), options:this._optsTrend() })); const {cType,cfg}=this._topConfig(); this.chartBar = Alpine.raw(new Chart(cvB.getContext('2d'), { type:cType, data:cfg.data, options:cfg.options })); },
    updateCharts(){ if(this.chartLine){ const {type,fillArea}=this._trendType(); if(this.chartLine.config.type!==type){ this.rebuildLineIfNeeded(); } else { const pal=this.chartPalette(); this.chartLine.data.labels=pure(this.entriesByDay.labels); const ds=this.chartLine.data.datasets[0]; ds.data=pure(this.entriesByDay.series); ds.fill=(type==='line'&&fillArea); ds.borderWidth=type==='line'?2:0; ds.borderColor=pal.line; ds.backgroundColor=type==='line'?(fillArea?pal.fill:pal.line):pal.bar; ds.tension=this.chartOpts.smoothing; ds.pointRadius=type==='line'?2:0; ds.pointHoverRadius=type==='line'?4:0; ds.borderRadius=type==='bar'?8:0; this.chartLine.options=this._optsTrend(); this.chartLine.update(); } } if(this.chartBar){ const {cType}=this._topConfig(); if(this.chartBar.config.type!==cType){ this.rebuildBarIfNeeded(); } else { this.chartBar.data.labels=pure(this.top.labels); this.chartBar.data.datasets[0].data=pure(this.top.series); this.chartBar.options=this._optsTop(this.chartOpts.topType==='hbar', this.chartOpts.topType==='doughnut'); this.chartBar.update(); } } },
    rebuildLineIfNeeded(justInit=false){ const cv=document.getElementById('chartLine'); const {type,fillArea}=this._trendType(); if(!justInit && this.chartLine?.config?.type===type) return; try{ this.chartLine?.destroy(); }catch(_){} this.chartLine=Alpine.raw(new Chart(cv.getContext('2d'), { type, data:this._trendData(fillArea), options:this._optsTrend() })); },
    rebuildBarIfNeeded(justInit=false){ const cv=document.getElementById('chartBar'); const {cType,cfg}=this._topConfig(); if(!justInit && this.chartBar?.config?.type===cType) return; try{ this.chartBar?.destroy(); }catch(_){} this.chartBar=Alpine.raw(new Chart(cv.getContext('2d'), { type:cType, data:cfg.data, options:cfg.options })); },

    bindWindowResize(){ const onWinResize=throttle(()=>{ this.chartLine?.resize(); this.chartBar?.resize(); },150); window.addEventListener('resize', onWinResize); this._onWinResize=onWinResize; document.addEventListener('visibilitychange', ()=>{ if(!document.hidden){ this.chartLine?.resize(); this.chartBar?.resize(); } }); },
    exportChart(id, fn){ const cv=document.getElementById(id); if(!cv) return; const a=document.createElement('a'); a.download=`${fn}.png`; a.href=cv.toDataURL('image/png',1.0); a.click(); },
    destroy(){ if(this._destroyed) return; this._destroyed=true; try{ this._onWinResize && window.removeEventListener('resize', this._onWinResize);}catch(e){} try{ this.chartLine?.destroy(); }catch(e){} try{ this.chartBar?.destroy(); }catch(e){} try{ this._ctrl?.abort(); }catch(e){} },
  }
}
</script>
@endpush
