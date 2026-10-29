@extends('layouts.app')

@section('title', 'SHE Manhours Management')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'filterMode' => 'listing',
        'showDashboardFilters' => true,
        'title' => 'Manhours Management',
        'subtitle' => 'Input, validasi SHE, riwayat, dan rekap jam kerja tervalidasi per site dan kontraktor.',
        'filters' => $filters,
        'options' => $options,
    ])

    @if(session('ok'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
            {{ session('ok') }}
        </div>
    @endif

    {{-- STATS CARDS --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Total Manhours Tervalidasi</span>
            <div class="mt-2 text-2xl font-bold text-[#1646A0]">{{ number_format($stats['total_hours'], 0, ',', '.') }} <span class="text-xs font-normal text-[#64748B]">jam</span></div>
        </div>
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Menunggu Validasi SHE</span>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['pending_validation'] }} <span class="text-xs font-normal text-[#64748B]">record</span></div>
        </div>
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Total Riwayat Input</span>
            <div class="mt-2 text-2xl font-bold text-[#0F172A]">{{ $stats['total_records'] }} <span class="text-xs font-normal text-[#64748B]">entri</span></div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Riwayat & Validasi Manhours</h2>
            <p class="text-xs text-slate-500">Antrean pencatatan jam kerja aman untuk divalidasi oleh SHE Department.</p>
        </div>
        <a href="{{ route('hse.dashboard.manhours.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#1646A0] px-4 py-2.5 text-xs font-semibold text-white shadow hover:bg-[#123880] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            + Input Jam Kerja (Manhours)
        </a>
    </div>

    {{-- TABEL RIWAYAT & VALIDASI --}}
    <x-hse.chart-card title="Antrean & Riwayat Manhours" subtitle="Validasi dan verifikasi resmi SHE Department">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Site</th>
                        <th>Perusahaan</th>
                        <th>Karyawan</th>
                        <th>Manhours</th>
                        <th>Status</th>
                        <th>Submitter</th>
                        <th>Validator</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->date->format('d M Y') }}</td>
                            <td><span class="font-medium text-[#0F172A]">{{ $row->site?->name ?? '—' }}</span></td>
                            <td>{{ $row->company?->name ?? 'Internal' }}</td>
                            <td>{{ number_format($row->employee_count) }}</td>
                            <td><strong>{{ number_format($row->manhours, 1) }}</strong> jam</td>
                            <td>
                                @if($row->status === 'Validated')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Validated</span>
                                @elseif($row->status === 'Submitted')
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Menunggu Validasi</span>
                                @else
                                    <span class="inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800">{{ $row->status }}</span>
                                @endif
                            </td>
                            <td>{{ $row->submitter?->name ?? '—' }}</td>
                            <td>{{ $row->validator?->name ?? '—' }}</td>
                            <td>
                                @if($row->status === 'Submitted' && auth()->user()?->isAdmin())
                                    <div class="flex items-center gap-1.5">
                                        <form action="{{ route('hse.dashboard.manhours.validate', $row->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="validate" />
                                            <x-ui.icon-button action="approve" label="Validasi" type="submit" />
                                        </form>
                                        <form action="{{ route('hse.dashboard.manhours.validate', $row->id) }}" method="POST" data-confirm="Kembalikan entri manhours ini?">
                                            @csrf
                                            <input type="hidden" name="action" value="return" />
                                            <x-ui.icon-button action="deactivate" label="Return" type="submit" />
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-[#94A3B8]">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="9" title="Belum ada riwayat" message="Belum ada entri manhours yang dicatat." />
                    @endforelse
                </tbody>
            </table>
        </div>
    <x-slot:footer><x-ui.table-pagination :paginator="$records" /></x-slot:footer>
    </x-hse.chart-card>
</div>
@endsection
