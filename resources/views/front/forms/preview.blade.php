@extends('layouts.app')
@section('title', 'Preview — '.($form->title ?? 'Form'))
@section('content')
<h1 class="text-2xl font-bold mb-4">Preview — {{ $form->title ?? 'Form' }}</h1>
<p class="text-sm text-gray-600">Tampilan pratinjau. (Sesuaikan sesuai kebutuhan builder.)</p>
@endsection
