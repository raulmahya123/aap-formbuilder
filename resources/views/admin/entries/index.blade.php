@extends('layouts.app')

@section('content')
<div
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-6xl mx-auto p-4 sm:p-6">
    <!-- HEADER -->
    <div class="flex items-center justify-between mb-4 sm:mb-6">
      <h1 class="text-2xl font-serif tracking-tight">Entries</h1>
      <a class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 transition text-sm"
         href="{{ route('admin.entries.export', request()->query()) }}">
        ⬇️ Export CSV
      </a>
    </div>

    <!-- FILTER FORM -->
    <form method="get" class="mb-4 grid md:grid-cols-4 gap-3">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari data (contoh: nama)"
             class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700
                    focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">

      <input type="text" name="user" value="{{ request('user') }}" placeholder="Nama/Email user"
             class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700
                    focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">

      <input type="number" name="form_id" value="{{ request('form_id') }}" placeholder="Form ID"
             class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700
                    focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">

      <select name="status"
              class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700
                     focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">
        <option value="">— Semua Status —</option>
        <option value="submitted" @selected(request('status')==='submitted')>Submitted</option>
        <option value="reviewed" @selected(request('status')==='reviewed')>Reviewed</option>
        <option value="approved" @selected(request('status')==='approved')>Approved</option>
        <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
      </select>

      <div class="md:col-span-4">
        <button class="px-4 py-2 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 transition text-sm w-full md:w-auto">
          Terapkan Filter
        </button>
      </div>
    </form>

    <!-- TABLE -->
    <div class="rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft overflow-x-auto">
      <table class="w-full text-sm min-w-[800px]">
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
                <span class="px-2 py-1 rounded text-xs font-medium
                  @if($e->status==='approved') bg-emerald-100 text-emerald-800
                  @elseif($e->status==='rejected') bg-rose-100 text-rose-800
                  @elseif($e->status==='reviewed') bg-amber-100 text-amber-800
                  @else bg-slate-100 text-slate-800 @endif">
                  {{ strtoupper($e->status) }}
                </span>
              </td>
              <td class="p-3">
                <div class="flex flex-wrap items-center gap-1.5">
                  <a href="{{ route('admin.entries.show',$e) }}"
                     class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition no-underline">
                    Detail
                  </a>

                  @if($e->pdf_output_path)
                    <a href="{{ route('admin.entries.download_pdf',$e) }}"
                       class="px-2.5 py-1 rounded-full border border-maroon-600 text-maroon-700 hover:bg-maroon-50/60
                              dark:text-maroon-300 dark:hover:bg-maroon-900/20 text-[12px] transition no-underline">
                      PDF
                    </a>
                  @endif

                  <form action="{{ route('admin.entries.destroy',$e) }}" method="post"
                        onsubmit="return confirm('Hapus entry?')">
                    @csrf @method('DELETE')
                    <button
                      class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition">
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="p-6 text-center text-coal-500 dark:text-coal-400">Belum ada entry.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
      {{ $entries->links() }}
    </div>
  </div>
</div>
@endsection
