@extends('layouts.app')
@section('title','Riwayat Entri')
@section('content')
<x-ui.page>
  <x-ui.page-header>
    <div class="ui-page-header__copy">
      <x-ui.page-title>Riwayat Entri Saya</x-ui.page-title>
      <x-ui.page-description>Lihat isian form, status, dan berkas pengajuan.</x-ui.page-description>
    </div>
  </x-ui.page-header>

  @if($entries->count())
    <section class="ui-list-stack" aria-label="Riwayat entri">
      @foreach($entries as $e)
        <a href="{{ route('front.forms.entries.show', $e->id) }}" class="ui-list-card">
          <span class="ui-list-card__title">{{ $e->form->title ?? 'Form' }}</span>
          <span class="ui-list-card__meta">#{{ $e->id }} · {{ $e->created_at?->format('d M Y H:i') }}</span>
        </a>
      @endforeach
    </section>
    <x-ui.table-pagination :paginator="$entries" />
  @else
    <x-ui.empty-state title="Belum ada entri" message="Form yang Anda kirim akan tampil di halaman ini." />
  @endif
</x-ui.page>
@endsection
