@extends('errors.layout')
@section('code', '401')
@section('content')
<x-ui.error-state kind="401" ><x-ui.link variant="secondary" href="/">Ke halaman utama</x-ui.link></x-ui.error-state>
@endsection
