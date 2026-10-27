@extends('layouts.app')

@section('title', 'Daftar Inspeksi SAP SHE/HSE')

@section('content')
<div class="ui-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Daftar Inspeksi SHE/HSE</h1>
            <p class="text-sm text-slate-500">Semua inspeksi SAP Inspeksi berdasarkan template SHE-FM</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hse.sap.inspeksi.dashboard') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Dashboard</a>
            <a href="{{ route('hse.sap.inspeksi.create') }}" class="px-3 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold">Buat Inspeksi</a>
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
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
            <select name="status" class="text-xs rounded-lg border border-slate-300 p-2">
                <option value="">Semua</option>
                @foreach(['Draft','Submitted','Under Review','Approved','Returned','Closed'] as $st)
                    <option value="{{ $st }}" @selected(request('status')==$st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold">Filter</button>
    </form>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="ui-table">
            <thead><tr><th>No. Inspeksi</th><th>Template</th><th>Site/Dept</th><th>Inspektor</th><th>Tanggal</th><th>Status</th><th>Skor</th><th></th></tr></thead>
            <tbody>
                @forelse($inspections as $i)
                <tr>
                    <td class="font-medium">{{ $i->inspection_no }}</td>
                    <td>{{ $i->template->code }} - {{ Str::limit($i->template->name,40) }}</td>
                    <td>{{ $i->site->name ?? '-' }} / {{ $i->department->name ?? '-' }}</td>
                    <td>{{ $i->inspector->name ?? '-' }}</td>
                    <td>{{ $i->created_at->format('d/m/Y') }}</td>
                    <td>{{ $i->status }}</td>
                    <td>{{ $i->percentage ? number_format($i->percentage,1).'%' : '-' }}</td>
                    <td class="text-right">
                        <a href="{{ route('hse.sap.inspeksi.show', $i->id) }}" class="text-[#1646A0] text-xs font-semibold">Detail</a>
                        @if(in_array($i->status, ['Draft','Returned']))
                        <a href="{{ route('hse.sap.inspeksi.edit', $i->id) }}" class="text-slate-700 text-xs font-semibold ml-2">Isi Checklist</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-slate-500 text-xs py-6">Belum ada inspeksi</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $inspections->links() }}</div>
    </div>
</div>
@endsection
