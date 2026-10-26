@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => $title,
        'subtitle' => 'Detail insiden, perbandingan target vs aktual, keparahan, investigasi, dan tindak lanjut PICA.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- STATS & TARGET SUMMARY --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Total Kasus Periode Ini</span>
            <div class="mt-2 text-3xl font-extrabold text-[#1646A0]">{{ number_format($actualCount) }}</div>
        </div>
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Target KPI (Tahun {{ $filters['year'] }})</span>
            <div class="mt-2 text-3xl font-extrabold text-[#0F172A]">{{ number_format($target) }}</div>
        </div>
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Status Kepatuhan</span>
            <div class="mt-2">
                @if($actualCount <= $target)
                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-800">Memenuhi Target</span>
                @else
                    <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-sm font-bold text-rose-800">Melebihi Target (Perhatian)</span>
                @endif
            </div>
        </div>
    </div>

    {{-- DRILL-DOWN RECORD TABLE --}}
    <x-hse.chart-card title="Drill-down Kasus & Kejadian Terkait" subtitle="Daftar insiden terverifikasi dari submission form dan input operasional">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>No. Insiden</th>
                        <th>Tanggal Kejadian</th>
                        <th>Site</th>
                        <th>Department</th>
                        <th>Tipe / Kategori</th>
                        <th>Severity</th>
                        <th>Hari Hilang</th>
                        <th>Status Investigasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td><strong class="text-[#0F172A]">{{ $row->incident_no ?? ('#' . $row->id) }}</strong></td>
                            <td>{{ $row->occurred_at ? $row->occurred_at->format('d M Y H:i') : '—' }}</td>
                            <td>{{ $row->site?->name ?? '—' }}</td>
                            <td>{{ $row->department?->name ?? '—' }}</td>
                            <td>{{ $row->type }}</td>
                            <td><x-hse.status :value="$row->severity" /></td>
                            <td>{{ number_format($row->lost_time_days) }} hari</td>
                            <td>
                                <span class="rounded px-2 py-0.5 text-xs font-semibold {{ $row->investigation_status === 'Completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $row->investigation_status }}
                                </span>
                            </td>
                            <td><x-hse.status :value="$row->status" /></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="9" title="Tidak ada catatan kejadian" message="Tidak ada kasus yang tercatat pada periode dan filter terpilih." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>

    <div class="mt-4">
        {{ $records->links() }}
    </div>
</div>
@endsection
