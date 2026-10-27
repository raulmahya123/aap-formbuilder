@extends('layouts.app')

@section('title', 'Detail Inspeksi')

@section('content')
<div class="ui-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $inspection->inspection_no }}</h1>
            <p class="text-sm text-slate-500">{{ $inspection->template->code }} - {{ $inspection->template->name }} | Status: <span class="font-semibold">{{ $inspection->status }}</span></p>
        </div>
        <div class="flex flex-wrap items-center gap-2"><x-ui.table-actions><x-ui.sap-record-actions module="inspeksi" :record="$inspection" /></x-ui.table-actions>
            @if(in_array($inspection->status, ['Draft','Returned']))
            <x-ui.link variant="primary" icon="pencil" href="{{ route('hse.dashboard.sap.inspeksi.edit', $inspection->id) }}">Lanjut Isi</x-ui.link>
            @endif
            @if($inspection->status === 'Submitted' || $inspection->status === 'Under Review')
            <form action="{{ route('hse.dashboard.sap.inspeksi.review', $inspection->id) }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="review_notes" placeholder="Catatan review" class="text-xs rounded-lg border border-slate-300 p-2">
                <button name="action" value="return" class="px-3 py-2 rounded-lg border border-amber-300 text-amber-700 text-xs font-semibold">Kembalikan</button>
                <button name="action" value="approve" class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Setujui</button>
            </form>
            @endif
            <a href="{{ route('hse.dashboard.sap.inspeksi.findings', $inspection->id) }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Temuan & Tindak Lanjut</a>
            <a href="{{ route('hse.dashboard.sap.inspeksi.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Kembali</a>
        </div>
    </div>

    @if(session('ok'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800">{{ session('ok') }}</div>@endif
    @if(session('error'))<div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-800">{{ session('error') }}</div>@endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Site</p><p class="font-semibold text-slate-900">{{ $inspection->site->name ?? '-' }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Inspektor</p><p class="font-semibold text-slate-900">{{ $inspection->inspector->name ?? '-' }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Skor</p><p class="font-semibold text-slate-900">{{ $inspection->score ?? '-' }} / {{ $inspection->max_score }} ({{ $inspection->percentage ? number_format($inspection->percentage,1).'%' : '-' }})</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Diajukan</p><p class="font-semibold text-slate-900">{{ $inspection->submitted_at ? $inspection->submitted_at->format('d/m/Y H:i') : '-' }}</p></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200"><h2 class="text-base font-bold text-slate-900">Rekap Jawaban Checklist</h2></div>
        <div class="divide-y divide-slate-100">
            @foreach($inspection->template->sections as $section)
            <div class="p-6">
                <h3 class="font-semibold text-slate-900 mb-3">{{ $section->title }}</h3>
                <div class="space-y-2">
                    @foreach($section->items as $item)
                    @php $ans = $inspection->answers->firstWhere('item_id', $item->id); @endphp
                    <div class="flex items-start justify-between gap-4 py-2 border-b border-slate-50 last:border-0">
                        <div class="flex-1">
                            <p class="text-sm text-slate-800">{{ $item->question }}</p>
                            @if($ans && $ans->remarks)<p class="text-xs text-slate-500 mt-1">Catatan: {{ $ans->remarks }}</p>@endif
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $ans && $ans->non_conformity ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $ans ? ($ans->answer ?? '-') : 'Belum diisi' }}
                            </span>
                            @if($ans && $ans->score !== null)<p class="text-xs text-slate-500 mt-1">Skor: {{ $ans->score }}</p>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if($inspection->approvals->count() > 0)
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200"><h2 class="text-base font-bold text-slate-900">Audit Trail Persetujuan</h2></div>
        <div class="divide-y divide-slate-100">
            @foreach($inspection->approvals as $ap)
            <div class="px-6 py-3 flex items-center justify-between">
                <div><p class="text-sm font-semibold text-slate-900">{{ $ap->action }}</p><p class="text-xs text-slate-500">{{ $ap->actor->name ?? '-' }} - {{ $ap->comments }}</p></div>
                <p class="text-xs text-slate-400">{{ $ap->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
