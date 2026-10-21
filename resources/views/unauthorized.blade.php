@extends('layouts.app')
@section('title', '403 — Akses Ditolak')
@section('content')
<x-ui.page>
  <x-ui.page-header>
    <div class="ui-page-header__copy">
      <x-ui.page-title>Akses Ditolak</x-ui.page-title>
      <x-ui.page-description>Anda tidak memiliki izin untuk mengakses halaman ini.</x-ui.page-description>
    </div>
    <x-ui.page-actions>
      <x-ui.link variant="secondary" href="{{ route('dashboard') }}">Ke Dashboard</x-ui.link>
      <x-ui.link variant="primary" href="{{ url()->previous() }}">Kembali</x-ui.link>
    </x-ui.page-actions>
  </x-ui.page-header>
  <x-ui.error-state kind="403" />
</x-ui.page>
@endsection
