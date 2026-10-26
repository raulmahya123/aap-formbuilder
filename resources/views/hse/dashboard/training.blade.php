@extends('layouts.app')

@section('title', 'HSE Training & Competency')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        @include('hse.dashboard.partials.header', [
            'title' => 'Training & Competency',
            'subtitle' => 'Completion, mandatory compliance, sertifikasi kedaluwarsa, attendance, dan training matrix per karyawan.',
            'filters' => $filters,
            'options' => $options,
            'export' => 'training',
        ])
    </div>

    <div class="flex justify-end -mt-4">
        <button type="button" onclick="document.getElementById('modal-input-training').showModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold shadow-sm hover:bg-[#0B2345] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Catat Pelatihan Karyawan
        </button>
    </div>

    @if(session('ok'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800 font-medium">
            {{ session('ok') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Scheduled vs Completed" subtitle="Record training per bulan">
            <x-hse.line-chart :chart="$data['charts']['completion_by_month']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Attendance" subtitle="Distribusi status record training">
            <x-hse.donut-chart :chart="$data['charts']['attendance']" title="Status attendance" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Training per Kategori" subtitle="Distribusi record per kategori course">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_category'])" label="Training per kategori" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Training per Department" subtitle="Top 10 department">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_department'])" label="Training per department" />
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Sertifikasi Akan Kedaluwarsa" subtitle="Dalam 120 hari ke depan">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>Karyawan</th><th>Course</th><th>Berlaku Sampai</th></tr></thead>
                <tbody>
                    @forelse($data['charts']['expiring'] as $row)
                        <tr>
                            <td class="font-medium">{{ $row['label'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['date'] }}</td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="3" title="Tidak ada sertifikasi kedaluwarsa" message="Semua sertifikasi masih berlaku." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>

    <x-hse.chart-card title="Training Matrix" subtitle="Status training per karyawan dan course">
        @if(empty($data['matrix']['rows']))
            <x-ui.empty-state title="Belum ada data training" message="Matrix akan tampil setelah record training tersedia." />
        @else
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th class="sticky left-0 bg-[#F8FAFC]">Karyawan</th>
                            @foreach($data['matrix']['courses'] as $course)
                                <th class="whitespace-nowrap" title="{{ $course->category }}">{{ $course->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['matrix']['rows'] as $row)
                            <tr>
                                <td class="sticky left-0 bg-white font-medium">{{ $row['user'] }}</td>
                                @foreach($data['matrix']['courses'] as $course)
                                    @php $cell = $row['cells'][$course->id] ?? 'Not applicable'; @endphp
                                    <td>
                                        <x-hse.status :value="$cell" />
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-hse.chart-card>
</div>

<!-- Modal Catat Pelatihan Karyawan -->
<dialog id="modal-input-training" class="rounded-xl border border-slate-200 p-0 shadow-2xl max-w-xl w-full backdrop:bg-slate-900/50">
    <div class="p-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
            <h3 class="text-base font-bold text-[#0F172A]">Catat Partisipasi Pelatihan K3 & Sertifikasi</h3>
            <button type="button" onclick="document.getElementById('modal-input-training').close()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <form action="{{ route('hse.dashboard.training.record.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Materi / Modul Pelatihan (Course) <span class="text-rose-500">*</span></label>
                <select name="course_id" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                    <option value="">-- Pilih Modul Pelatihan --</option>
                    @foreach($options['training_courses'] ?? [] as $crs)
                        <option value="{{ $crs->id }}">{{ $crs->name ?? $crs->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Karyawan / Peserta <span class="text-rose-500">*</span></label>
                    <select name="user_id" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($options['users'] ?? [] as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->name }} ({{ $usr->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Site Penugasan <span class="text-rose-500">*</span></label>
                    <select name="site_id" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="">-- Pilih Site --</option>
                        @foreach($options['sites'] ?? [] as $st)
                            <option value="{{ $st->id }}" {{ ($filters['site_id'] ?? null) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Jadwal <span class="text-rose-500">*</span></label>
                    <input type="date" name="scheduled_date" required value="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Selesai Pelatihan</label>
                    <input type="date" name="completed_date" value="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Evaluasi / Ujian (0 - 100)</label>
                    <input type="number" name="score" min="0" max="100" step="0.1" value="85" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keikutsertaan <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Completed" selected>Completed (Lulus/Selesai)</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Not Started">Not Started</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('modal-input-training').close()" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold hover:bg-[#0B2345]">Simpan Catatan Pelatihan</button>
            </div>
        </form>
    </div>
</dialog>
@endsection
