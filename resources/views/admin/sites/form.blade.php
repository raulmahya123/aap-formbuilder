{{-- resources/views/admin/sites/form.blade.php --}}
@extends('layouts.app')
@section('title', $site->exists ? 'Edit Site' : 'Tambah Site')

@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>{{ $site->exists ? 'Edit' : 'Tambah' }} Site</x-ui.page-title>
<x-ui.page-description>Kelola identitas dan status site operasional.</x-ui.page-description></div>
  <x-ui.page-actions>
<x-ui.button form="page-primary-form" variant="primary" >
      Simpan
    </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

@if ($errors->any())
  <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
    <ul class="list-disc ml-5 space-y-1 text-sm">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form id="page-primary-form" method="POST"
      action="{{ $site->exists ? route('admin.sites.update',$site) : route('admin.sites.store') }}"
      class="w-full min-w-0 ui-card">
  @csrf
  @if($site->exists) @method('PUT') @endif

  {{-- Perusahaan --}}
  <div>
    <label class="block">Perusahaan</label>
    <select name="company_id"
            class="w-full ui-input">
      <option value="" {{ old('company_id', $site->company_id) ? '' : 'selected' }}>— (Tidak terkait perusahaan)</option>
      @foreach(($companies ?? []) as $c)
        <option value="{{ $c->id }}"
          @selected((string)old('company_id', $site->company_id) === (string)$c->id)>
          {{ $c->code }} — {{ $c->name }}
        </option>
      @endforeach
    </select>
    <p class="text-xs text-slate-500 mt-1">Opsional. Pilih perusahaan pemilik site.</p>
  </div>

  <div>
    <label class="block">
      Code <span class="text-sm text-gray-500">(mis: HO, BGG, SBS, DBK)</span>
    </label>
    <input name="code" value="{{ old('code',$site->code) }}"
           class="w-full ui-input"
           required>
  </div>

  <div>
    <label class="block">Name</label>
    <input name="name" value="{{ old('name',$site->name) }}"
           class="w-full ui-input"
           required>
  </div>

  <div class="grid gap-4 sm:grid-cols-2">
    <div>
      <label class="block">Latitude</label>
      <input name="latitude" type="number" step="any" min="-90" max="90"
             value="{{ old('latitude',$site->latitude) }}"
             class="w-full ui-input"
             placeholder="-6.2000000">
      <p class="text-xs text-slate-500 mt-1">Opsional. Digunakan untuk peta sebaran site HSE.</p>
    </div>
    <div>
      <label class="block">Longitude</label>
      <input name="longitude" type="number" step="any" min="-180" max="180"
             value="{{ old('longitude',$site->longitude) }}"
             class="w-full ui-input"
             placeholder="106.8166667">
    </div>
  </div>

  <div>
    <label class="block">Description</label>
    <textarea name="description"
              class="w-full ui-input"
              rows="3">{{ old('description',$site->description) }}</textarea>
  </div>

  <div class="flex gap-3">

    <x-ui.link variant="secondary" href="{{ route('admin.sites.index') }}"
       >
      Batal
    </x-ui.link>
  </div>
</form>
@endsection
