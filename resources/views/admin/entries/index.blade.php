@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Entries</h1>
    <a class="px-3 py-2 rounded bg-slate-800 text-white"
       href="{{ route('admin.entries.export', request()->query()) }}">Export CSV</a>
  </div>

  <form method="get" class="mb-4 grid md:grid-cols-4 gap-3">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari data (contoh: nama)"
           class="border rounded w-full px-3 py-2">
    <input type="text" name="user" value="{{ request('user') }}" placeholder="Nama/Email user"
           class="border rounded w-full px-3 py-2">
    <input type="number" name="form_id" value="{{ request('form_id') }}" placeholder="Form ID"
           class="border rounded w-full px-3 py-2">
    <select name="status" class="border rounded w-full px-3 py-2">
      <option value="">— Semua Status —</option>
      <option value="submitted" @selected(request('status')==='submitted')>Submitted</option>
      <option value="reviewed" @selected(request('status')==='reviewed')>Reviewed</option>
      <option value="approved" @selected(request('status')==='approved')>Approved</option>
      <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
    </select>
    <div class="md:col-span-4">
      <button class="px-4 py-2 bg-emerald-600 text-white rounded">Filter</button>
    </div>
  </form>

  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="text-left p-3">#</th>
          <th class="text-left p-3">Form</th>
          <th class="text-left p-3">Department</th>
          <th class="text-left p-3">User</th>
          <th class="text-left p-3">Waktu</th>
          <th class="text-left p-3">Status</th>
          <th class="text-left p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($entries as $e)
          <tr class="border-t">
            <td class="p-3">{{ $e->id }}</td>
            <td class="p-3">{{ $e->form->title ?? '-' }}</td>
            <td class="p-3">{{ $e->form->department->name ?? '-' }}</td>
            <td class="p-3">
              {{ $e->user->name ?? '-' }}
              <span class="text-slate-500">({{ $e->user->email ?? '' }})</span>
            </td>
            <td class="p-3">{{ $e->created_at->format('d/m/Y H:i') }}</td>
            <td class="p-3">
              <span class="px-2 py-1 rounded text-xs
                @if($e->status==='approved') bg-emerald-100 text-emerald-800
                @elseif($e->status==='rejected') bg-rose-100 text-rose-800
                @elseif($e->status==='reviewed') bg-amber-100 text-amber-800
                @else bg-slate-100 text-slate-800 @endif">
                {{ strtoupper($e->status) }}
              </span>
            </td>
            <td class="p-3 space-x-2">
              <a class="underline" href="{{ route('admin.entries.show',$e) }}">Detail</a>
              @if($e->pdf_output_path)
                <a class="underline" href="{{ route('admin.entries.download_pdf',$e) }}">PDF</a>
              @endif
              <form action="{{ route('admin.entries.destroy',$e) }}" method="post" class="inline" onsubmit="return confirm('Hapus entry?')">
                @csrf @method('DELETE')
                <button class="text-red-600 underline">Hapus</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $entries->links() }}</div>
</div>
@endsection
