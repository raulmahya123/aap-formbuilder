@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-4">
  <div class="flex items-center justify-between mb-5">
    <div>
      <h1 class="text-2xl font-semibold text-maroon-700">{{ $thread->subject }}</h1>
      <p class="text-sm text-gray-600">
        Dibuat oleh <strong>{{ $thread->creator->name }}</strong> • Status: {{ ucfirst($thread->status) }}
      </p>
    </div>

    @can('resolve',$thread)
    <form method="POST" action="{{ route('admin.qa.resolve',$thread) }}">
      @csrf
      <button class="px-3 py-1.5 text-sm rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-700 hover:text-white">
        Tandai Selesai
      </button>
    </form>
    @endcan
  </div>

  @if(session('success'))
    <div class="mb-4 rounded border border-green-600/30 bg-green-50 text-green-700 px-3 py-2 text-sm">
      {{ session('success') }}
    </div>
  @endif

  <div class="space-y-4">
    @foreach($messages as $m)
      <div class="p-4 rounded-lg border {{ $m->is_official_answer ? 'bg-maroon-50 border-maroon-400' : 'bg-white' }}">
        <div class="flex items-center justify-between mb-1">
          <span class="text-sm font-medium text-maroon-700">{{ $m->user->name }}</span>
          <span class="text-xs text-gray-500">{{ $m->created_at->diffForHumans() }}</span>
        </div>
        <div class="text-gray-800 whitespace-pre-line">{{ $m->body }}</div>
        @if($m->is_official_answer)
          <span class="inline-block mt-2 text-xs px-2 py-0.5 rounded bg-maroon-700 text-white">Jawaban Resmi</span>
        @endif
      </div>
    @endforeach
  </div>

  @can('reply',$thread)
  <form method="POST" action="{{ route('admin.qa.messages.store',$thread) }}" class="mt-6 space-y-3">
    @csrf
    <textarea name="body" rows="4" class="w-full rounded-lg border-gray-300 focus:border-maroon-500 focus:ring-maroon-500"
              placeholder="Tulis balasan..." required></textarea>

    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_official_answer" value="1"
               class="rounded border-gray-300 text-maroon-600 focus:ring-maroon-500">
        Tandai sebagai Jawaban Resmi
      </label>
    @endif

    <button class="px-4 py-2 rounded-lg bg-maroon-700 text-white hover:bg-maroon-600">Kirim Balasan</button>
  </form>
  @endcan
</div>
@endsection
