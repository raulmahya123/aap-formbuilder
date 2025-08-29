@extends('layouts.app')

@section('title','Document Templates')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">
  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Document Templates</h1>
    <a href="{{ route('admin.document_templates.create') }}"
       class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">+ Template</a>
  </div>

  {{-- LIST --}}
  <div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-[#1D1C1A] text-white">
        <tr>
          <th class="p-3 text-left">#</th>
          <th class="p-3 text-left">Nama</th>
          <th class="p-3 text-left">Header</th>
          <th class="p-3 text-left">Footer</th>
          <th class="p-3 text-left">Updated</th>
        </tr>
      </thead>
      <tbody>
        @forelse($templates as $tpl)
          <tr class="border-b hover:bg-[#7A2C2F]/5">
            <td class="p-3">{{ $tpl->id }}</td>
            <td class="p-3 font-medium">{{ $tpl->name }}</td>
            <td class="p-3">
              @if(!empty($tpl->header_config['logo']['url']))
                <img src="{{ $tpl->header_config['logo']['url'] }}" class="h-6 inline">
              @endif
              {{ $tpl->header_config['title']['align'] ?? '-' }}
            </td>
            <td class="p-3 text-xs text-gray-600">{{ $tpl->footer_config['text'] ?? '-' }}</td>
            <td class="p-3 text-xs text-gray-500">{{ $tpl->updated_at->format('d M Y H:i') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="p-6 text-center text-gray-500">Belum ada template</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div>{{ $templates->links() }}</div>
</div>
@endsection
