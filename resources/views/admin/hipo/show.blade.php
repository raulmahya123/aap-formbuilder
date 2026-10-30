@extends('layouts.app')
@section('content')
<x-ui.page class="w-full min-w-0 text-black">

{{-- HEADER --}}
<x-ui.page-header>
  <div class="ui-page-header__copy"><div class="flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 border border-amber-200 text-amber-600">
            <x-ui-icon name="warning" class="h-6 w-6" />
        </div>
        <div>
            <x-ui.page-title>Detail HIPO / Nearmiss</x-ui.page-title>
            <x-ui.page-description>High Potential Hazard Report — #{{ $hipo->id }}</x-ui.page-description>
        </div>
    </div></div>
  <x-ui.page-actions><x-ui.back-link href="{{ route('admin.hipo.index') }}">Kembali</x-ui.back-link></x-ui.page-actions>
</x-ui.page-header>

{{-- STATUS + RISK LEVEL BANNER --}}
<x-ui.panel class="bg-white border rounded-xl overflow-hidden shadow-none">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center h-10 w-10 rounded-lg
                @if($hipo->status === 'Closed') bg-green-100 text-green-600
                @elseif($hipo->status === 'Rejected') bg-red-100 text-red-600
                @elseif($hipo->status === 'On Progress') bg-blue-100 text-blue-600
                @else bg-amber-100 text-amber-600 @endif">
            <x-ui.lucide name="{{ $hipo->status === 'Closed' ? 'circle-check' : ($hipo->status === 'Rejected' ? 'circle-x' : ($hipo->status === 'On Progress' ? 'loader-2' : 'alert-triangle')) }}" />
            </div>
            <div>
                <div class="text-xs font-medium text-gray-500">Status Laporan</div>
                <div class="text-lg font-bold tracking-tight
                    @if($hipo->status === 'Closed') text-green-700
                    @elseif($hipo->status === 'Rejected') text-red-700
                    @elseif($hipo->status === 'On Progress') text-blue-700
                    @else text-amber-700 @endif">
                    {{ strtoupper($hipo->status) }}
                </div>
                @if($hipo->status === 'Closed')
                    <p class="mt-1 text-xs text-gray-500">
                        Ditutup oleh {{ $hipo->closedBy?->name ?? 'Admin' }}
                        @if($hipo->closed_at) pada {{ $hipo->closed_at->format('d M Y, H:i') }} @endif
                    </p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div>
                <div class="text-xs font-medium text-gray-500 text-right">Risk Level</div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-bold
                    @if($hipo->risk_level === 'Low') bg-green-100 text-green-700 ring-1 ring-green-200
                    @elseif($hipo->risk_level === 'Medium') bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200
                    @elseif($hipo->risk_level === 'High') bg-orange-100 text-orange-700 ring-1 ring-orange-200
                    @elseif($hipo->risk_level === 'Extreme') bg-red-100 text-red-700 ring-1 ring-red-200
                    @else bg-gray-100 text-gray-700 ring-1 ring-gray-200 @endif">
                    <span class="w-2 h-2 rounded-full
                        @if($hipo->risk_level === 'Low') bg-green-500
                        @elseif($hipo->risk_level === 'Medium') bg-yellow-500
                        @elseif($hipo->risk_level === 'High') bg-orange-500
                        @elseif($hipo->risk_level === 'Extreme') bg-red-500
                        @else bg-gray-500 @endif"></span>
                    {{ $hipo->risk_level }}
                </span>
            </div>
        </div>
    </div>
</x-ui.panel>

{{-- INFO CARDS --}}
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
@foreach([
    ['icon' => 'users', 'label' => 'Pelapor', 'value' => $hipo->reporter_name],
    ['icon' => 'map-pin', 'label' => 'Jobsite', 'value' => $hipo->jobsite_display],
    ['icon' => 'calendar-days', 'label' => 'Waktu', 'value' => $hipo->report_time?->format('d M Y H:i')],
    ['icon' => 'rotate-ccw', 'label' => 'Shift', 'value' => $hipo->shift],
    ['icon' => 'file-text', 'label' => 'Sumber', 'value' => $hipo->source],
    ['icon' => 'tag', 'label' => 'Kategori', 'value' => $hipo->category],
    ['icon' => 'alert-triangle', 'label' => 'Jenis', 'value' => $hipo->jenis_hipo],
    ['icon' => 'activity', 'label' => 'Konsekuensi', 'value' => $hipo->potential_consequence],
    ['icon' => 'shield', 'label' => 'Stop Work', 'value' => $hipo->stop_work ? 'YA' : 'TIDAK'],
    ['icon' => 'user-check', 'label' => 'PIC Utama', 'value' => $hipo->pic],
    ['icon' => 'shield-check', 'label' => 'Admin Site', 'value' => $hipo->assignedAdmin?->name ?? 'Belum ditugaskan'],
] as $item)
<x-ui.panel class="bg-white border rounded-xl shadow-none">
    <div class="flex items-start gap-2.5">
        <div class="flex-shrink-0 mt-0.5 text-gray-400"><x-ui.lucide name="{{ $item['icon'] }}" class="w-4 h-4" /></div>
        <div class="min-w-0">
            <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">{{ $item['label'] }}</div>
            <div class="text-sm font-semibold text-gray-800 truncate">{{ $item['value'] ?? '—' }}</div>
        </div>
    </div>
</x-ui.panel>
@endforeach
</div>

{{-- RINCIAN KEJADIAN --}}
<x-ui.panel class="bg-white border rounded-xl shadow-none space-y-5">
    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600">
            <x-ui.lucide name="file-text" class="w-4 h-4" />
        </div>
        <x-ui.card-title class="font-bold text-gray-800">Rincian Kejadian</x-ui.card-title>
    </div>
    @foreach([
        ['label' => 'KTA (Kondisi Tidak Aman)', 'value' => $hipo->kta, 'color' => 'red'],
        ['label' => 'TTA (Tindakan Tidak Aman)', 'value' => $hipo->tta, 'color' => 'orange'],
        ['label' => 'Deskripsi Kejadian', 'value' => $hipo->description, 'color' => 'blue'],
    ] as $detail)
    <div class="rounded-lg border border-gray-100 overflow-hidden">
        <div class="px-4 py-2 bg-gray-50 border-b border-gray-100">
            <span class="text-xs font-bold text-gray-600 uppercase tracking-wide">{{ $detail['label'] }}</span>
        </div>
        <div class="px-4 py-3 text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $detail['value'] ?? '—' }}</div>
    </div>
    @endforeach
</x-ui.panel>

{{-- KONTROL RISIKO --}}
<x-ui.panel class="bg-white border rounded-xl shadow-none space-y-4">
    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600">
            <x-ui.lucide name="shield" class="w-4 h-4" />
        </div>
        <x-ui.card-title class="font-bold text-gray-800">Pengendalian & Kontrol Risiko</x-ui.card-title>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
    @foreach([
        'engineering' => ['label' => 'Rekayasa Engineering', 'icon' => 'wrench', 'color' => 'blue'],
        'administrative' => ['label' => 'Administratif', 'icon' => 'clipboard-list', 'color' => 'violet'],
        'work_practice' => ['label' => 'Praktek Kerja', 'icon' => 'shield', 'color' => 'amber'],
        'ppe' => ['label' => 'APD', 'icon' => 'shield-check', 'color' => 'emerald'],
    ] as $key => $meta)
    <div class="rounded-xl border border-gray-200 overflow-hidden bg-gray-50/50 hover:shadow-sm transition-shadow">
        <div class="px-4 py-2.5 bg-white border-b border-gray-100 flex items-center gap-2">
            <x-ui.lucide name="{{ $meta['icon'] }}" class="w-4 h-4 text-gray-500" />
            <h4 class="text-sm font-bold text-gray-700">{{ $meta['label'] }}</h4>
        </div>
        <div class="px-4 py-3">
            <div class="text-sm text-gray-600 whitespace-pre-line leading-relaxed">
                {{ $hipo->{"control_$key"} ?? '—' }}
            </div>
            @if($hipo->{"evidence_$key"})
                <a href="{{ route('pubfile.stream', ['path' => $hipo->{"evidence_$key"}]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                    <x-ui.lucide name="image" class="w-3.5 h-3.5" /> Lihat Evidence
                </a>
            @endif
        </div>
    </div>
    @endforeach
    </div>
</x-ui.panel>

{{-- ======================== TINDAKAN ADMIN (REDESIGNED) ======================== --}}
<form method="POST"
      action="{{ route('admin.hipo.update', $hipo->id) }}"
      class="rounded-xl border border-gray-200 bg-white shadow-none overflow-hidden">
@csrf
@method('PUT')

    {{-- Card Header --}}
    <div class="px-6 py-4 bg-zinc-50 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-100 text-blue-700">
                <x-ui.lucide name="wrench" class="w-4.5 h-4.5" />
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-800">Tindakan Admin</h3>
                <p class="text-xs text-gray-500">Admin Site yang ditugaskan mengelola tindak lanjut dan penutupan temuan pada site ini.</p>
            </div>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="p-6 space-y-5">
        <div class="grid md:grid-cols-2 gap-5">
            {{-- PIC Utama --}}
            <div class="space-y-1.5">
                <label class="flex items-center gap-1.5 text-sm font-semibold text-gray-700">
                    <x-ui.lucide name="user-check" class="w-4 h-4 text-gray-400" />
                    PIC Utama
                </label>
                <x-ui.user-name-select
                    name="pic"
                    :users="$picUsers"
                    :value="old('pic', $hipo->pic)"
                    aria-label="PIC Utama"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow" />
                @error('pic') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Status --}}
            <div class="space-y-1.5">
                <label class="flex items-center gap-1.5 text-sm font-semibold text-gray-700">
                    <x-ui.lucide name="activity" class="w-4 h-4 text-gray-400" />
                    Status
                </label>
                <select name="status"
                        class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                    @foreach(['Open','On Progress','Closed','Rejected'] as $s)
                        <option value="{{ $s }}" @selected($hipo->status===$s)
                            @disabled(!$canClose && $s !== $hipo->status && ($s === 'Closed' || $hipo->status === 'Closed'))>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
                @unless($canClose)
                    <p class="text-xs text-gray-500">Hanya Admin Site yang ditugaskan, Admin HO, atau Super Admin yang dapat menutup atau membuka kembali temuan.</p>
                @endunless
                @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="max-w-xl space-y-1.5">
            <label for="assigned-admin-id" class="flex items-center gap-1.5 text-sm font-semibold text-gray-700">
                <x-ui.lucide name="shield-check" class="w-4 h-4 text-gray-500" />
                Admin Site penanggung jawab
            </label>
            <select id="assigned-admin-id" name="assigned_admin_id" class="w-full ui-input">
                <option value="">Belum ditugaskan</option>
                @if($siteAdmins->isEmpty())
                    <option value="" disabled>Tidak ada Admin Site aktif untuk site ini</option>
                @endif
                @foreach($siteAdmins as $admin)
                    <option value="{{ $admin->id }}" @selected((string) old('assigned_admin_id', $hipo->assigned_admin_id) === (string) $admin->id)>
                        {{ $admin->name }} — {{ $admin->roleLabel() }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500">
                {{ $siteAdmins->isEmpty() ? 'Belum ada Admin Site aktif dengan akses ke site laporan ini.' : 'Admin Site yang ditugaskan dapat menutup temuan untuk site ini. Admin HO dan Super Admin tetap dapat mengelola semua site.' }}
            </p>
            @if(auth()->user()->canUseFeature('site_access'))
                <a href="{{ route('admin.site_access.index', ['site_id' => $hipo->site_id]) }}" class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 hover:underline">
                    <x-ui.lucide name="user-round-plus" class="h-3.5 w-3.5" /> Atur role dan akses site
                </a>
            @endif
            @error('assigned_admin_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Catatan Admin --}}
        <div class="space-y-1.5">
            <label class="flex items-center gap-1.5 text-sm font-semibold text-gray-700">
                <x-ui.lucide name="message-square" class="w-4 h-4 text-gray-400" />
                Catatan Admin
            </label>
            <textarea name="admin_note"
                      class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow resize-y"
                      rows="3"
                      placeholder="Tambahkan catatan atau instruksi tindak lanjut...">{{ old('admin_note', $hipo->admin_note) }}</textarea>
            @error('admin_note') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Card Footer --}}
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <p class="text-xs text-gray-400">Perubahan akan langsung diterapkan setelah disimpan</p>
        <x-ui.button variant="primary" class="px-6 py-2.5 rounded-lg font-semibold shadow-sm hover:shadow-md transition-all">
            <x-ui.lucide name="save" class="w-4 h-4" /> Simpan Perubahan
        </x-ui.button>
    </div>
</form>

</x-ui.page>
@endsection
