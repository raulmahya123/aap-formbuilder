@extends('layouts.app')
@section('title', 'Preview — '.($form->title ?? 'Form'))
@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Preview — {{ $form->title ?? 'Form' }}</x-ui.page-title>
<x-ui.page-description>Kelola form, struktur isian, dan tipe dokumen.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>
<p class="text-sm text-gray-600">Tampilan pratinjau. (Sesuaikan sesuai kebutuhan builder.)</p>
@endsection
