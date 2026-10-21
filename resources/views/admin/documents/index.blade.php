@extends('layouts.app')

@section('content')
<x-ui.page class="max-w-7xl mx-auto min-w-0">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Documents</x-ui.page-title>
<x-ui.page-description>Kelola dokumen, revisi, dan hak akses.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link icon="plus" variant="primary" href="{{ route('admin.documents.create') }}"
       class="inline-flex justify-center px-4 py-2 rounded-lg hover:opacity-90">Buat Dokumen</x-ui.link></x-ui.page-actions>
</x-ui.page-header>

  <x-ui.panel :padded="false" class="max-w-full bg-white border rounded-lg overflow-x-auto">
    <x-ui.table class="w-full text-sm min-w-[980px]">
      <thead class="bg-[#1D1C1A] text-white">
        <tr>
          <th class="p-3 text-left">Template</th>
          <th class="p-3 text-left">Doc.No</th>
          <th class="p-3 text-left">Dept Code</th>
          <th class="p-3 text-left">Doc Type</th>
          <th class="p-3 text-left">Project Code</th>
          <th class="p-3 text-left">Revision</th>
          <th class="p-3 text-left">Effective Date</th>
          <th class="p-3 text-left">Title</th>
          <th class="p-3 text-left">Controlled Status</th>
          <th class="p-3 text-left">Class</th>
          <th class="p-3 text-left">Owner</th>
          <th class="p-3 w-40 text-right">Actions</th>
        </tr>
      </thead>

      <tbody>
      @forelse($docs as $d)
        <tr class="border-b hover:bg-[#713F12]/5">
          {{-- Template --}}
          <td class="p-3">
            @php
              // kalau ada relasi ->template, tampilkan id + nama; fallback ke template_id
              $tplLabel = $d->template->name ?? null;
            @endphp
            <div class="font-medium">#{{ $d->template_id ?? '—' }}</div>
            <div class="text-xs text-gray-600 truncate max-w-[180px]">
              {{ $tplLabel ?? '—' }}
            </div>
          </td>

          {{-- Doc.No --}}
          <td class="p-3 font-medium">{{ $d->doc_no ?? '—' }}</td>

          {{-- Dept --}}
          <td class="p-3">{{ $d->dept_code ?? '—' }}</td>

          {{-- Doc Type --}}
          <td class="p-3">{{ $d->doc_type ?? '—' }}</td>

          {{-- Project Code --}}
          <td class="p-3">{{ $d->project_code ?? '—' }}</td>

          {{-- Revision --}}
          <td class="p-3">{{ (int)($d->revision_no ?? 0) }}</td>

          {{-- Effective Date --}}
          <td class="p-3">
            @if(!empty($d->effective_date))
              {{ \Illuminate\Support\Carbon::parse($d->effective_date)->format('Y-m-d') }}
            @else
              —
            @endif
          </td>

          {{-- Title (wrap multiline kalau panjang) --}}
          <td class="p-3 whitespace-pre-line">{{ $d->title ?? '—' }}</td>

          {{-- Controlled Status (badge) --}}
          <td class="p-3">
            @php
              $status = $d->controlled_status ?? '-';
              $badge  = match($status) {
                'controlled'   => 'ui-status ui-status--success',
                'uncontrolled' => 'ui-status ui-status--warning',
                'obsolete'     => 'bg-gray-200 text-gray-700',
                default        => 'bg-gray-100 text-gray-700',
              };
            @endphp
            <span class="px-2 py-0.5 rounded text-xs {{ $badge }}">{{ $status }}</span>
          </td>

          {{-- Class --}}
          <td class="p-3">{{ $d->class ?? '—' }}</td>

          {{-- Owner --}}
          <td class="p-3">{{ $d->owner->name ?? '—' }}</td>

          {{-- Actions --}}
          <td class="ui-action-cell p-3 text-right"><x-ui.table-actions>
            <div class="inline-flex items-center gap-2">
              <x-ui.icon-button ability="view" :subject="$d" action="view" label="Lihat detail" href="{{ route('admin.documents.show',$d) }}" />

              <x-ui.icon-button ability="update" :subject="$d" action="edit" label="Edit" href="{{ route('admin.documents.edit',$d) }}" />

              <form action="{{ route('admin.documents.destroy',$d) }}" method="POST" data-confirm="Hapus dokumen ini?" class="inline">
                @csrf @method('DELETE')
                <x-ui.icon-button ability="delete" :subject="$d" action="delete" label="Hapus" type="submit" />
              </form>
            </div>
          </x-ui.table-actions></td>
        </tr>
      @empty
        <tr>
          <td colspan="12" class="p-6 text-center text-gray-500">Belum ada dokumen</td>
        </tr>
      @endforelse
      </tbody>
    </x-ui.table>
  </x-ui.panel>

  <div class="mt-4">{{ $docs->links() }}</div>
</x-ui.page>
@endsection
