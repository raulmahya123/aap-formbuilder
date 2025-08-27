@extends('layouts.app')

@section('content')
<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard — {{ config('app.name') }}</title>

  {{-- Tailwind CDN --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              green: '#16a34a',
              ink: '#0f172a',
              fog: '#f8fafc'
            }
          },
          boxShadow: {
            'soft': '0 6px 24px rgba(2,6,23,0.06)'
          }
        }
      }
    }
  </script>
  <style>
    /* scrollbar halus */
    .nice-scroll::-webkit-scrollbar{height:8px;width:8px}
    .nice-scroll::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:8px}
    .nice-scroll::-webkit-scrollbar-track{background:transparent}
  </style>
</head>

<body
  x-data="dash()"
  x-init="init()"
  x-bind:class="dark ? 'dark' : ''"
  class="bg-slate-50 dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-100">

  <div class="max-w-7xl mx-auto p-6" id="dash"
       data-url-summary="{{ route('admin.dashboard.data.summary') }}"
       data-url-entries="{{ route('admin.dashboard.data.entries_by_day') }}"
       data-url-top="{{ route('admin.dashboard.data.top_forms') }}"
       data-url-dept="{{ route('admin.dashboard.data.by_department') }}"
  >
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
      <div>
        <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">Dashboard</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Ringkasan aktivitas formulir & entri.</p>
      </div>
      <div class="flex items-center gap-2">
        <button type="button"
                @click="toggleDark()"
                class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
          <span x-show="!dark">🌙 Dark</span>
          <span x-show="dark">☀️ Light</span>
        </button>
        <a class="px-3 py-2 rounded-lg border border-emerald-600 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition"
           :href="exportHref()">
          ⬇️ Export CSV
        </a>
      </div>
    </div>

    {{-- FILTERS TOOLBAR --}}
    <form @submit.prevent="reloadAll()"
          class="rounded-2xl border bg-white dark:bg-slate-900 dark:border-slate-800 p-4 shadow-soft mb-6">
      <div class="grid gap-4 md:grid-cols-5">
        <div class="md:col-span-2">
          <label class="text-xs font-medium text-slate-500 dark:text-slate-400">Department</label>
          <select x-model="filters.department_id"
                  class="mt-1 border rounded-lg w-full px-3 py-2 bg-white dark:bg-slate-950 dark:border-slate-700">
            <option value="">— Semua —</option>
            @foreach($departments as $d)
              <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="text-xs font-medium text-slate-500 dark:text-slate-400">Form</label>
          <select x-model="filters.form_id"
                  class="mt-1 border rounded-lg w-full px-3 py-2 bg-white dark:bg-slate-950 dark:border-slate-700">
            <option value="">— Semua —</option>
            @foreach($forms as $f)
              <option value="{{ $f->id }}">{{ $f->title }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-xs font-medium text-slate-500 dark:text-slate-400">Periode</label>
          <div class="grid grid-cols-2 gap-2 mt-1">
            <input type="date" x-model="filters.date_from"
                   class="border rounded-lg w-full px-3 py-2 bg-white dark:bg-slate-950 dark:border-slate-700">
            <input type="date" x-model="filters.date_to"
                   class="border rounded-lg w-full px-3 py-2 bg-white dark:bg-slate-950 dark:border-slate-700">
          </div>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-2">
        <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
          Terapkan
        </button>
        <button type="button" @click="resetFilters()"
                class="px-4 py-2 border rounded-lg hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800 transition">
          Reset
        </button>
        <span class="text-xs text-slate-500 dark:text-slate-400" x-show="!loading">Terakhir diperbarui: <span x-text="lastUpdated"></span></span>
      </div>
    </form>

    {{-- KPI CARDS --}}
    <div class="grid md:grid-cols-4 gap-4 mb-6">
      <template x-for="card in kpiCards" :key="card.key">
        <div class="p-4 bg-white dark:bg-slate-900 border dark:border-slate-800 rounded-2xl shadow-soft">
          <div class="flex items-start justify-between">
            <div>
              <div class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400" x-text="card.label"></div>
              <div class="mt-2">
                <div class="h-8 w-32 rounded animate-pulse bg-slate-200 dark:bg-slate-800" x-show="loading"></div>
                <div class="text-2xl font-semibold" x-show="!loading" x-text="formatNumber(summary[card.key] ?? 0)"></div>
              </div>
            </div>
            <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-300">
              <span x-html="card.icon"></span>
            </div>
          </div>
        </div>
      </template>
    </div>

    {{-- CHARTS --}}
    <div class="grid lg:grid-cols-3 gap-6">
      <div class="bg-white dark:bg-slate-900 border dark:border-slate-800 rounded-2xl p-4 lg:col-span-2 shadow-soft">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-semibold">Entries — 30 Hari</h2>
          <div class="text-xs text-slate-500 dark:text-slate-400" x-text="entriesByDay.labels?.length ? entriesByDay.labels[0] + ' — ' + entriesByDay.labels[entriesByDay.labels.length-1] : ''"></div>
        </div>
        <div class="relative h-[320px]">
          <div class="absolute inset-0 p-4" x-show="loading">
            <div class="h-full w-full rounded-xl bg-gradient-to-b from-slate-100 to-white dark:from-slate-800 dark:to-slate-900 animate-pulse"></div>
          </div>
          <canvas id="chartLine" class="nice-scroll"></canvas>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-900 border dark:border-slate-800 rounded-2xl p-4 shadow-soft">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-semibold">Top Forms</h2>
          <span class="text-xs text-slate-500 dark:text-slate-400" x-show="!loading" x-text="top.labels?.length + ' item'"></span>
        </div>
        <div class="relative h-[320px]">
          <div class="absolute inset-0 p-4" x-show="loading">
            <div class="h-full w-full rounded-xl bg-gradient-to-b from-slate-100 to-white dark:from-slate-800 dark:to-slate-900 animate-pulse"></div>
          </div>
          <canvas id="chartBar"></canvas>
        </div>
      </div>
    </div>

    {{-- TABEL REKAP --}}
    <div class="bg-white dark:bg-slate-900 border dark:border-slate-800 rounded-2xl p-4 mt-6 shadow-soft overflow-x-auto">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Rekap per Department</h2>
        <div class="text-xs text-slate-500 dark:text-slate-400" x-show="!loading" x-text="byDept.rows?.length + ' baris'"></div>
      </div>

      <template x-if="!loading && (!byDept.rows || byDept.rows.length===0)">
        <div class="p-8 text-center text-slate-500 dark:text-slate-400">
          Tidak ada data untuk filter saat ini.
        </div>
      </template>

      <div x-show="loading" class="space-y-2">
        <div class="h-10 rounded bg-slate-100 dark:bg-slate-800 animate-pulse"></div>
        <div class="h-10 rounded bg-slate-100 dark:bg-slate-800 animate-pulse"></div>
        <div class="h-10 rounded bg-slate-100 dark:bg-slate-800 animate-pulse"></div>
      </div>

      <div x-show="!loading">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300">
            <tr>
              <th class="text-left p-3">Department</th>
              <th class="text-right p-3">Total Forms</th>
              <th class="text-right p-3">Active Forms</th>
              <th class="text-right p-3">Total Entries</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="row in byDept.rows" :key="row.department">
              <tr class="border-t dark:border-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-800/50">
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

  {{-- Alpine + Chart.js --}}
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <script>
  function dash(){
    return {
      // state
      dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark',
      loading: true,
      lastUpdated: '',
      filters: { department_id:'', form_id:'', date_from:'', date_to:'' },
      summary: {},
      entriesByDay: { labels:[], series:[] },
      top: { labels:[], series:[] },
      byDept: { rows:[] },
      chartLine: null,
      chartBar: null,

      kpiCards: [
        { key:'totalForms',  label:'Total Forms',  icon:'📄' },
        { key:'activeForms', label:'Active Forms', icon:'✅' },
        { key:'totalEntries',label:'Total Entries',icon:'🧾' },
        { key:'uniqueUsers', label:'Unique Users', icon:'👤' },
      ].map(c => ({...c, icon:`<span class='text-lg'>${c.icon}</span>`})),

      toggleDark(){
        this.dark = !this.dark;
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
      },

      exportHref(){
        const p = new URLSearchParams(this.filters);
        return `{{ route('admin.entries.export') }}?${p.toString()}`;
      },

      formatNumber(n){
        if(n===null || n===undefined) return '0';
        return new Intl.NumberFormat('id-ID').format(n);
      },

      chartPalette(){
        // palet yang cocok untuk light/dark
        const primaryLine  = this.dark ? 'rgba(34,197,94,0.9)'  : 'rgba(16,185,129,0.9)';
        const primaryFill  = this.dark ? 'rgba(34,197,94,0.15)' : 'rgba(16,185,129,0.12)';
        const barColor     = this.dark ? 'rgba(59,130,246,0.85)' : 'rgba(37,99,235,0.85)';
        const grid         = this.dark ? 'rgba(148,163,184,0.15)' : 'rgba(148,163,184,0.25)';
        const ticks        = this.dark ? '#cbd5e1' : '#475569';
        return { primaryLine, primaryFill, barColor, grid, ticks };
      },

      async init(){
        await this.reloadAll();
        this.initCharts();
        // re-theme charts saat toggle dark
        this.$watch('dark', () => { this.rethemeCharts(); });
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

          this.summary      = sum ?? {};
          this.entriesByDay = ent ?? {labels:[],series:[]};
          this.top          = top ?? {labels:[],series:[]};
          this.byDept       = dept ?? {rows:[]};

          this.lastUpdated = new Date().toLocaleString('id-ID', {hour12:false});
          this.updateCharts();
        }catch(e){
          console.error(e);
          alert('Gagal memuat data. Coba beberapa saat lagi.');
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

        // Line
        const ctxL = document.getElementById('chartLine').getContext('2d');
        this.chartLine = new Chart(ctxL, {
          type: 'line',
          data: {
            labels: this.entriesByDay.labels,
            datasets: [{
              label: 'Entries',
              data: this.entriesByDay.series,
              fill: true,
              borderWidth: 2,
              borderColor: pal.primaryLine,
              backgroundColor: pal.primaryFill,
              tension: 0.35,
              pointRadius: 2,
              pointHoverRadius: 4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: { mode: 'index', intersect: false }
            },
            scales: {
              x: { grid: { color: pal.grid }, ticks:{ color: pal.ticks } },
              y: { grid: { color: pal.grid }, ticks:{ color: pal.ticks } }
            }
          }
        });

        // Bar
        const ctxB = document.getElementById('chartBar').getContext('2d');
        this.chartBar = new Chart(ctxB, {
          type: 'bar',
          data: {
            labels: this.top.labels,
            datasets: [{
              label: 'Entries',
              data: this.top.series,
              borderWidth: 0,
              borderRadius: 8,
              backgroundColor: pal.barColor
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: { mode: 'index', intersect: false }
            },
            scales: {
              x: { grid: { display:false }, ticks:{ color: pal.ticks } },
              y: { grid: { color: pal.grid }, ticks:{ color: pal.ticks } }
            }
          }
        });
      },

      updateCharts(){
        if(this.chartLine){
          this.chartLine.data.labels = this.entriesByDay.labels;
          this.chartLine.data.datasets[0].data = this.entriesByDay.series;
          this.chartLine.update();
        }
        if(this.chartBar){
          this.chartBar.data.labels = this.top.labels;
          this.chartBar.data.datasets[0].data = this.top.series;
          this.chartBar.update();
        }
      },

      rethemeCharts(){
        const pal = this.chartPalette();
        if(this.chartLine){
          this.chartLine.data.datasets[0].borderColor = pal.primaryLine;
          this.chartLine.data.datasets[0].backgroundColor = pal.primaryFill;
          this.chartLine.options.scales.x.grid.color = pal.grid;
          this.chartLine.options.scales.y.grid.color = pal.grid;
          this.chartLine.options.scales.x.ticks.color = pal.ticks;
          this.chartLine.options.scales.y.ticks.color = pal.ticks;
          this.chartLine.update('none');
        }
        if(this.chartBar){
          this.chartBar.data.datasets[0].backgroundColor = pal.barColor;
          this.chartBar.options.scales.x.ticks.color = pal.ticks;
          this.chartBar.options.scales.y.ticks.color = pal.ticks;
          this.chartBar.options.scales.y.grid.color = pal.grid;
          this.chartBar.update('none');
        }
      }
    }
  }
  </script>
</body>
</html>
@endsection
