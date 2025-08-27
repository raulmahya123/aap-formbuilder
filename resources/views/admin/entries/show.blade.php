@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded-xl">
  <h1 class="text-xl font-semibold mb-4">Entry #{{ $entry->id }}</h1>

  <div class="grid md:grid-cols-2 gap-6">
    <div>
      <h2 class="font-medium mb-2">Info</h2>
      <div class="text-sm">
        <div><b>Form:</b> {{ $entry->form->title }}</div>
        <div><b>User:</b> {{ $entry->user->name }} ({{ $entry->user->email }})</div>
        <div><b>Tanggal:</b> {{ $entry->created_at->format('d/m/Y H:i') }}</div>
        <div class="mt-2">
          <b>Status:</b>
          <span class="px-2 py-1 rounded text-xs
            @if($entry->status==='approved') bg-emerald-100 text-emerald-800
            @elseif($entry->status==='rejected') bg-rose-100 text-rose-800
            @elseif($entry->status==='reviewed') bg-amber-100 text-amber-800
            @else bg-slate-100 text-slate-800 @endif">
            {{ strtoupper($entry->status) }}
          </span>
        </div>
      </div>
    </div>
    <div>
      <h2 class="font-medium mb-2">Aksi</h2>
      @if($entry->pdf_output_path)
        <a class="underline" href="{{ route('admin.entries.download_pdf', $entry) }}">Unduh PDF</a>
      @else
        <div class="text-slate-500 text-sm">Belum ada PDF.</div>
      @endif
    </div>
  </div>

  <h2 class="font-medium mt-6 mb-2">Data</h2>
  <div class="border rounded">
    <table class="w-full text-sm">
      @foreach($entry->data as $k=>$v)
        <tr class="border-b">
          <th class="text-left p-3 w-48">{{ ucfirst(str_replace('_',' ',$k)) }}</th>
          <td class="p-3">{{ is_array($v) ? implode(', ', $v) : $v }}</td>
        </tr>
      @endforeach
    </table>
  </div>

  <h2 class="font-medium mt-6 mb-2">Lampiran</h2>
  @if($entry->files->count())
    <ul class="list-disc pl-6">
      @foreach($entry->files as $f)
        <li>
          <a class="underline" href="{{ route('front.entry.download.attachment', $f) }}">
            {{ $f->original_name }}
          </a>
          <span class="text-slate-500 text-xs">({{ $f->mime }}, {{ number_format($f->size/1024,1) }} KB)</span>
        </li>
      @endforeach
    </ul>
  @else
    <div class="text-slate-500 text-sm">Tidak ada lampiran.</div>
  @endif

  {{-- ====== BLOK APPROVAL ====== --}}
  @can('entry-approve', $entry)
    @php
      $canReview  = in_array($entry->status, ['submitted']);
      $canApprove = in_array($entry->status, ['submitted','reviewed']);
      $canReject  = in_array($entry->status, ['submitted','reviewed']);
    @endphp

    <div class="mt-6 p-4 border rounded-xl bg-white">
      <h2 class="font-medium mb-2">Approval</h2>
      <form action="{{ route('admin.entries.approval', $entry) }}" method="post" class="space-y-3">
        @csrf
        <div>
          <label class="text-sm block mb-1">Catatan (opsional)</label>
          <textarea name="notes" rows="3" class="border rounded w-full"></textarea>
        </div>
        <div class="flex flex-wrap gap-2">
          @if($canReview)
            <button name="action" value="review" class="px-3 py-2 rounded border">Mark as Reviewed</button>
          @endif
          @if($canApprove)
            <button name="action" value="approve" class="px-3 py-2 rounded bg-emerald-600 text-white">Approve</button>
          @endif
          @if($canReject)
            <button name="action" value="reject" class="px-3 py-2 rounded bg-rose-600 text-white"
                    onclick="return confirm('Tolak entry ini?')">Reject</button>
          @endif
        </div>
      </form>
      <div class="mt-3 text-sm">
        <b>Status sekarang:</b>
        <span class="px-2 py-1 rounded bg-slate-100">{{ strtoupper($entry->status) }}</span>
      </div>
    </div>
  @endcan

  {{-- ====== RIWAYAT ====== --}}
  <div class="mt-6 p-4 border rounded-xl bg-white">
    <h2 class="font-medium mb-2">Riwayat</h2>
    @if($entry->approvals->count())
      <ul class="text-sm space-y-2">
        @foreach($entry->approvals()->latest()->get() as $h)
          <li>
            <b>{{ strtoupper($h->action) }}</b>
            oleh {{ $h->actor->name }} — {{ $h->created_at->format('d/m/Y H:i') }}
            @if($h->notes)
              <div class="text-slate-600">Catatan: {{ $h->notes }}</div>
            @endif
          </li>
        @endforeach
      </ul>
    @else
      <div class="text-slate-500 text-sm">Belum ada histori.</div>
    @endif
  </div>
</div>
@endsection
