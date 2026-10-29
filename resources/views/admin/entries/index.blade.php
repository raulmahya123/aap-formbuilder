@extends('layouts.app')
@section('content')
<x-ui.page>
  <div class="w-full min-w-0 ">
    <!-- HEADER -->
    <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Entries</x-ui.page-title>
<x-ui.page-description>Lihat isian form, status, dan berkas pengajuan.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link variant="primary" class="px-3 py-2 rounded-lg text-ivory-50 transition text-sm"
        href="{{ route('admin.entries.export', request()->query()) }}">
        ⬇️ Export CSV
      </x-ui.link></x-ui.page-actions>

<x-slot:filters>
<x-ui.table-toolbar class="mb-4">
      <x-ui.table-search name="q" value="{{ request('q') }}" aria-label="Cari data isian" placeholder="Cari data (contoh: nama)"
        class="w-full" />
      <div class="ui-filter-field"><label class="ui-filter-field__label" for="entries-user-filter">Pengguna</label><input id="entries-user-filter" type="text" name="user" value="{{ request('user') }}" aria-label="Nama atau email pengguna" placeholder="Nama atau email pengguna"
        class="w-full ui-input">
      </div><div class="ui-filter-field"><label class="ui-filter-field__label" for="entries-form-filter">ID Form</label><input id="entries-form-filter" type="number" name="form_id" value="{{ request('form_id') }}" placeholder="ID Form"
        class="w-full ui-input">
      </div><div class="ui-filter-field"><label class="ui-filter-field__label" for="entries-status-filter">Status</label><select id="entries-status-filter" name="status"
        class="w-full ui-input">
        <option value="">— Semua Status —</option>
        <option value="submitted" @selected(request('status')==='submitted' )>Submitted</option>
        <option value="reviewed" @selected(request('status')==='reviewed' )>Reviewed</option>
        <option value="approved" @selected(request('status')==='approved' )>Approved</option>
        <option value="rejected" @selected(request('status')==='rejected' )>Rejected</option>
      </select></div>

<x-slot:actions>
<div class="md:col-span-4">
        <x-ui.button variant="primary" class="w-full md:w-auto">
          Terapkan Filter
        </x-ui.button>
        <x-ui.link variant="secondary"
          href="{{ route('admin.entries.export_zip', request()->query()) }}">
          <x-ui.lucide name="download" /> Export ZIP
        </x-ui.link>
      </div>

</x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>
    <!-- FILTER FORM -->

    <!-- TABLE -->
    <div class="rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none overflow-x-auto">
      <x-ui.table :paginator="$entries" class="w-full text-sm min-w-[800px]">
        <thead class="bg-ivory-100 dark:bg-coal-800/60 text-coal-700 dark:text-coal-300">
          <tr>
            <th class="text-left p-3">#</th>
            <th class="text-left p-3">Form</th>
            <th class="text-left p-3">Department</th>
            <th class="text-left p-3">User</th>
            <th class="text-left p-3">Waktu</th>
            <th class="text-left p-3">Status</th>
            <th class="text-left p-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($entries as $e)
          <tr class="border-t dark:border-coal-800/70 hover:bg-ivory-100 dark:hover:bg-coal-800/50">
            <td class="p-3">{{ $e->id }}</td>
            <td class="p-3">{{ $e->form->title ?? '-' }}</td>
            <td class="p-3">{{ $e->form->department->name ?? '-' }}</td>
            <td class="p-3">
              {{ $e->user->name ?? '-' }}
              <span class="text-coal-500 dark:text-coal-400">({{ $e->user->email ?? '' }})</span>
            </td>
            <td class="p-3">{{ $e->created_at->format('d/m/Y H:i') }}</td>
            <td class="p-3">
              <span class="px-2 py-1 rounded-lg text-xs font-medium @if($e->status==='approved') bg-emerald-100 text-emerald-800 @elseif($e->status==='rejected') bg-rose-100 text-rose-800 @elseif($e->status==='reviewed') bg-amber-100 text-amber-800 @else bg-slate-100 text-slate-800 @endif">
                {{ strtoupper($e->status) }}
              </span>
            </td>
            <td class="ui-action-cell p-3"><x-ui.table-actions>
              <div class="flex flex-nowrap items-center gap-2">
                <x-ui.icon-button ability="view" :subject="$e" action="view" label="Lihat detail" href="{{ route('admin.entries.show',$e) }}" />
                <x-ui.icon-button ability="view" :subject="$e->form" action="pdf" label="Unduh data entri sebagai PDF" href="{{ route('admin.entries.data_pdf',$e) }}" />
                @if($e->pdf_output_path)
                <x-ui.icon-button ability="view" :subject="$e" action="download" label="Unduh lampiran PDF" href="{{ route('admin.entries.download_pdf',$e) }}" />
                @endif
                <form action="{{ route('admin.entries.destroy',$e) }}" method="post"
                  data-confirm="Hapus entry?">
                  @csrf @method('DELETE')
                  <x-ui.icon-button ability="delete" :subject="$e" action="delete" label="Hapus" type="submit" />
                </form>
              </div>
            </x-ui.table-actions></td>
          </tr>
          @empty
<x-ui.table-empty-state colspan="7"></x-ui.table-empty-state>
@endforelse
        </tbody>
      </x-ui.table>
    </div>
    <!-- PAGINATION -->

  </div>
</x-ui.page>
@endsection
