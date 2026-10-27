@extends('layouts.app')

@section('title', 'HSE Inspection & Audit')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        @include('hse.dashboard.partials.header', [
            'title' => 'Inspection & Audit Findings',
            'subtitle' => 'Jadwal dan closure inspeksi, skor, temuan audit, severity, aging finding, dan status perbaikan.',
            'filters' => $filters,
            'options' => $options,
            'export' => 'inspections',
        ])
    </div>

    <div class="flex justify-end -mt-4">
        <button type="button" onclick="document.getElementById('modal-input-inspection').showModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold shadow-sm hover:bg-[#0B2345] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Catat Inspeksi / PTO Baru
        </button>
    </div>

    @if(session('ok'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800 font-medium">
            {{ session('ok') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Scheduled vs Completed" subtitle="Inspeksi per bulan">
            <x-hse.line-chart :chart="$data['charts']['completion']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Tren Skor Inspeksi" subtitle="Rata-rata skor inspeksi selesai">
            <x-hse.line-chart :chart="$data['charts']['score_trend']" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Skor per Site" subtitle="Rata-rata skor inspeksi selesai">
            <x-ui.bar-chart :series="$toBars($data['charts']['score_by_site'])" label="Skor inspeksi per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Severity Temuan" subtitle="Distribusi severity finding">
            <x-hse.donut-chart :chart="$data['charts']['findings_severity']" title="Severity temuan" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Closure" subtitle="Distribusi status finding">
            <x-hse.donut-chart :chart="$data['charts']['closure']" title="Status closure" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Temuan per Department" subtitle="Top 10 department">
            <x-ui.bar-chart :series="$toBars($data['charts']['findings_department'])" label="Temuan per department" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Aging Temuan" subtitle="Berdasarkan due date">
            <x-ui.bar-chart :series="$toBars($data['charts']['aging'])" label="Aging temuan" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Inspeksi Mendatang" subtitle="10 jadwal terdekat">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead><tr><th>Inspeksi</th><th>Site</th><th>Jadwal</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($data['upcoming'] as $row)
                            <tr>
                                <td class="font-medium">{{ $row['title'] }}</td>
                                <td>{{ $row['site'] }}</td>
                                <td>{{ $row['date'] }}</td>
                                <td><x-hse.status :value="$row['status']" /></td>
                            </tr>
                        @empty
                            <x-ui.table-empty-state :colspan="4" title="Tidak ada jadwal" message="Belum ada inspeksi terjadwal." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-hse.chart-card>
        <x-hse.chart-card title="Riwayat Inspeksi" subtitle="50 inspeksi terakhir">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead><tr><th>Inspeksi</th><th>Site</th><th>Tanggal</th><th>Skor</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($data['history'] as $row)
                            <tr>
                                <td class="font-medium">{{ $row['title'] }}</td>
                                <td>{{ $row['site'] }}</td>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['score'] === null ? '—' : number_format((float) $row['score'], 1, ',', '.') }}</td>
                                <td><x-hse.status :value="$row['status']" /></td>
                            </tr>
                        @empty
                            <x-ui.table-empty-state :colspan="5" title="Belum ada riwayat" message="Belum ada inspeksi pada periode ini." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Temuan Audit & Inspeksi" subtitle="50 temuan terakhir">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>No</th><th>Temuan</th><th>Site</th><th>Severity</th><th>Due Date</th><th>Assignee</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($data['findings'] as $row)
                        <tr>
                            <td class="font-medium">{{ $row['finding_no'] }}</td>
                            <td>{{ $row['title'] }}</td>
                            <td>{{ $row['site'] }}</td>
                            <td><x-hse.status :value="$row['severity']" /></td>
                            <td>{{ $row['due_date'] }}</td>
                            <td>{{ $row['assignee'] }}</td>
                            <td><x-hse.status :value="$row['status']" /></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="7" title="Belum ada temuan" message="Tidak ada finding pada periode ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>

<!-- Modal Catat Inspeksi / PTO Baru -->
<dialog id="modal-input-inspection" class="rounded-xl border border-slate-200 p-0 shadow-2xl max-w-2xl w-full backdrop:bg-slate-900/50">
    <div class="p-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
            <h3 class="text-base font-bold text-[#0F172A]">Catat Inspeksi / Observasi Tugas (PTO) Baru</h3>
            <button type="button" onclick="document.getElementById('modal-input-inspection').close()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <form action="{{ route('hse.dashboard.inspections.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Site Operasional <span class="text-rose-500">*</span></label>
                    <select name="site_id" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="">-- Pilih Site --</option>
                        @foreach($options['sites'] ?? [] as $st)
                            <option value="{{ $st->id }}" {{ ($filters['site_id'] ?? null) == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen</label>
                    <select name="department_id" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($options['departments'] ?? [] as $dp)
                            <option value="{{ $dp->id }}" {{ ($filters['department_id'] ?? null) == $dp->id ? 'selected' : '' }}>{{ $dp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Inspeksi / Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Inspeksi K3 Alat Berat Hauling" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Kegiatan <span class="text-rose-500">*</span></label>
                    <select name="type" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Routine">Inspeksi Rutin (Routine)</option>
                        <option value="PTO">Planned Task Observation (PTO)</option>
                        <option value="Equipment">Inspeksi Alat & Peralatan</option>
                        <option value="Vehicle">Inspeksi Kendaraan / LV</option>
                        <option value="Housekeeping">Housekeeping / 5R</option>
                        <option value="Management Walkthrough">Management Walkthrough</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Jadwal <span class="text-rose-500">*</span></label>
                    <input type="date" name="scheduled_date" required value="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Penyelesaian</label>
                    <input type="date" name="completed_date" value="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Kegiatan <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Completed" selected>Completed</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="In Progress">In Progress</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Pemenuhan (0 - 100)</label>
                    <input type="number" name="score" min="0" max="100" step="0.1" value="100" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Temuan (Jika ada)</label>
                    <input type="text" name="finding_description" placeholder="Akan otomatis membuat tugas PICA jika diisi" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Keterangan Observasi</label>
                <textarea name="notes" rows="2" placeholder="Catatan inspektor atau ringkasan temuan di lapangan..." class="w-full text-xs rounded-lg border border-slate-300 p-2.5"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('modal-input-inspection').close()" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                <x-ui.button variant="primary" icon="save" type="submit">Simpan Inspeksi</x-ui.button>
            </div>
        </form>
    </div>
</dialog>
@endsection
