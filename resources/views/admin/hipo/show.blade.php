@extends('layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-6 text-black">

    {{-- FLASH MESSAGE --}}
    @if(session('success'))
    <div
        x-data="{show:true}"
        x-init="setTimeout(() => show=false, 3000)"
        x-show="show"
        x-transition
        class="bg-green-100 border border-black px-4 py-3 rounded-xl flex items-center gap-3"
    >
        <span class="text-xl">✅</span>
        <div class="text-sm font-semibold">
            {{ session('success') }}
        </div>
    </div>
    @endif

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-2xl">
                ⚠️
            </div>
            <div>
                <h1 class="text-xl font-bold">Detail HIPO / Nearmiss</h1>
                <p class="text-sm">High Potential Hazard Report</p>
            </div>
        </div>

        <a href="{{ route('admin.hipo.index') }}"
           class="text-sm px-4 py-2 rounded-lg border border-black hover:bg-gray-100 font-medium">
            ← Kembali
        </a>
    </div>

    {{-- STATUS CARD --}}
    <div class="flex items-center justify-between bg-white border rounded-xl px-6 py-4 shadow-sm">
        <div>
            <div class="text-sm font-medium">Status Laporan</div>
            <div class="text-xl font-bold">{{ strtoupper($hipo->status) }}</div>
        </div>

        <div class="text-right">
            <div class="text-sm font-medium">Jobsite</div>
            <div class="text-base font-bold">{{ $hipo->jobsite }}</div>
        </div>
    </div>

    {{-- INFO GRID --}}
    <div class="grid md:grid-cols-4 gap-4 text-sm">

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs">Pelapor</div>
            <div class="font-semibold">{{ $hipo->reporter_name }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs">Waktu</div>
            <div class="font-semibold">
                {{ $hipo->report_time?->format('d M Y H:i') }}
            </div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs">Shift</div>
            <div class="font-semibold">{{ $hipo->shift }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs">Sumber</div>
            <div class="font-semibold">{{ $hipo->source }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs">Kategori</div>
            <div class="font-semibold">{{ $hipo->category }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs">Konsekuensi</div>
            <div class="font-semibold">{{ $hipo->potential_consequence }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs">Stop Work</div>
            <div class="font-bold">
                {{ $hipo->stop_work ? 'YA' : 'TIDAK' }}
            </div>
        </div>
    </div>

    {{-- DESKRIPSI --}}
    <div class="bg-white border rounded-xl p-6">
        <h3 class="font-semibold mb-2 flex items-center gap-2">
            📝 Rincian HIPO / Nearmiss
        </h3>
        <div class="text-sm whitespace-pre-line">
            {{ $hipo->description }}
        </div>
    </div>

    {{-- PENGENDALIAN --}}
    <div class="grid md:grid-cols-2 gap-4">

        <div class="bg-white border rounded-xl p-4">
            <h4 class="font-semibold mb-1">🛠️ Engineering Control</h4>
            <div class="text-sm">{{ $hipo->control_engineering ?? '-' }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <h4 class="font-semibold mb-1">📋 Administrative Control</h4>
            <div class="text-sm">{{ $hipo->control_administrative ?? '-' }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <h4 class="font-semibold mb-1">👷 Work Practice</h4>
            <div class="text-sm">{{ $hipo->control_work_practice ?? '-' }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <h4 class="font-semibold mb-1">🦺 APD</h4>
            <div class="text-sm">{{ $hipo->control_ppe ?? '-' }}</div>
        </div>

    </div>

    {{-- EVIDENCE --}}
    @if($hipo->evidence_file)
    <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            📎
            <div>
                <div class="font-semibold text-sm">Evidence Lampiran</div>
                <div class="text-xs">Foto / Dokumen pendukung</div>
            </div>
        </div>

        <a href="{{ route('pubfile.stream', $hipo->evidence_file) }}"
           target="_blank"
           class="px-4 py-2 border border-black rounded-lg text-sm hover:bg-gray-100">
            Lihat File
        </a>
    </div>
    @endif

    {{-- ADMIN ACTION (MUNCUL SETELAH DISIMPAN) --}}
    @if($hipo->pic)
    <form method="POST"
          action="{{ route('admin.hipo.update', $hipo->id) }}"
          class="bg-white border rounded-xl p-6 space-y-4">

        @csrf
        @method('PUT')

        <h3 class="font-semibold text-sm flex items-center gap-2">
            🔧 Tindakan Admin
        </h3>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs">PIC</label>
                <input name="pic"
                       value="{{ old('pic', $hipo->pic) }}"
                       class="w-full border border-black rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="text-xs">Status</label>
                <select name="status"
                        class="w-full border border-black rounded-lg px-3 py-2">
                    <option value="Open" @selected($hipo->status==='Open')>Open</option>
                    <option value="Closed" @selected($hipo->status==='Closed')>Closed</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <button class="px-5 py-2 border border-black rounded-lg hover:bg-gray-100">
                Simpan Perubahan
            </button>
        </div>
    </form>
    @endif

</div>
@endsection
