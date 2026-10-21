@extends('layouts.app')

@section('title', 'Tanya Jawab')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <p class="text-sm font-medium text-maroon-700">Community site</p>
      <h1 class="mt-1 text-3xl font-semibold text-coal-900">Tanya Jawab</h1>
      <p class="mt-1 text-sm text-coal-500">Diskusi, informasi, dan solusi bersama untuk setiap site.</p>
    </div>
    <a href="{{ route('admin.qa.create', $selectedSiteId ? ['site_id' => $selectedSiteId] : []) }}" class="inline-flex items-center gap-2 rounded-xl bg-maroon-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-maroon-800">
      <x-ui-icon name="plus" class="h-4 w-4" /> Buat postingan
    </a>
  </div>

  <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-coal-200 bg-white p-3 shadow-sm">
    <div class="flex items-center gap-2 text-sm font-medium text-coal-700"><x-ui-icon name="chat" class="h-5 w-5 text-maroon-700" /> Forum diskusi site</div>
    <form method="GET" action="{{ route('admin.qa.index') }}" class="flex items-center gap-2">
      <label for="qa-site" class="sr-only">Pilih site</label>
      <select id="qa-site" name="site_id" onchange="this.form.submit()" class="rounded-xl border-coal-200 bg-ivory-50 px-3 py-2 text-sm focus:border-maroon-500 focus:ring-maroon-500">
        @if(auth()->user()->isSuperAdmin())<option value="">Semua site</option>@endif
        @foreach($sites as $site)
          <option value="{{ $site->id }}" @selected((int) $selectedSiteId === (int) $site->id)>{{ $site->code }} — {{ $site->name }}</option>
        @endforeach
      </select>
    </form>
  </div>

  <div class="space-y-5">
    @forelse($threads as $thread)
      @php($latest = $thread->messages->first())
      <article class="overflow-hidden rounded-2xl border border-coal-200 bg-white shadow-sm transition hover:border-maroon-300 hover:shadow-md">
        <div class="p-5">
          <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
              <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-maroon-100 font-semibold text-maroon-800">{{ strtoupper(substr($thread->creator->name ?? 'U', 0, 2)) }}</div>
              <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-coal-900">{{ $thread->creator->name ?? 'Pengguna' }}</div>
                <div class="flex flex-wrap items-center gap-1 text-xs text-coal-500"><span>{{ optional($thread->created_at)->diffForHumans() }}</span><span>•</span><span>{{ $thread->site?->code ?? 'Semua site' }}</span></div>
              </div>
            </div>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $thread->status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $thread->status === 'resolved' ? 'Selesai' : 'Terbuka' }}</span>
          </div>
          <a href="{{ route('admin.qa.show', $thread) }}" class="mt-4 block">
            <h2 class="text-lg font-semibold text-coal-900 hover:text-maroon-700">{{ $thread->subject }}</h2>
            @if($latest)<p class="mt-2 line-clamp-3 whitespace-pre-line text-sm leading-6 text-coal-600">{{ $latest->body }}</p>@endif
          </a>
        </div>
        <div class="flex items-center justify-between border-t border-coal-100 bg-ivory-50/70 px-5 py-3 text-xs text-coal-500"><span>{{ $thread->messages_count }} komentar</span><a href="{{ route('admin.qa.show', $thread) }}" class="font-semibold text-maroon-700 hover:text-maroon-900">Lihat diskusi →</a></div>
      </article>
    @empty
      <div class="rounded-2xl border border-dashed border-coal-300 bg-white px-6 py-14 text-center shadow-sm">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-maroon-100 text-maroon-700"><x-ui-icon name="chat" class="h-7 w-7" /></div>
        <h2 class="mt-4 font-semibold text-coal-900">Belum ada diskusi di site ini</h2>
        <p class="mt-1 text-sm text-coal-500">Mulai percakapan pertama untuk berbagi informasi atau bertanya.</p>
        <a href="{{ route('admin.qa.create', $selectedSiteId ? ['site_id' => $selectedSiteId] : []) }}" class="mt-5 inline-flex rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white">Buat postingan</a>
      </div>
    @endforelse
  </div>
  <div>{{ $threads->withQueryString()->links() }}</div>
</div>
@endsection
