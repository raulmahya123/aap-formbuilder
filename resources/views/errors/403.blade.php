@extends('errors.layout')
@section('code', '403')
@section('content')
<x-ui.error-state kind="403" ><x-ui.link variant="secondary" href="/">Ke halaman utama</x-ui.link></x-ui.error-state>
@endsection
