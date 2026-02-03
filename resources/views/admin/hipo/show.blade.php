@extends('layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-6 text-black">

{{-- FLASH MESSAGE --}}
@if(session('success'))
<div x-data="{show:true}" x-init="setTimeout(()=>show=false,3000)" x-show="show"
     class="bg-green-100 border border-black px-4 py-3 rounded-xl flex items-center gap-3">
    <span class="text-xl">✅</span>
    <div class="text-sm font-semibold">{{ session('success') }}</div>
</div>
@endif

{{-- HEADER --}}
<div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-2xl">⚠️</div>
        <div>
            <h1 class="text-xl font-bold">Detail HIPO / Nearmiss</h1>
            <p class="text-sm">High Potential Hazard Report</p>
        </div>
    </div>

    <a href="{{ route('admin.hipo.index') }}"
       class="text-sm px-4 py-2 rounded-lg border border-black hover:bg-gray-100">
        ← Kembali
    </a>
</div>

{{-- STATUS --}}
<div class="flex items-center justify-between bg-white border rounded-xl px-6 py-4">
    <div>
        <div class="text-xs">Status</div>
        <div class="text-xl font-bold">{{ strtoupper($hipo->status) }}</div>
    </div>

    <div class="text-right">
        <div class="text-xs">Risk Level</div>
        <span class="px-3 py-1 rounded text-sm font-bold
            @class([
                'bg-green-100 text-green-700' => $hipo->risk_level==='Low',
                'bg-yellow-100 text-yellow-700' => $hipo->risk_level==='Medium',
                'bg-orange-100 text-orange-700' => $hipo->risk_level==='High',
                'bg-red-100 text-red-700' => $hipo->risk_level==='Extreme',
            ])">
            {{ $hipo->risk_level }}
        </span>
    </div>
</div>

{{-- INFO --}}
<div class="grid md:grid-cols-4 gap-4 text-sm">
    @foreach([
        'Pelapor' => $hipo->reporter_name,
        'Jobsite' => $hipo->jobsite,
        'Waktu' => $hipo->report_time?->format('d M Y H:i'),
        'Shift' => $hipo->shift,
        'Sumber' => $hipo->source,
        'Kategori' => $hipo->category,
        'Konsekuensi' => $hipo->potential_consequence,
        'Stop Work' => $hipo->stop_work ? 'YA' : 'TIDAK',
    ] as $label => $value)
    <div class="bg-white border rounded-xl p-4">
        <div class="text-xs">{{ $label }}</div>
        <div class="font-semibold">{{ $value }}</div>
    </div>
    @endforeach
</div>

{{-- DESKRIPSI --}}
<div class="bg-white border rounded-xl p-6">
    <h3 class="font-semibold mb-2">📝 Rincian Kejadian</h3>
    <div class="text-sm whitespace-pre-line">{{ $hipo->description }}</div>
</div>

{{-- KONTROL RISIKO + PIC + EVIDENCE --}}
<div class="grid md:grid-cols-2 gap-4">
@foreach([
    'engineering' => 'Rekayasa Engineering',
    'administrative' => 'Administratif',
    'work_practice' => 'Praktek Kerja',
    'ppe' => 'APD'
] as $key => $label)
<div class="bg-white border rounded-xl p-4 space-y-2">
    <h4 class="font-semibold">{{ $label }}</h4>
    <div class="text-sm">{{ $hipo->{"control_$key"} }}</div>

    <div class="text-xs">
        <strong>PIC:</strong> {{ $hipo->{"pic_$key"} }}
    </div>

    @if($hipo->{"evidence_$key"})
        <a href="{{ asset('storage/'.$hipo->{"evidence_$key"}) }}"
           target="_blank"
           class="text-sm text-blue-600 underline">
            📎 Lihat Evidence
        </a>
    @endif
</div>
@endforeach
</div>

{{-- ADMIN ACTION --}}
<form method="POST"
      action="{{ route('admin.hipo.update', $hipo->id) }}"
      enctype="multipart/form-data"
      class="bg-white border rounded-xl p-6 space-y-4">

@csrf
@method('PUT')

<h3 class="font-semibold text-sm">🔧 Tindakan Admin</h3>

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="text-xs">PIC Utama</label>
        <input name="pic" value="{{ old('pic',$hipo->pic) }}"
               class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
        <label class="text-xs">Status</label>
        <select name="status" class="w-full border rounded-lg px-3 py-2">
            @foreach(['Open','On Progress','Closed','Rejected'] as $s)
                <option value="{{ $s }}" @selected($hipo->status===$s)>
                    {{ $s }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label class="text-xs">Catatan Admin</label>
    <textarea name="admin_note"
              class="w-full border rounded-lg px-3 py-2"
              rows="3">{{ old('admin_note',$hipo->admin_note) }}</textarea>
</div>

<div class="flex justify-end">
    <button class="px-5 py-2 border border-black rounded-lg hover:bg-gray-100">
        Simpan Perubahan
    </button>
</div>
</form>

</div>
@endsection
