@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-serif font-semibold text-maroon-700">Forum Tanya Jawab</h1>
    <a href="{{ route('admin.qa.create') }}"
       class="px-4 py-2 rounded-lg bg-maroon-700 text-white hover:bg-maroon-600">
       + Buat Thread
    </a>
  </div>

  <div class="space-y-3">
    @forelse($threads as $t)
      <a href="{{ route('admin.qa.show',$t) }}"
         class="block p-4 rounded-lg border hover:border-maroon-400 hover:bg-maroon-50 transition">
        <div class="flex items-center justify-between mb-1">
          <h2 class="font-semibold text-lg">{{ $t->subject }}</h2>
          <span class="text-xs px-2 py-0.5 rounded 
                       {{ $t->scope==='public' ? 'bg-emerald-100 text-emerald-700' : 'bg-maroon-100 text-maroon-700' }}">
            {{ strtoupper($t->scope) }}
          </span>
        </div>
        <p class="text-sm text-gray-600">
          Dibuat oleh <strong>{{ $t->creator->name }}</strong> • 
          {{ optional($t->last_message_at)->diffForHumans() ?? 'baru' }}
        </p>
        <p class="text-xs mt-1 text-gray-500">Status: {{ ucfirst($t->status) }}</p>
      </a>
    @empty
      <div class="p-6 text-center text-gray-500 border rounded-lg">
        Belum ada thread tanya jawab.
      </div>
    @endforelse
  </div>

  <div class="mt-6">{{ $threads->links() }}</div>
</div>
@endsection
