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
<x-ui.table-toolbar class="mb-4 grid md:grid-cols-4 gap-3">
      <x-ui.table-search   name="q" value="{{ request('q') }}" placeholder="Cari data (contoh: nama)"
        class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700 focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500" />
      <input type="text" name="user" value="{{ request('user') }}" placeholder="Nama/Email user"
        class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700 focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">
      <input type="number" name="form_id" value="{{ request('form_id') }}" placeholder="Form ID"
        class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700 focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">
      <select name="status"
        class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700 focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">
        <option value="">— Semua Status —</option>
        <option value="submitted" @selected(request('status')==='submitted' )>Submitted</option>
        <option value="reviewed" @selected(request('status')==='reviewed' )>Reviewed</option>
        <option value="approved" @selected(request('status')==='approved' )>Approved</option>
        <option value="rejected" @selected(request('status')==='rejected' )>Rejected</option>
      </select>

<x-slot:actions>
<div class="md:col-span-4">
        <x-ui.button variant="primary" class="px-4 py-2 rounded-lg text-ivory-50 transition text-sm w-full md:w-auto">
          Terapkan Filter
        </x-ui.button>
        <x-ui.link variant="secondary" class="px-3 py-2 rounded-lg border dark:text-maroon-300 dark:border-maroon-400 dark:hover:bg-maroon-900/20 transition text-sm"
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
                @if($e->pdf_output_path)
                <x-ui.icon-button ability="view" :subject="$e" action="download" label="Unduh" href="{{ route('admin.entries.download_pdf',$e) }}" />
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