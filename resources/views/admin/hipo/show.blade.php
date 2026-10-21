@extends('layouts.app')
@section('content')
<x-ui.page class="max-w-6xl mx-auto text-black">
{{-- FLASH MESSAGE --}}
{{-- HEADER --}}
<x-ui.page-header>
  <div class="ui-page-header__copy"><div class="flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-[#D4D4D8] text-[#713F12]"><x-ui-icon name="warning" class="h-6 w-6" /></div>
        <div>
            <x-ui.page-title>Detail HIPO / Nearmiss</x-ui.page-title>
            <x-ui.page-description>High Potential Hazard Report</x-ui.page-description>
        </div>
    </div></div>
  <x-ui.page-actions><x-ui.link variant="secondary" href="{{ route('admin.hipo.index') }}"
       class="text-sm px-4 py-2 rounded-lg border">
        ← Kembali
    </x-ui.link></x-ui.page-actions>
</x-ui.page-header>
{{-- STATUS --}}
<x-ui.panel class="flex items-center justify-between bg-white border rounded-lg">
    <div>
        <div class="text-xs">Status</div>
        <div class="text-xl font-bold">{{ strtoupper($hipo->status) }}</div>
    </div>
    <div class="text-right">
        <div class="text-xs">Risk Level</div>
        <span class="px-3 py-1 rounded-lg text-sm font-bold @class([ 'ui-status ui-status--success' => $hipo->risk_level==='Low', 'ui-status ui-status--warning' => $hipo->risk_level==='Medium', 'ui-status ui-status--warning' => $hipo->risk_level==='High', 'ui-status ui-status--danger' => $hipo->risk_level==='Extreme', ])">
            {{ $hipo->risk_level }}
        </span>
    </div>
</x-ui.panel>
{{-- INFO --}}
<div class="grid md:grid-cols-4 gap-4 text-sm">
@foreach([
    'Pelapor' => $hipo->reporter_name,
    'Jobsite' => $hipo->jobsite,
    'Waktu' => $hipo->report_time?->format('d M Y H:i'),
    'Shift' => $hipo->shift,
    'Sumber' => $hipo->source,
    'Kategori' => $hipo->category,
    'Jenis' => $hipo->jenis_hipo,
    'Konsekuensi' => $hipo->potential_consequence,
    'Stop Work' => $hipo->stop_work ? 'YA' : 'TIDAK',
    'PIC Utama' => $hipo->pic,
] as $label => $value)
<x-ui.panel class="bg-white border rounded-lg">
    <div class="text-xs">{{ $label }}</div>
    <div class="font-semibold">{{ $value }}</div>
</x-ui.panel>
@endforeach
</div>
{{-- RINCIAN KEJADIAN --}}
<x-ui.panel class="bg-white border rounded-lg space-y-4">
    <x-ui.card-title class="font-semibold">📝 Rincian Kejadian</x-ui.card-title>
    <div>
        <div class="text-xs font-semibold">KTA (Kondisi Tidak Aman)</div>
        <div class="text-sm whitespace-pre-line">{{ $hipo->kta }}</div>
    </div>
    <div>
        <div class="text-xs font-semibold">TTA (Tindakan Tidak Aman)</div>
        <div class="text-sm whitespace-pre-line">{{ $hipo->tta }}</div>
    </div>
    <div>
        <div class="text-xs font-semibold">Deskripsi Kejadian</div>
        <div class="text-sm whitespace-pre-line">{{ $hipo->description }}</div>
    </div>
</x-ui.panel>
{{-- KONTROL RISIKO --}}
<div class="grid md:grid-cols-2 gap-4">
@foreach([
    'engineering' => 'Rekayasa Engineering',
    'administrative' => 'Administratif',
    'work_practice' => 'Praktek Kerja',
    'ppe' => 'APD'
] as $key => $label)
<x-ui.panel class="bg-white border rounded-lg space-y-2">
    <h4 class="font-semibold">{{ $label }}</h4>
    <div class="text-sm whitespace-pre-line">
        {{ $hipo->{"control_$key"} }}
    </div>
    @if($hipo->{"evidence_$key"})
        <a href="{{ route('pubfile.stream', ['path' => $hipo->{"evidence_$key"}]) }}"
           target="_blank"
           class="text-sm text-blue-600 underline">
            📎 Lihat Evidence
        </a>
    @endif
</x-ui.panel>
@endforeach
</div>
{{-- ADMIN ACTION --}}
<form method="POST"
      action="{{ route('admin.hipo.update', $hipo->id) }}"
      class="ui-card bg-white border rounded-lg space-y-4">
@csrf
@method('PUT')
<x-ui.card-title class="font-semibold text-sm">🔧 Tindakan Admin</x-ui.card-title>
<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="text-xs">PIC Utama</label>
        <input name="pic"
               value="{{ old('pic',$hipo->pic) }}"
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
    <x-ui.button variant="primary" class="px-5 py-2 border rounded-lg">
        Simpan Perubahan
    </x-ui.button>
</div>
</form>
</x-ui.page>
@endsection