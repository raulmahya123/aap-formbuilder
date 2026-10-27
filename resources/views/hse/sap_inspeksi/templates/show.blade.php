@extends('layouts.app')

@section('title', $template->code . ' - Template')

@section('content')
<div class="ui-page space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $template->code }} - {{ $template->name }}</h1>
            <p class="text-sm text-slate-500">Tipe Penilaian: {{ $template->scoring_type }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hse.sap.inspeksi.templates') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Kembali</a>
            <a href="{{ route('hse.sap.inspeksi.create') }}?template_id={{ $template->id }}" class="px-3 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold">Buat Inspeksi</a>
        </div>
    </div>

    @foreach($template->sections as $section)
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-base font-bold text-slate-900">{{ $section->title }}</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($section->items as $item)
            <div class="px-6 py-3 flex items-start justify-between gap-4">
                <div class="flex-1">
                    <p class="text-sm text-slate-800">{{ $item->question }}</p>
                    @if($item->guidance)<p class="text-xs text-slate-500 mt-1">{{ $item->guidance }}</p>@endif
                </div>
                <div class="text-xs text-slate-500 whitespace-nowrap">{{ $item->answer_type }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
