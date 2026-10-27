@props(['record', 'module'])
<x-ui.icon-button action="print" label="Cetak laporan" href="{{ route('hse.dashboard.sap.record.export', [$module, $record->id, 'print']) }}" target="_blank" rel="noopener" />
<x-ui.icon-button action="pdf" label="Unduh PDF laporan" href="{{ route('hse.dashboard.sap.record.export', [$module, $record->id, 'pdf']) }}" />
@if(\App\Support\SapRecordActions::canDelete(auth()->user(), $record))
    <form method="POST" action="{{ route('hse.dashboard.sap.record.destroy', [$module, $record->id]) }}" data-confirm="Hapus draft {{ $record->inspection_no ?? $record->pto_no ?? $record->hazard_no }}?">
        @csrf @method('DELETE')
        <x-ui.icon-button action="delete" label="Hapus draft" type="submit" />
    </form>
@endif
