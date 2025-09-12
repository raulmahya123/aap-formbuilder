@extends('layouts.app')
@section('title','Riwayat Entri')
@section('content')
<h1 class="text-2xl font-bold mb-4">Riwayat Entri Saya</h1>

@if($entries->count())
  <div class="space-y-3">
    @foreach($entries as $e)
      <a href="{{ route('front.forms.entries.show', $e->id) }}" class="block p-3 border rounded hover:bg-gray-50">
        <div class="font-semibold">{{ $e->form->title ?? 'Form' }}</div>
        <div class="text-xs text-gray-500">#{{ $e->id }} • {{ $e->created_at?->format('Y-m-d H:i') }}</div>
      </a>
    @endforeach
  </div>

  <div class="mt-4">
    {{ $entries->withQueryString()->links() }}
  </div>
@else
  <div class="p-6 border rounded text-center text-gray-600">Belum ada entri.</div>
@endif
@endsection
