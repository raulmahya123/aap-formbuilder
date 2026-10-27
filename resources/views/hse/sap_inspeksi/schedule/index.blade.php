@extends('layouts.app')

@section('title', 'Jadwal Inspeksi')

@section('content')
<div class="ui-page space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Jadwal Inspeksi SHE/HSE</h1>
            <p class="text-sm text-slate-500">Perencanaan inspeksi berdasarkan template</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hse.dashboard.sap.inspeksi.dashboard') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Dashboard</a>
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.inspeksi.schedule.create') }}">Buat Jadwal</x-ui.link>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="ui-table">
            <thead><tr><th>Template</th><th>Site/Dept</th><th>Ditugaskan</th><th>Periode</th><th>Jatuh Tempo</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($schedules as $s)
                <tr>
                    <td class="font-medium">{{ $s->template->code }} - {{ $s->template->name }}</td>
                    <td>{{ $s->site->name ?? '-' }} / {{ $s->department->name ?? '-' }}</td>
                    <td>{{ $s->assignee->name ?? '-' }}</td>
                    <td>{{ $s->period ?? '-' }} {{ $s->year }}{{ $s->month ? '/'.$s->month : '' }}</td>
                    <td>{{ $s->due_date ? $s->due_date->format('d/m/Y') : '-' }}</td>
                    <td>{{ $s->status }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-slate-500 text-xs py-6">Belum ada jadwal</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ui-pagination-footer">{{ $schedules->links() }}</div>
    </div>
</div>
@endsection
