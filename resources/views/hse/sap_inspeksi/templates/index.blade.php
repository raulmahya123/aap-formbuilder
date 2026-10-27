@extends('layouts.app')

@section('title', 'Template SHE-FM')

@section('content')
<div class="ui-page space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Template Inspeksi SHE-FM</h1>
            <p class="text-sm text-slate-500">9 formulir resmi SHE-FM untuk SAP Inspeksi</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hse.sap.inspeksi.dashboard') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Dashboard</a>
            <a href="{{ route('hse.sap.inspeksi.create') }}" class="px-3 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold">Buat Inspeksi</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($templates as $t)
        <a href="{{ route('hse.sap.inspeksi.templates.show', $t->id) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="px-2 py-1 rounded bg-blue-50 text-blue-700 text-xs font-bold">{{ $t->code }}</span>
                <span class="text-xs text-slate-500">v{{ $t->version }}</span>
            </div>
            <h3 class="mt-3 text-base font-bold text-slate-900">{{ $t->name }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ $t->description }}</p>
            <div class="mt-4 flex items-center gap-4 text-xs text-slate-600">
                <span>{{ $t->sections_count }} Section</span>
                <span>{{ $t->items_count }} Item</span>
                <span>{{ $t->inspections_count }} Inspeksi</span>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center text-slate-500 text-sm py-8">Belum ada template. Silakan import/seed template SHE-FM.</div>
        @endforelse
    </div>
</div>
@endsection
