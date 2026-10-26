@extends('layouts.app')

@section('title', 'SAP Target Management')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'SAP Target Management',
        'subtitle' => 'Konfigurasi target bulanan inspeksi, PTO, dan hazard report per karyawan/departemen/site.',
        'filters' => $filters,
        'options' => $options,
    ])

    @if(session('ok'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
            {{ session('ok') }}
        </div>
    @endif

    {{-- FORM INPUT TARGET SAP --}}
    <div class="rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-[#0F172A]">Tetapkan Target SAP Baru</h2>
        <p class="mt-1 text-xs text-[#64748B]">Tetapkan kuota kegiatan wajib inspeksi, observasi tugas (PTO), dan pelaporan bahaya.</p>

        <form action="{{ route('hse.dashboard.sap.targets.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-[#475569]">Site</label>
                <select name="site_id" class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                    <option value="">Semua Site</option>
                    @foreach($options['sites'] as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Department</label>
                <select name="department_id" class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                    <option value="">Semua Department</option>
                    @foreach($options['departments'] as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Tahun</label>
                <input type="number" name="year" value="{{ now()->year }}" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Bulan</label>
                <select name="month" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>Bulan {{ $m }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Target Inspeksi</label>
                <input type="number" name="target_inspection" min="0" value="4" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Target PTO</label>
                <input type="number" name="target_pto" min="0" value="2" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Target Hazard Report</label>
                <input type="number" name="target_hazard" min="0" value="6" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-[#1646A0] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#123880] transition">
                    Simpan Target SAP
                </button>
            </div>
        </form>
    </div>

    {{-- LIST TARGET SAP --}}
    <x-hse.chart-card title="Daftar Target SAP Terdaftar" subtitle="Target kewajiban keselamatan per entitas">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Site</th>
                        <th>Department</th>
                        <th>Target Inspeksi</th>
                        <th>Target PTO</th>
                        <th>Target Hazard</th>
                        <th>Dibuat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($targets as $row)
                        <tr>
                            <td><strong>{{ $row->year }} - Bulan {{ $row->month }}</strong></td>
                            <td>{{ $row->site?->name ?? 'Semua Site' }}</td>
                            <td>{{ $row->department?->name ?? 'Semua Department' }}</td>
                            <td><span class="font-bold text-[#1646A0]">{{ $row->target_inspection }}</span> inspeksi</td>
                            <td><span class="font-bold text-emerald-600">{{ $row->target_pto }}</span> PTO</td>
                            <td><span class="font-bold text-amber-600">{{ $row->target_hazard }}</span> laporan</td>
                            <td>{{ $row->creator?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="7" title="Belum ada target" message="Belum ada konfigurasi target SAP yang dibuat." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>

    <div class="mt-4">
        {{ $targets->links() }}
    </div>
</div>
@endsection
