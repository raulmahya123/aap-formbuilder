@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
  <div class="flex items-center justify-between mb-5">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Documents</h1>
    <a href="{{ route('admin.documents.create') }}"
       class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Buat Dokumen</a>
  </div>

  <div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-[#1D1C1A] text-white">
        <tr>
          <th class="text-left p-3">Doc.No</th>
          <th class="text-left p-3">Judul</th>
          <th class="text-left p-3">Dept/Type</th>
          <th class="text-left p-3">Status</th>
          <th class="text-left p-3">Owner</th>
          <th class="p-3 w-32"></th>
        </tr>
      </thead>
      <tbody>
      @forelse($docs as $d)
        <tr class="border-b hover:bg-[#7A2C2F]/5">
          <td class="p-3 font-medium">{{ $d->doc_no }}</td>
          <td class="p-3">{{ $d->title }}</td>
          <td class="p-3">{{ $d->dept_code }} / {{ $d->doc_type }}</td>
          <td class="p-3 capitalize">{{ $d->controlled_status }}</td>
          <td class="p-3">{{ $d->owner->name ?? '-' }}</td>
          <td class="p-3 text-right">
            <a class="text-[#7A2C2F]" href="{{ route('admin.documents.show',$d) }}">Lihat</a>
            <span class="mx-2 text-gray-400">|</span>
            <a class="text-[#1D1C1A]" href="{{ route('admin.documents.edit',$d) }}">Edit</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="p-6 text-center text-gray-500">Belum ada dokumen</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $docs->links() }}</div>
</div>
@endsection
