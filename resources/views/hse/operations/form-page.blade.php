@extends('layouts.app')
@section('title', $title)
@section('content')
<x-ui.page>
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <x-ui.page-title>{{ $title }}</x-ui.page-title>
            <x-ui.page-description>{{ $description }}</x-ui.page-description>
        </div>
        <x-ui.page-actions><x-ui.back-link :href="$backHref">Kembali ke daftar</x-ui.back-link></x-ui.page-actions>
    </x-ui.page-header>
    @if($errors->any())
        <div role="alert" class="ui-card border-red-600 mb-5"><p class="font-medium">Periksa kembali isian formulir.</p><ul class="mt-2 list-disc pl-5 text-sm text-red-700">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <section class="ui-card ui-standalone-form">@include('hse.operations.forms.'.$formKey)</section>
</x-ui.page>
@endsection
