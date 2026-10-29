@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => $title,
        'subtitle' => 'Daftar aktivitas dan submission terverifikasi untuk ' . $title . '.',
        'filters' => $filters,
        'options' => $options,
        'filterMode' => 'listing',
    ])

    <x-hse.chart-card title="Riwayat Aktivitas & Submission" subtitle="Data realisasi kegiatan karyawan">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Pelapor / PIC</th>
                        <th>Site</th>
                        <th>Judul / Deskripsi</th>
                        <th>Status</th>
                        <th>Tindak Lanjut PICA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($type === 'hazard')
                                    {{ $row->report_time ? $row->report_time->format('d M Y H:i') : '—' }}
                                @else
                                    {{ $row->scheduled_date ? $row->scheduled_date->format('d M Y') : '—' }}
                                @endif
                            </td>
                            <td>
                                @if($type === 'hazard')
                                    <strong class="text-[#0F172A]">{{ $row->reporter_name ?? ($row->user?->name ?? '—') }}</strong>
                                @else
                                    <strong class="text-[#0F172A]">{{ $row->inspector?->name ?? '—' }}</strong>
                                @endif
                            </td>
                            <td>{{ $row->site?->name ?? '—' }}</td>
                            <td>
                                @if($type === 'hazard')
                                    {{ Str::limit($row->description, 60) }}
                                @else
                                    {{ $row->title }}
                                @endif
                            </td>
                            <td>
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    {{ $row->status ?? 'Submitted' }}
                                </span>
                            </td>
                            <td>
                                <span class="rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">Tercatat PICA</span>
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="7" title="Belum ada aktivitas" message="Tidak ada aktivitas untuk kategori ini pada periode terpilih." />
                    @endforelse
                </tbody>
            </table>
        </div>
    <x-slot:footer><x-ui.table-pagination :paginator="$records" /></x-slot:footer>
    </x-hse.chart-card>
</div>
@endsection
