@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
  <div class="flex items-center justify-between mb-5">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Documents</h1>
    <a href="{{ route('admin.documents.create') }}"
       class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Buat Dokumen</a>
  </div>

  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm min-w-[980px]">
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
        <tr class="border-b hover:bg-[#7A2C2F]/5">
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
                'controlled'   => 'bg-emerald-100 text-emerald-700',
                'uncontrolled' => 'bg-amber-100 text-amber-700',
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
          <td class="p-3 text-right">
            <div class="inline-flex items-center gap-2">
              <a class="text-[#7A2C2F] hover:underline" href="{{ route('admin.documents.show',$d) }}">Lihat</a>
              <span class="text-gray-300">|</span>
              <a class="text-[#1D1C1A] hover:underline" href="{{ route('admin.documents.edit',$d) }}">Edit</a>
              <span class="text-gray-300">|</span>
              <form action="{{ route('admin.documents.destroy',$d) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')" class="inline">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="12" class="p-6 text-center text-gray-500">Belum ada dokumen</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $docs->links() }}</div>
</div>
@endsection
