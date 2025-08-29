@extends('layouts.app')

@section('title','Edit Template')

@section('content')
<div class="max-w-3xl mx-auto p-6 space-y-6">
  <h1 class="text-xl font-semibold">Edit Template #{{ $template->id }}</h1>

  <form method="POST" action="{{ route('admin.document_templates.update',$template) }}" class="space-y-4">
    @csrf @method('PUT')

    <div>
      <label class="block text-sm mb-1">Nama</label>
      <input name="name" class="w-full border rounded-xl px-3 py-2" value="{{ old('name',$template->name) }}" required>
      @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="block text-sm mb-1">Header Config</label>
      <textarea name="header_config" rows="6" class="w-full border rounded-xl px-3 py-2"
        placeholder='Contoh JSON: {"logo":{"url":""},"title":{"align":"left"}}'>{{ 
          old(
            'header_config', 
            is_array($template->header_config) 
              ? json_encode($template->header_config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) 
              : ($template->header_config ?? '')
          ) 
      }}</textarea>
      @error('header_config')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="block text-sm mb-1">Footer Config</label>
      <textarea name="footer_config" rows="6" class="w-full border rounded-xl px-3 py-2"
        placeholder='Contoh JSON: {"text":"..."}'>{{ 
          old(
            'footer_config', 
            is_array($template->footer_config) 
              ? json_encode($template->footer_config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) 
              : ($template->footer_config ?? '')
          ) 
      }}</textarea>
      @error('footer_config')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-2">
      <a href="{{ route('admin.document_templates.index') }}" class="px-4 py-2 rounded-xl border">Batal</a>
      <button class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white">Simpan</button>
    </div>
  </form>
</div>
@endsection
