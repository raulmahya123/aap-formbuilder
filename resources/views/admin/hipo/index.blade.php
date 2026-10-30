@extends('layouts.app')
@section('content')
<x-ui.page class="">
    {{-- HEADER --}}
    <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
            <x-ui.page-title>HIPO / Nearmiss</x-ui.page-title>
            <x-ui.page-description>Daftar laporan HIPO & Nearmiss</x-ui.page-description>
        </div></div>
  <x-ui.page-actions><div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
            {{-- Search --}}
            {{-- CREATE BUTTON --}}
            <x-ui.link icon="plus" variant="primary" href="{{ route('admin.hipo.create') }}"
               class="inline-flex justify-center px-4 py-2 rounded-lg text-sm font-semibold shadow-none transition duration-200">Tambah HIPO
            </x-ui.link>
        </div></x-ui.page-actions>

<x-slot:filters>
<x-ui.table-toolbar><x-ui.table-search name="search" placeholder="Cari jobsite, pelapor, atau laporan…" aria-label="Cari HIPO" /><x-ui.table-filter name="status" :options="['Open'=>'Open','On Progress'=>'On Progress','Closed'=>'Closed','Rejected'=>'Rejected']" label="Status" placeholder="Semua status" /><x-ui.table-filter name="risk_level" :options="['Low'=>'Low','Medium'=>'Medium','High'=>'High','Extreme'=>'Extreme']" label="Risiko" placeholder="Semua risiko" />
<x-slot:actions>
<x-ui.link variant="secondary" :href="url()->current()">Reset</x-ui.link>
</x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>
{{-- TABLE CARD --}}
    <x-ui.panel :padded="false" class="bg-white rounded-lg shadow-none border overflow-hidden">
        <div class="overflow-x-auto">
            <x-ui.table :paginator="$reports" class="w-full min-w-[760px] text-sm">
                <thead class="bg-gray-100 text-gray-700 normal-case text-xs">
                    <tr>
                        <th class="text-left py-3 px-3">Tanggal</th>
                        <th class="text-left py-3 px-3">Jobsite</th>
                        <th class="text-left py-3 px-3">Jenis</th>
                        <th class="text-left py-3 px-3">Kategori</th>
                        <th class="text-left py-3 px-3">Risk</th>
                        <th class="text-left py-3 px-3">PIC</th>
                        <th class="text-left py-3 px-3">Admin Site</th>
                        <th class="text-center py-3 px-3">Stop Work</th>
                        <th class="text-center py-3 px-3">Status</th>
                        <th class="text-center py-3 px-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                    <tr class="border-t hover:bg-gray-50 transition">
                        {{-- Tanggal --}}
                        <td class="py-3 px-3 whitespace-nowrap">
                            {{ $r->report_time?->format('d M Y') }}
                        </td>
                        {{-- Jobsite --}}
                        <td class="py-3 px-3 font-medium text-gray-700">
                            {{ $r->jobsite_display }}
                        </td>
                        {{-- Jenis --}}
                        <td class="py-3 px-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $r->jenis_hipo === 'HIPO'
                                    ? 'ui-status ui-status--danger'
                                    : 'ui-status ui-status--info' }}">
                                {{ $r->jenis_hipo }}
                            </span>
                        </td>
                        {{-- Kategori --}}
                        <td class="py-3 px-3 text-gray-600">
                            {{ $r->category }}
                        </td>
                        {{-- Risk --}}
                        <td class="py-3 px-3">
                            <x-ui.status-badge :variant="match($r->risk_level) { 'Low'=>'success', 'Extreme'=>'danger', default=>'warning' }">{{ $r->risk_level }}</x-ui.status-badge>
                        </td>
                        {{-- PIC --}}
                        <td class="py-3 px-3 text-gray-600">
                            {{ $r->pic ?? '-' }}
                        </td>
                        <td class="py-3 px-3 text-gray-600">
                            {{ $r->assignedAdmin?->name ?? 'Belum ditugaskan' }}
                        </td>
                        {{-- Stop Work --}}
                        <td class="py-3 px-3 text-center">
                            @if($r->stop_work)
                                <x-ui.badge variant="danger" class="px-2 py-1 rounded-full text-xs">
                                    YA
                                </x-ui.badge>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">
                                    TIDAK
                                </span>
                            @endif
                        </td>
                        {{-- Status --}}
                        <td class="py-3 px-3 text-center">
                            <x-ui.status-badge :variant="match($r->status) { 'Closed'=>'success', 'Open'=>'danger', 'On Progress'=>'warning', default=>'neutral' }">{{ $r->status }}</x-ui.status-badge>
                        </td>
                        {{-- Aksi --}}
                        <td class="ui-action-cell py-3 px-3 text-center whitespace-nowrap"><x-ui.table-actions>
                            <div class="flex justify-center gap-3">
                                <x-ui.icon-button action="view" label="Lihat detail" href="{{ route('admin.hipo.show', $r->id) }}" /><x-ui.icon-button data-modal-form action="edit" label="Edit laporan" href="{{ route('admin.hipo.show', $r->id) }}?edit=1" />
                                <form action="{{ route('admin.hipo.destroy', $r->id) }}"
                                      method="POST"
                                      data-confirm="Yakin ingin hapus data ini?">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.icon-button action="delete" label="Hapus" type="submit" />
                                </form>
                            </div>
                        </x-ui.table-actions></td>
                    </tr>
                    @empty
<x-ui.table-empty-state colspan="10"></x-ui.table-empty-state>
@endforelse
                </tbody>
            </x-ui.table>
        </div>

    </x-ui.panel>
</x-ui.page>
@endsection
