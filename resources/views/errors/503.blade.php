@extends('errors.layout')
@section('code', '503')
@section('content')
<x-ui.error-state kind="503" :retry-url="url()->current()"><x-ui.link variant="secondary" href="/">Ke halaman utama</x-ui.link></x-ui.error-state>
@endsection
