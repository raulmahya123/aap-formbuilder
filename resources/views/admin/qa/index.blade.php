@extends('layouts.app')

@section('title', 'Tanya Jawab')

@section('content')
<x-ui.page class="mx-auto max-w-5xl">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
      <x-ui.page-title>Tanya Jawab</x-ui.page-title>
      <x-ui.page-description>Kirim pertanyaan, laporan, dan balasan langsung di halaman ini.</x-ui.page-description>
    </div></div>
  <x-ui.page-actions><x-ui.link icon="plus" variant="primary" href="{{ route('admin.qa.create', $selectedSiteId ? ['site_id' => $selectedSiteId] : []) }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold shadow-none transition">Buat topik baru
    </x-ui.link></x-ui.page-actions>
</x-ui.page-header>

  <x-ui.panel class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-coal-200 bg-white shadow-none">
    <div class="flex items-center gap-2 text-sm font-medium text-coal-700"><x-ui-icon name="chat" class="h-5 w-5 text-maroon-700" /> Chat dan laporan site</div>
    <form method="GET" action="{{ route('admin.qa.index') }}">
      <label for="qa-site" class="sr-only">Pilih site</label>
      <select id="qa-site" name="site_id" onchange="this.form.submit()" class="rounded-lg border-coal-200 bg-ivory-50 px-3 py-2 text-sm focus:border-maroon-500 focus:ring-maroon-500">
        @if(auth()->user()->isSuperAdmin())<option value="">Semua site</option>@endif
        @foreach($sites as $site)<option value="{{ $site->id }}" @selected((int) $selectedSiteId === (int) $site->id)>{{ $site->code }} — {{ $site->name }}</option>@endforeach
      </select>
    </form>
  </x-ui.panel>

  <div class="space-y-5">
    @forelse($threads as $thread)
      @php($visibleMessages = $thread->messages->sortBy('created_at')->values())
      <article class="overflow-hidden rounded-lg border border-coal-200 bg-white shadow-none">
        <div class="p-5 pb-3">
          <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
              <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-maroon-100 font-semibold text-maroon-800">{{ strtoupper(substr($thread->creator->name ?? 'U', 0, 2)) }}</div>
              <div class="min-w-0"><div class="truncate text-sm font-semibold text-coal-900">{{ $thread->creator->name ?? 'Pengguna' }}</div><div class="text-xs text-coal-500">{{ optional($thread->created_at)->diffForHumans() }} · {{ $thread->site?->code ?? 'Semua site' }}</div></div>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $thread->status === 'resolved' ? 'ui-status ui-status--success' : 'ui-status ui-status--warning' }}">{{ $thread->status === 'resolved' ? 'Selesai' : 'Terbuka' }}</span>
          </div>
          <x-ui.section-title class="mt-4 text-lg font-semibold text-coal-900">{{ $thread->subject }}</x-ui.section-title>
        </div>

        <div class="space-y-3 bg-ivory-50/60 px-5 py-4">
          @foreach($visibleMessages as $message)
            @php($mine = $message->user_id === auth()->id())
            <div class="flex items-end gap-2 {{ $mine ? 'justify-end' : 'justify-start' }}">
              @unless($mine)<div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-maroon-100 text-[10px] font-semibold text-maroon-800">{{ strtoupper(substr($message->user->name ?? 'U', 0, 2)) }}</div>@endunless
              <x-ui.panel class="max-w-[86%] rounded-2xl {{ $mine ? 'rounded-br-md bg-maroon-700 text-white' : 'rounded-bl-md border border-coal-200 bg-white text-coal-800' }}">
                <div class="mb-1 text-[11px] {{ $mine ? 'text-white/70' : 'text-coal-500' }}"><span class="font-semibold">{{ $mine ? 'Anda' : $message->user->name }}</span> · {{ $message->created_at->format('H:i') }}</div>
                @if(filled($message->body))<div class="whitespace-pre-line text-sm leading-5">{{ $message->body }}</div>@endif
                @if($message->attachments)
                  <div class="mt-2 grid gap-2">@foreach($message->attachments as $attachmentIndex => $attachment)<x-ui.link variant="secondary" href="{{ route('admin.qa.messages.attachments.download', [$message, $attachmentIndex]) }}" class="flex items-center gap-2 rounded-lg border px-2.5 py-1.5 text-xs {{ $mine ? 'border-white/20 bg-white/10 text-white' : 'border-coal-200 bg-ivory-50 text-coal-700' }}"><x-ui-icon name="file" class="h-3.5 w-3.5" /><span class="truncate">{{ $attachment['name'] ?? 'Lampiran' }}</span></x-ui.link>@endforeach</div>
                @endif
              </x-ui.panel>
            </div>
          @endforeach
        </div>

        @can('reply', $thread)
          @if($thread->status !== 'resolved')
            <form method="POST" action="{{ route('admin.qa.messages.store', $thread) }}" enctype="multipart/form-data" class="border-t border-coal-100 p-4" x-data="{files: ''}">
              @csrf
              <div class="flex items-end gap-2">
                <textarea name="body" rows="1" class="min-h-11 flex-1 resize-none rounded-lg border-coal-200 bg-ivory-50 px-4 py-3 text-sm focus:border-maroon-500 focus:ring-maroon-500" placeholder="Tulis balasan atau kirim laporan..."></textarea>
                <label class="inline-flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-coal-200 text-coal-600 hover:bg-ivory-100" title="Lampirkan file"><x-ui-icon name="paperclip" class="h-5 w-5" /><input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt" class="hidden" @change="files = [...$event.target.files].map(f => f.name).join(', ')"></label>
                <x-ui.button variant="primary" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg" title="Kirim"><x-ui-icon name="send" class="h-5 w-5" /></x-ui.button>
              </div>
              <div x-show="files" x-text="files" class="mt-2 truncate text-xs text-coal-500"></div>
            </form>
          @endif
        @endcan
        <div class="flex items-center justify-between border-t border-coal-100 px-5 py-3 text-xs text-coal-500"><span>{{ $thread->messages_count }} pesan</span><a href="{{ route('admin.qa.show', $thread) }}" class="font-semibold text-maroon-700 hover:text-maroon-900">Buka chat penuh →</a></div>
      </article>
    @empty
      <x-ui.panel class="rounded-lg border border-dashed border-coal-300 bg-white text-center shadow-none"><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-maroon-100 text-maroon-700"><x-ui-icon name="chat" class="h-7 w-7" /></div><x-ui.section-title class="mt-4 font-semibold text-coal-900">Belum ada percakapan di site ini</x-ui.section-title><p class="mt-1 text-sm text-coal-500">Buat topik pertama atau kirim laporan untuk memulai.</p><x-ui.link icon="plus" variant="primary" href="{{ route('admin.qa.create', $selectedSiteId ? ['site_id' => $selectedSiteId] : []) }}" class="mt-5 inline-flex rounded-lg px-4 py-2 text-sm font-semibold">Buat topik baru</x-ui.link></x-ui.panel>
    @endforelse
  </div>
  <div>{{ $threads->withQueryString()->links() }}</div>
</x-ui.page>
@endsection
