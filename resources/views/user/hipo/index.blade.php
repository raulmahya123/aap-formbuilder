@extends('layouts.app')

@section('content')
<x-ui.page class="max-w-6xl mx-auto">

    <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Laporan HIPO / Nearmiss</x-ui.page-title>
<x-ui.page-description>Catat dan pantau laporan HIPO serta Nearmiss.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link icon="plus" variant="primary" href="{{ route('user.hipo.create') }}"
           class="px-4 py-2 rounded-lg text-sm">Buat Laporan
        </x-ui.link></x-ui.page-actions>
</x-ui.page-header>

    <x-ui.panel :padded="false" class="bg-white border rounded-lg overflow-x-auto">
        <x-ui.table class="w-full text-sm">
            <thead class="bg-ivory-100 border-b">
                <tr>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Jobsite</th>
                    <th class="px-4 py-2 text-left">Jenis</th>
                    <th class="px-4 py-2 text-left">Kategori</th>
                    <th class="px-4 py-2 text-left">Risk</th>
                    <th class="px-4 py-2 text-left">KTA</th>
                    <th class="px-4 py-2 text-left">TTA</th>
                    <th class="px-4 py-2 text-left">Konsekuensi</th>
                    <th class="px-4 py-2 text-left">PIC</th>
                    <th class="px-4 py-2 text-center">Stop Work</th>
                    <th class="px-4 py-2 text-center">Status</th>
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($reports as $r)
                <tr class="border-b hover:bg-ivory-50 align-top">

                    {{-- Tanggal --}}
                    <td class="px-4 py-2 whitespace-nowrap">
                        {{ $r->report_time?->format('d M Y') }}
                    </td>

                    {{-- Jobsite --}}
                    <td class="px-4 py-2">
                        {{ $r->jobsite }}
                    </td>

                    {{-- Jenis HIPO --}}
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $r->jenis_hipo === 'HIPO'
                                ? 'ui-status ui-status--danger'
                                : 'ui-status ui-status--info' }}">
                            {{ $r->jenis_hipo }}
                        </span>
                    </td>

                    {{-- Kategori --}}
                    <td class="px-4 py-2">
                        {{ $r->category }}
                    </td>

                    {{-- Risk Level --}}
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded-lg text-xs @class([ 'ui-status ui-status--success' => $r->risk_level === 'Low', 'ui-status ui-status--warning' => $r->risk_level === 'Medium', 'ui-status ui-status--warning' => $r->risk_level === 'High', 'ui-status ui-status--danger' => $r->risk_level === 'Extreme', ])">
                            {{ $r->risk_level }}
                        </span>
                    </td>

                    {{-- KTA --}}
                    <td class="px-4 py-2 text-xs text-gray-700">
                        {{ \Illuminate\Support\Str::limit($r->kta, 50) }}
                    </td>

                    {{-- TTA --}}
                    <td class="px-4 py-2 text-xs text-gray-700">
                        {{ \Illuminate\Support\Str::limit($r->tta, 50) }}
                    </td>

                    {{-- Konsekuensi --}}
                    <td class="px-4 py-2">
                        {{ $r->potential_consequence }}
                    </td>

                    {{-- PIC --}}
                    <td class="px-4 py-2">
                        {{ $r->pic }}
                    </td>

                    {{-- Stop Work --}}
                    <td class="px-4 py-2 text-center">
                        @if($r->stop_work)
                            <x-ui.badge variant="danger" class="px-2 py-1 rounded-lg text-xs">
                                Ya
                            </x-ui.badge>
                        @else
                            <span class="px-2 py-1 rounded-lg text-xs bg-gray-100 text-gray-600">
                                Tidak
                            </span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-2 text-center">
                        <span class="px-2 py-1 rounded-lg text-xs @class([ 'ui-status ui-status--danger' => $r->status === 'Open', 'ui-status ui-status--warning' => $r->status === 'On Progress', 'ui-status ui-status--success' => $r->status === 'Closed', 'bg-gray-200 text-gray-700' => $r->status === 'Rejected', ])">
                            {{ $r->status }}
                        </span>
                    </td>

                    {{-- Aksi --}}
                    <td class="ui-action-cell px-4 py-2 text-center whitespace-nowrap"><x-ui.table-actions>
                        <x-ui.icon-button action="view" label="Lihat detail" href="{{ route('admin.hipo.show', $r->id) }}" />
                    </x-ui.table-actions></td>
                </tr>
                @empty
                <tr>
                    <td colspan="12"
                        class="px-4 py-6 text-center text-coal-500">
                        Belum ada laporan HIPO / Nearmiss
                    </td>
                </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.panel>
    @if($reports->hasPages())
      <div class="mt-4">{{ $reports->links() }}</div>
    @endif

</x-ui.page>
@endsection
