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
      <h1 class="text-2xl font-serif tracking-tight">Entry #{{ $entry->id }}</h1>
      @if($entry->pdf_output_path)
        <a href="{{ route('admin.entries.download_pdf', $entry) }}"
           class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 transition text-sm">
          ⬇️ Unduh PDF
        </a>
      @endif
    </div>

    <!-- INFO & AKSI -->
    <div class="grid md:grid-cols-2 gap-4 sm:gap-6">
      <div class="rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft p-4 sm:p-5">
        <h2 class="font-medium mb-3">Info</h2>
        <div class="text-sm space-y-1.5">
          <div><b>Form:</b> {{ $entry->form->title }}</div>
          <div><b>User:</b> {{ $entry->user->name }} <span class="text-coal-500 dark:text-coal-400">({{ $entry->user->email }})</span></div>
          <div><b>Tanggal:</b> {{ $entry->created_at->format('d/m/Y H:i') }}</div>
          <div class="pt-1.5">
            <b>Status:</b>
            <span class="px-2 py-1 rounded text-xs font-medium
              @if($entry->status==='approved') bg-emerald-100 text-emerald-800
              @elseif($entry->status==='rejected') bg-rose-100 text-rose-800
              @elseif($entry->status==='reviewed') bg-amber-100 text-amber-800
              @else bg-slate-100 text-slate-800 @endif">
              {{ strtoupper($entry->status) }}
            </span>
          </div>
        </div>
      </div>

      <div class="rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft p-4 sm:p-5">
        <h2 class="font-medium mb-3">Aksi</h2>
        <div class="text-sm">
          @if($entry->pdf_output_path)
            <a href="{{ route('admin.entries.download_pdf', $entry) }}"
               class="inline-flex items-center px-3 py-1.5 rounded-lg border border-maroon-600 text-maroon-700 hover:bg-maroon-50/60
                      dark:text-maroon-300 dark:hover:bg-maroon-900/20 transition">
              Lihat / Unduh PDF
            </a>
          @else
            <div class="text-coal-500 dark:text-coal-400">Belum ada PDF.</div>
          @endif
        </div>
      </div>
    </div>

    <!-- DATA -->
    <div class="mt-6 rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft overflow-x-auto">
      <h2 class="font-medium p-4 sm:p-5 pb-0">Data</h2>
      <div class="p-4 sm:p-5 pt-3">
        <table class="w-full text-sm min-w-[640px]">
          <tbody>
          @foreach($entry->data as $k => $v)
            <tr class="border-t first:border-t-0 dark:border-coal-800/70 hover:bg-ivory-100/60 dark:hover:bg-coal-800/40">
              <th class="text-left p-3 align-top w-56 text-coal-700 dark:text-coal-300">
                {{ ucfirst(str_replace('_',' ',$k)) }}
              </th>
              <td class="p-3">
                {{ is_array($v) ? implode(', ', $v) : $v }}
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- LAMPIRAN -->
    <div class="mt-6 rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft p-4 sm:p-5">
      <h2 class="font-medium mb-2">Lampiran</h2>
      @if($entry->files->count())
        <ul class="list-disc pl-6 space-y-1.5 text-sm">
          @foreach($entry->files as $f)
            <li>
              <a class="text-maroon-700 hover:underline dark:text-maroon-300"
                 href="{{ route('front.entry.download.attachment', $f) }}">
                {{ $f->original_name }}
              </a>
              <span class="text-coal-500 dark:text-coal-400 text-xs">
                ({{ $f->mime }}, {{ number_format($f->size/1024,1) }} KB)
              </span>
            </li>
          @endforeach
        </ul>
      @else
        <div class="text-coal-500 dark:text-coal-400 text-sm">Tidak ada lampiran.</div>
      @endif
    </div>

    {{-- ====== BLOK APPROVAL ====== --}}
    @can('entry-approve', $entry)
      @php
        $canReview  = in_array($entry->status, ['submitted']);
        $canApprove = in_array($entry->status, ['submitted','reviewed']);
        $canReject  = in_array($entry->status, ['submitted','reviewed']);
      @endphp

      <div class="mt-6 rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft p-4 sm:p-5">
        <h2 class="font-medium mb-3">Approval</h2>
        <form action="{{ route('admin.entries.approval', $entry) }}" method="post" class="space-y-3">
          @csrf
          <div>
            <label class="block text-sm mb-1">Catatan (opsional)</label>
            <textarea name="notes" rows="3"
                      class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700
                             focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500"></textarea>
          </div>
          <div class="flex flex-wrap gap-2">
            @if($canReview)
              <button name="action" value="review"
                      class="px-3 py-2 rounded-lg border border-maroon-600 text-maroon-700 hover:bg-maroon-50/60
                             dark:text-maroon-300 dark:hover:bg-maroon-900/20 transition text-sm">
                Mark as Reviewed
              </button>
            @endif
            @if($canApprove)
              <button name="action" value="approve"
                      class="px-3 py-2 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 transition text-sm">
                Approve
              </button>
            @endif
            @if($canReject)
              <button name="action" value="reject"
                      class="px-3 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-500 transition text-sm"
                      onclick="return confirm('Tolak entry ini?')">
                Reject
              </button>
            @endif
          </div>
        </form>

        <div class="mt-3 text-sm">
          <b>Status sekarang:</b>
          <span class="ml-1 px-2 py-1 rounded bg-slate-100 text-slate-800 dark:bg-coal-800 dark:text-ivory-100">
            {{ strtoupper($entry->status) }}
          </span>
        </div>
      </div>
    @endcan

    <!-- RIWAYAT -->
    <div class="mt-6 rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft p-4 sm:p-5">
      <h2 class="font-medium mb-2">Riwayat</h2>
      @if($entry->approvals->count())
        <ul class="text-sm space-y-2">
          @foreach($entry->approvals()->latest()->get() as $h)
            <li class="border-t first:border-t-0 dark:border-coal-800/60 pt-2 first:pt-0">
              <b>{{ strtoupper($h->action) }}</b>
              oleh {{ $h->actor->name }} — {{ $h->created_at->format('d/m/Y H:i') }}
              @if($h->notes)
                <div class="text-coal-600 dark:text-coal-300">Catatan: {{ $h->notes }}</div>
              @endif
            </li>
          @endforeach
        </ul>
      @else
        <div class="text-coal-500 dark:text-coal-400 text-sm">Belum ada histori.</div>
      @endif
    </div>
  </div>
</div>
@endsection
