@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
  <h1 class="text-xl font-semibold mb-4">Kelola Status User</h1>

  <table class="w-full border">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-3 py-2 text-left">Nama</th>
        <th class="px-3 py-2 text-left">Email</th>
        <th class="px-3 py-2 text-left">Status</th>
        <th class="px-3 py-2 text-left">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($users as $user)
      <tr class="border-t">
        <td class="px-3 py-2">{{ $user->name }}</td>
        <td class="px-3 py-2">{{ $user->email }}</td>
        <td class="px-3 py-2">
          @if ($user->is_active)
            <span class="text-green-600 font-medium">Aktif</span>
          @else
            <span class="text-red-600 font-medium">Nonaktif</span>
          @endif
        </td>
        <td class="px-3 py-2">
          <form action="{{ route('admin.users.active.toggle', $user) }}" method="POST" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="px-3 py-1 rounded bg-blue-600 text-white text-sm">
              {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="mt-4">
    {{ $users->links() }}
  </div>
</div>
@endsection
