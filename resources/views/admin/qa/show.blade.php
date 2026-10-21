@extends('layouts.app')
@section('title', $thread->subject)
@section('content')
<x-ui.page class="mx-auto grid max-w-6xl gap-5 lg:grid-cols-[minmax(0,1fr)_18rem]">
  <x-ui.page-header class="lg:col-span-2">
  <div class="ui-page-header__copy"><x-ui.page-title>{{ $thread->subject }}</x-ui.page-title><x-ui.page-description>{{ $thread->site?->code ?? 'Semua site' }} · dibuat oleh {{ $thread->creator->name }}</x-ui.page-description></div>
<x-ui.page-actions><a href="{{ route('admin.qa.index', $thread->site_id ? ['site_id' => $thread->site_id] : []) }}" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-coal-200 text-coal-600 hover:bg-ivory-100" aria-label="Kembali"><x-ui-icon name="arrow-left" class="h-4 w-4" /></a>@can('resolve', $thread)
          @if($thread->status !== 'resolved')
            <form method="POST" action="{{ route('admin.qa.resolve', $thread) }}">@csrf<x-ui.button variant="success" class="shrink-0 rounded-lg border px-3 py-2 text-xs font-semibold">Tandai selesai</x-ui.button></form>
          @endif
        @endcan</x-ui.page-actions>
</x-ui.page-header>
<section class="min-w-0 overflow-hidden rounded-lg border border-coal-200 bg-white shadow-none">
    <div class="max-h-[calc(100vh-20rem)] space-y-5 overflow-y-auto bg-ivory-50/60 p-5">
      @foreach($messages as $message)
        @php($mine = $message->user_id === auth()->id())
        <div class="flex items-end gap-2 {{ $mine ? 'justify-end' : 'justify-start' }}">
          @unless($mine)<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-100 text-xs font-semibold text-maroon-800">{{ strtoupper(substr($message->user->name ?? 'U', 0, 2)) }}</div>@endunless
          <x-ui.panel class="max-w-[85%] rounded-2xl shadow-sm {{ $mine ? 'rounded-br-md bg-maroon-700 text-white' : 'rounded-bl-md border border-coal-200 bg-white text-coal-800' }}">
            <div class="mb-1 flex items-center gap-2 text-xs {{ $mine ? 'text-white/70' : 'text-coal-500' }}"><span class="font-semibold">{{ $mine ? 'Anda' : $message->user->name }}</span><span>{{ $message->created_at->format('d M, H:i') }}</span></div>
            @if(filled($message->body))<div class="whitespace-pre-line text-sm leading-6">{{ $message->body }}</div>@endif
            @if($message->is_official_answer)<span class="mt-2 inline-flex rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold text-emerald-800">Jawaban resmi</span>@endif
            @if($message->attachments)
              <div class="mt-3 grid gap-2">
                @foreach($message->attachments as $index => $attachment)
                  <x-ui.link variant="secondary" href="{{ route('admin.qa.messages.attachments.download', [$message, $index]) }}" class="flex items-center gap-2 rounded-xl border px-3 py-2 text-xs {{ $mine ? 'border-white/20 bg-white/10 text-white' : 'border-coal-200 bg-ivory-50 text-coal-700' }}">
                    <x-ui-icon name="file" class="h-4 w-4 shrink-0" /><span class="min-w-0 flex-1 truncate">{{ $attachment['name'] ?? 'Lampiran' }}</span><span>↓</span>
                  </x-ui.link>
                @endforeach
              </div>
            @endif
          </x-ui.panel>
        </div>
      @endforeach
    </div>
    @can('reply', $thread)
      @if($thread->status !== 'resolved')
        <form method="POST" action="{{ route('admin.qa.messages.store', $thread) }}" enctype="multipart/form-data" class="ui-card border-t border-coal-200 bg-white" x-data="{files: ''}">
          @csrf
          <textarea name="body" rows="3" class="w-full resize-none rounded-lg border-coal-200 bg-ivory-50 focus:border-maroon-500 focus:ring-maroon-500" placeholder="Tulis pesan ke grup..."></textarea>
          <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-coal-200 px-3 py-2 text-xs font-medium text-coal-600 hover:bg-ivory-100"><x-ui-icon name="paperclip" class="h-4 w-4" /> Lampirkan file<input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt" class="sr-only" aria-label="Lampirkan file" @change="files = [...$event.target.files].map(f => f.name).join(', ')"></label>
              <span x-text="files" class="max-w-xs truncate text-xs text-coal-500"></span>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())<label class="inline-flex items-center gap-2 text-xs text-coal-600"><input type="checkbox" name="is_official_answer" value="1" class="rounded-lg border-coal-300 text-maroon-700 focus:ring-maroon-500"> Jawaban resmi</label>@endif
            <x-ui.button variant="primary" class="inline-flex items-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold"><x-ui-icon name="send" class="h-4 w-4" /> Kirim</x-ui.button>
          </div>
        </form>
      @endif
    @endcan
  </section>
  <aside class="h-fit rounded-lg border border-coal-200 bg-white p-4 shadow-none">
    <x-ui.section-title class="font-semibold text-coal-900">Anggota grup</x-ui.section-title>
    <p class="mt-1 text-xs text-coal-500">{{ $thread->participants->count() ?: 'Semua pengguna site' }} anggota</p>
    @if($thread->participants->isNotEmpty())
      <div class="mt-4 space-y-3">
        @foreach($thread->participants as $participant)
          <div class="flex items-center gap-2"><div class="flex h-8 w-8 items-center justify-center rounded-full bg-ivory-100 text-xs font-semibold text-coal-700">{{ strtoupper(substr($participant->name, 0, 2)) }}</div><div class="min-w-0"><div class="truncate text-sm font-medium text-coal-800">{{ $participant->name }}</div><div class="text-xs text-coal-500">{{ strtoupper($participant->role) }}</div></div></div>
        @endforeach
      </div>
    @else
      <div class="mt-4 rounded-lg bg-ivory-50 p-3 text-xs leading-5 text-coal-600">Ini adalah ruang publik untuk seluruh pengguna yang memiliki akses ke site ini.</div>
    @endif
  </aside>
</x-ui.page>
@endsection