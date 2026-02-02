{{-- resources/views/admin/ccm/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="p-6">

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">
      🧯 Critical Control Management
    </h1>

    <a href="{{ route('ccm-reports.create') }}"
       class="px-4 py-2 rounded-lg bg-maroon-700 text-white text-sm">
      + Input CCM
    </a>
  </div>

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-ivory-100">
        <tr>
          <th class="p-3 text-left">Tanggal</th>
          <th class="p-3 text-left">Jobsite</th>
          <th class="p-3 text-left">Pelapor</th>
          <th class="p-3 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($reports as $r)
          <tr class="border-t">
            <td class="p-3">{{ $r->waktu_pelaporan }}</td>
            <td class="p-3">{{ $r->jobsite }}</td>
            <td class="p-3">{{ $r->nama_pelapor }}</td>
            <td class="p-3">
              <a href="{{ route('ccm-reports.show', $r->id) }}"
                 class="text-blue-600 hover:underline">
                Detail
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="p-6 text-center text-coal-500">
              Belum ada data CCM
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $reports->links() }}
  </div>

</div>
@endsection
