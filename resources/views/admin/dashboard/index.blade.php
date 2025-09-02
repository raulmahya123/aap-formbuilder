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
  data-url-dept="{{ route('admin.dashboard.data.by_department') }}"
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
      <div class="grid gap-4 sm:gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-5">
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
        <h2 class="font-semibold">Rekap per Department</h2>
        <div class="text-xs text-coal-500" x-show="!loading" x-text="byDept.rows?.length + ' baris'"></div>
      </div>

      <template x-if="!loading && (!byDept.rows || byDept.rows.length===0)">
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
        <table class="w-full text-sm min-w-[680px]">
          <thead class="bg-ivory-100 text-coal-700 sticky top-0">
            <tr>
              <th class="text-left p-3">Department</th>
              <th class="text-right p-3">Total Forms</th>
              <th class="text-right p-3">Active Forms</th>
              <th class="text-right p-3">Total Entries</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="row in byDept.rows" :key="row.department">
              <tr class="border-t hover:bg-ivory-100">
                <td class="p-3" x-text="row.department"></td>
                <td class="p-3 text-right" x-text="formatNumber(row.total_forms)"></td>
                <td class="p-3 text-right" x-text="formatNumber(row.active_forms)"></td>
                <td class="p-3 text-right" x-text="formatNumber(row.total_entries)"></td>
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
<script>
function dash(){
  // throttle util
  const throttle = (fn, ms) => {
    let t = 0, id;
    return (...args) => {
      const now = Date.now();
      if (now - t >= ms) { t = now; fn(...args); }
      else { clearTimeout(id); id = setTimeout(() => { t = Date.now(); fn(...args); }, ms - (now - t)); }
    };
  };

  // HAPUS semua reactivity/proxy Alpine dari nilai yang dikirim ke Chart.js
  const pure = (v) => {
    if (Array.isArray(v)) return v.slice();            // shallow copy array
    if (v && typeof v === 'object') return JSON.parse(JSON.stringify(v)); // deep clone object
    return v ?? null;
  };

  return {
    // state
    loading: true,
    lastUpdated: '',
    filters: { department_id:'', form_id:'', date_from:'', date_to:'' },

    summary: {},
    entriesByDay: { labels:[], series:[] },
    top: { labels:[], series:[] },
    byDept: { rows:[] },

    // instance Chart — disimpan sebagai RAW agar tidak di-proxy Alpine
    chartLine: null,
    chartBar: null,

    _onWinResize: null,
    _destroyed: false,

    kpiCards: [
      { key:'totalForms', label:'Total Forms', icon:'📄' },
      { key:'activeForms', label:'Active Forms', icon:'✅' },
      { key:'totalEntries', label:'Total Entries', icon:'🧾' },
      { key:'uniqueUsers', label:'Unique Users', icon:'👤' },
      { key:'totalDocuments', label:'Total Documents', icon:'📚' },
      { key:'totalTemplates', label:'Total Templates', icon:'📑' },
    ].map(c => ({...c, icon:`<span class='text-lg'>${c.icon}</span>`})),

    exportHref(){
      const p = new URLSearchParams(this.filters);
      return `{{ route('admin.entries.export') }}?${p.toString()}`;
    },

    formatNumber(n){
      if(n===null || n===undefined) return '0';
      return new Intl.NumberFormat('id-ID').format(n);
    },

    chartPalette(){
      const line = 'rgba(153,26,37,0.95)';
      const fill = 'rgba(186,32,46,0.10)';
      const bar  = 'rgba(123,30,43,0.85)';
      const grid = 'rgba(58,58,64,0.15)';
      const ticks= '#3a3a40';
      return { line, fill, bar, grid, ticks };
    },

    async init(){
      await this.reloadAll();
      this.initCharts();
      this.bindWindowResize();
      requestAnimationFrame(() => {
        this.chartLine?.resize();
        this.chartBar?.resize();
      });
      window.addEventListener('beforeunload', () => this.destroy());
    },

    async reloadAll(){
      this.loading = true;
      const p  = new URLSearchParams(this.filters);
      const el = document.getElementById('dash');

      try{
        const [sum, ent, top, dept] = await Promise.all([
          fetch(el.dataset.urlSummary + '?' + p.toString()).then(r=>r.json()),
          fetch(el.dataset.urlEntries + '?' + p.toString()).then(r=>r.json()),
          fetch(el.dataset.urlTop     + '?' + p.toString()).then(r=>r.json()),
          fetch(el.dataset.urlDept    + '?' + p.toString()).then(r=>r.json()),
        ]);

        // normalisasi: pastikan array selalu ada
        this.summary      = sum ?? {};
        this.entriesByDay = { labels: Array.isArray(ent?.labels) ? ent.labels : [], series: Array.isArray(ent?.series) ? ent.series : [] };
        this.top          = { labels: Array.isArray(top?.labels) ? top.labels : [], series: Array.isArray(top?.series) ? top.series : [] };
        this.byDept       = { rows: Array.isArray(dept?.rows) ? dept.rows : [] };

        this.lastUpdated = new Date().toLocaleString('id-ID', {hour12:false});
        this.updateCharts(); // update data chart saja

        requestAnimationFrame(() => {
          this.chartLine?.resize();
          this.chartBar?.resize();
        });
      }catch(e){
        console.error(e);
      }finally{
        this.loading = false;
      }
    },

    resetFilters(){
      this.filters = { department_id:'', form_id:'', date_from:'', date_to:'' };
      this.reloadAll();
    },

    initCharts(){
      const pal = this.chartPalette();

      const cvLine = document.getElementById('chartLine');
      const cvBar  = document.getElementById('chartBar');

      // Pastikan tidak ada instance lama
      Chart.getChart(cvLine)?.destroy();
      Chart.getChart(cvBar)?.destroy();

      // ===== LINE =====
      const ctxL = cvLine.getContext('2d');
      const lineChart = new Chart(ctxL, {
        type: 'line',
        data: {
          labels: pure(this.entriesByDay.labels),      // <-- pakai data murni
          datasets: [{
            label: 'Entries',
            data: pure(this.entriesByDay.series),      // <-- pakai data murni
            fill: true,
            borderWidth: 2,
            borderColor: pal.line,
            backgroundColor: pal.fill,
            tension: 0.35,
            pointRadius: 2,
            pointHoverRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
          scales: {
            x: { grid: { color: pal.grid }, ticks:{ color: pal.ticks } },
            y: { grid: { color: pal.grid }, ticks:{ color: pal.ticks } }
          }
        }
      });
      this.chartLine = Alpine.raw(lineChart); // jangan diproxy

      // ===== BAR =====
      const ctxB = cvBar.getContext('2d');
      const barChart = new Chart(ctxB, {
        type: 'bar',
        data: {
          labels: pure(this.top.labels),               // <-- pakai data murni
          datasets: [{
            label: 'Entries',
            data: pure(this.top.series),               // <-- pakai data murni
            borderWidth: 0,
            borderRadius: 8,
            backgroundColor: pal.bar
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
          scales: {
            x: { grid: { display:false }, ticks:{ color: pal.ticks } },
            y: { grid: { color: pal.grid }, ticks:{ color: pal.ticks } }
          }
        }
      });
      this.chartBar = Alpine.raw(barChart); // jangan diproxy
    },

    updateCharts(){
      // Update data — dengan array plain (non-proxy)
      if (this.chartLine && this.chartLine.data) {
        const pal = this.chartPalette();
        this.chartLine.data.labels = pure(this.entriesByDay.labels);
        this.chartLine.data.datasets[0].data = pure(this.entriesByDay.series);
        this.chartLine.data.datasets[0].borderColor = pal.line;
        this.chartLine.data.datasets[0].backgroundColor = pal.fill;
        this.chartLine.update();
      }
      if (this.chartBar && this.chartBar.data) {
        const pal = this.chartPalette();
        this.chartBar.data.labels = pure(this.top.labels);
        this.chartBar.data.datasets[0].data = pure(this.top.series);
        this.chartBar.data.datasets[0].backgroundColor = pal.bar;
        this.chartBar.update();
      }
    },

    bindWindowResize(){
      const onWinResize = throttle(() => {
        this.chartLine?.resize();
        this.chartBar?.resize();
      }, 150);
      window.addEventListener('resize', onWinResize);
      this._onWinResize = onWinResize;

      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
          this.chartLine?.resize();
          this.chartBar?.resize();
        }
      });
    },

    destroy(){
      if (this._destroyed) return;
      this._destroyed = true;
      try { this._onWinResize && window.removeEventListener('resize', this._onWinResize); } catch(e){}
      try { this.chartLine?.destroy(); } catch(e){}
      try { this.chartBar?.destroy(); } catch(e){}
    },
  }
}
</script>
@endpush

