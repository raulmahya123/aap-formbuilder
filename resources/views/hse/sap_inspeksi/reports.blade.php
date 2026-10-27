@extends('layouts.app')

@section('title', 'Laporan SAP Inspeksi')

@section('content')
<div class="ui-page space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Laporan SAP Inspeksi SHE/HSE</h1>
            <p class="text-sm text-slate-500">Rekap inspeksi, skor, temuan & status persetujuan</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hse.dashboard.sap.inspeksi.dashboard') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Dashboard</a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Template</label>
            <select name="template_id" class="text-xs rounded-lg border border-slate-300 p-2">
                <option value="">Semua</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}" @selected(request('template_id')==$t->id)>{{ $t->code }}</option>
                @endforeach
            </select>
        </div>
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold">Filter</button>
    </form>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="ui-table">
            <thead><tr><th>No. Inspeksi</th><th>Template</th><th>Site</th><th>Dept</th><th>Inspektor</th><th>Diajukan</th><th>Skor</th><th>%</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($inspections as $i)
                <tr>
                    <td class="font-medium">{{ $i->inspection_no }}</td>
                    <td>{{ $i->template->code }}</td>
                    <td>{{ $i->site->name ?? '-' }}</td>
                    <td>{{ $i->department->name ?? '-' }}</td>
                    <td>{{ $i->inspector->name ?? '-' }}</td>
                    <td>{{ $i->submitted_at ? $i->submitted_at->format('d/m/Y') : '-' }}</td>
                    <td>{{ $i->score ?? '-' }}</td>
                    <td>{{ $i->percentage ? number_format($i->percentage,1).'%' : '-' }}</td>
                    <td>{{ $i->status }}</td>
                    <td><a href="{{ route('hse.dashboard.sap.inspeksi.show', $i->id) }}" class="text-[#1646A0] text-xs font-semibold">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-slate-500 text-xs py-6">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $inspections->links() }}</div>
    </div>
</div>
@endsection
