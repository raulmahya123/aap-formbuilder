@extends('layouts.app')
@section('title','Riwayat Entri')
@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Riwayat Entri Saya</x-ui.page-title>
<x-ui.page-description>Lihat isian form, status, dan berkas pengajuan.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>

@if($entries->count())
  <div class="space-y-3">
    @foreach($entries as $e)
      <a href="{{ route('front.forms.entries.show', $e->id) }}" class="block p-3 border rounded-lg hover:bg-gray-50">
        <div class="font-semibold">{{ $e->form->title ?? 'Form' }}</div>
        <div class="text-xs text-gray-500">#{{ $e->id }} • {{ $e->created_at?->format('Y-m-d H:i') }}</div>
      </a>
    @endforeach
  </div>

  <div class="mt-4">

  </div>
@else
  <div class="p-6 border rounded-lg text-center text-gray-600">Belum ada entri.</div>
@endif
@endsection
