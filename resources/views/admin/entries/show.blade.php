@extends('layouts.app')
@section('content')
<x-ui.page>
  <div class="w-full min-w-0 ">
    <!-- HEADER -->
    <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>{{ $entry->form->title }} <span class="text-coal-500 dark:text-coal-400 text-sm">#{{ $entry->id }}</span></x-ui.page-title>
<x-ui.page-description>Tinjau detail entri form dan tindakan yang tersedia.</x-ui.page-description></div>
  <x-ui.page-actions><div class="flex items-center gap-2">
        @if($entry->pdf_output_path)
          <x-ui.link variant="primary" href="{{ route('admin.entries.download_pdf', $entry) }}"
             class="px-3 py-2 rounded-lg text-ivory-50 transition text-sm">
            ⬇️ Unduh PDF
          </x-ui.link>
        @endif
        {{-- Tombol unduh semua (ZIP) --}}
        <x-ui.link variant="secondary" href="{{ route('admin.entries.download_all', $entry) }}"
           class="px-3 py-2 rounded-lg border dark:text-maroon-300 dark:border-maroon-400 dark:hover:bg-maroon-900/20 transition text-sm">
          <x-ui.lucide name="download" /> Unduh Semua (ZIP)
        </x-ui.link>
      </div></x-ui.page-actions>
</x-ui.page-header>
    <!-- INFO & AKSI -->
    <div class="grid md:grid-cols-2 gap-4 sm:gap-5">
      <div class="rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none p-4 sm:p-5">
        <x-ui.section-title class="font-medium mb-3">Info</x-ui.section-title>
        <div class="text-sm space-y-2">
          <div><b>Form:</b> {{ $entry->form->title }}</div>
          <div><b>User:</b> {{ $entry->user->name }} <span class="text-coal-500 dark:text-coal-400">({{ $entry->user->email }})</span></div>
          <div><b>Tanggal:</b> {{ $entry->created_at->format('d/m/Y H:i') }}</div>
          <div class="pt-2">
            <b>Status:</b>
            <span class="px-2 py-1 rounded-lg text-xs font-medium @if($entry->status==='approved') bg-emerald-100 text-emerald-800 @elseif($entry->status==='rejected') bg-rose-100 text-rose-800 @elseif($entry->status==='reviewed') bg-amber-100 text-amber-800 @else bg-slate-100 text-slate-800 @endif">
              {{ strtoupper($entry->status) }}
            </span>
          </div>
        </div>
      </div>
      <div class="rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none p-4 sm:p-5">
        <x-ui.section-title class="font-medium mb-3">Aksi</x-ui.section-title>
        <div class="text-sm flex flex-wrap gap-2">
          @if($entry->pdf_output_path)
            <x-ui.link variant="secondary" href="{{ route('admin.entries.download_pdf', $entry) }}"
               class="inline-flex items-center px-3 py-2 rounded-lg border dark:text-maroon-300 dark:hover:bg-maroon-900/20 transition">
              Lihat / Unduh PDF
            </x-ui.link>
          @else
            <div class="text-coal-500 dark:text-coal-400">Belum ada PDF.</div>
          @endif
        </div>
      </div>
    </div>
    <!-- DATA -->
    <!-- DATA -->
<div class="mt-6 rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none overflow-x-auto">
  <div class="flex items-center justify-between p-4 sm:p-5 pb-0">
    <x-ui.section-title class="font-medium">Data</x-ui.section-title>
    {{-- Tombol Download Data (PDF) --}}
      <x-ui.link variant="primary" icon="file-down" href="{{ route('admin.entries.data_pdf', $entry) }}"
         class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-ivory-50 transition text-sm">
      Unduh Data PDF
    </x-ui.link>
  </div>
  <div class="p-4 sm:p-5 pt-3">
    <x-ui.table class="w-full text-sm min-w-[640px]">
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
    </x-ui.table>
  </div>
</div>
    <!-- LAMPIRAN -->
    <div class="mt-6 rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none p-4 sm:p-5">
      <x-ui.section-title class="font-medium mb-2">Lampiran</x-ui.section-title>
      @if($entry->files->count())
        <ul class="list-disc pl-6 space-y-2 text-sm">
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
      <div class="mt-6 rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none p-4 sm:p-5">
        <x-ui.section-title class="font-medium mb-3">Approval</x-ui.section-title>
        <form action="{{ route('admin.entries.approval', $entry) }}" method="post" class="space-y-3">
          @csrf
          <div>
            <label class="block text-sm mb-1">Catatan (opsional)</label>
            <textarea name="notes" rows="3"
                      class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-coal-950 dark:border-coal-700 focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500"></textarea>
          </div>
          <div class="flex flex-wrap gap-2">
            @if($canReview)
              <x-ui.button variant="secondary" name="action" value="review"
                      class="px-3 py-2 rounded-lg border dark:text-maroon-300 dark:hover:bg-maroon-900/20 transition text-sm">
                Mark as Reviewed
              </x-ui.button>
            @endif
            @if($canApprove)
              <x-ui.button variant="success" name="action" value="approve"
                      class="px-3 py-2 rounded-lg text-ivory-50 transition text-sm">
                Approve
              </x-ui.button>
            @endif
            @if($canReject)
              <x-ui.button variant="danger" name="action" value="reject"
                      class="px-3 py-2 rounded-lg transition text-sm"
                      onclick="return confirm('Tolak entry ini?')">
                Reject
              </x-ui.button>
            @endif
          </div>
        </form>
        <div class="mt-3 text-sm">
          <b>Status sekarang:</b>
          <span class="ml-1 px-2 py-1 rounded-lg bg-slate-100 text-slate-800 dark:bg-coal-800 dark:text-ivory-100">
            {{ strtoupper($entry->status) }}
          </span>
        </div>
      </div>
    @endcan
    <!-- RIWAYAT -->
    <div class="mt-6 rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none p-4 sm:p-5">
      <x-ui.section-title class="font-medium mb-2">Riwayat</x-ui.section-title>
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
</x-ui.page>
@endsection
