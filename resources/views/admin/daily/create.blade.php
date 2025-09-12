@extends('layouts.app')

@section('title','Input Harian')

@section('content')
<h1 class="text-2xl font-bold mb-4">Input Harian Per Site</h1>

<form action="{{ route('admin.daily.store') }}" method="post" class="space-y-4">
  @csrf
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">Site</label>
      <select name="site_id" class="w-full border rounded px-3 py-2" required>
        @foreach($sites as $s) <option value="{{ $s->id }}">{{ $s->code }} — {{ $s->name }}</option> @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Tanggal</label>
      <input type="date" name="date" value="{{ $date }}" class="w-full border rounded px-3 py-2" required>
    </div>
  </div>

  @foreach($groups as $g)
    <div class="mt-6">
      <div class="px-3 py-2 font-semibold bg-gray-100 rounded-t">{{ $g->name }}</div>
      <div class="bg-white border rounded-b">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2 text-left w-10">#</th>
              <th class="px-3 py-2 text-left">Indicator</th>
              <th class="px-3 py-2 text-left w-40">Value</th>
              <th class="px-3 py-2 text-left">Unit/Note</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @foreach($g->indicators as $i)
              <tr>
                <td class="px-3 py-2">{{ $i->order_index }}</td>
                <td class="px-3 py-2">
                  <div class="font-medium">{{ $i->name }}</div>
                  <div class="text-xs text-gray-500 font-mono">CODE: {{ $i->code }}</div>
                </td>
                <td class="px-3 py-2">
                  @if($i->is_derived)
                    <input disabled placeholder="Derived" class="w-full border rounded px-3 py-2 bg-gray-50 text-gray-500">
                  @else
                    <input name="values[{{ $i->id }}]" type="number" step="0.0001"
                           class="w-full border rounded px-3 py-2" placeholder="0">
                  @endif
                </td>
                <td class="px-3 py-2">
                  <div class="text-xs text-gray-500">Unit: {{ $i->unit ?? '-' }}</div>
                  <input name="notes[{{ $i->id }}]" class="mt-1 w-full border rounded px-3 py-2" placeholder="Catatan (opsional)">
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endforeach

  <button class="mt-4 px-5 py-2 bg-indigo-600 text-white rounded">Simpan</button>
</form>
@endsection
