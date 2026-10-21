@extends('errors.layout')
@section('code', '404')
@section('content')
<x-ui.error-state kind="404" ><x-ui.link variant="secondary" href="/">Ke halaman utama</x-ui.link></x-ui.error-state>
@endsection
