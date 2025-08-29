{{-- resources/views/admin/documents/partials/share.blade.php --}}
@can('share', $document)
<div class="mt-8 border-t pt-6">
  <h2 class="font-semibold mb-3">Bagikan Akses</h2>

  <form class="flex flex-wrap gap-3 items-end"
        method="POST"
        action="{{ route('admin.documents.share', $document) }}">
    @csrf
    <div>
      <label class="block text-sm mb-1">Target</label>
      <select name="target_type" class="border rounded px-3 py-2" required>
        <option value="user">User</option>
        <option value="department">Department</option>
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">ID Target</label>
      <input name="target_id" type="number" class="border rounded px-3 py-2" placeholder="User/Dept ID" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Permission</label>
      <select name="perm" class="border rounded px-3 py-2" required>
        <option value="view">view</option>
        <option value="edit">edit</option>
        <option value="delete">delete</option>
        <option value="export">export</option>
        <option value="share">share</option>
      </select>
    </div>
    <button class="px-4 py-2 rounded bg-maroon-700 text-white">Tambah Akses</button>
  </form>

  {{-- daftar ACL --}}
  <div class="mt-5">
    <h3 class="font-medium mb-2">Daftar Akses</h3>
    <table class="min-w-full text-sm border">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 border">Jenis</th>
          <th class="px-3 py-2 border">Target</th>
          <th class="px-3 py-2 border">Perm</th>
          <th class="px-3 py-2 border">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($document->acls as $a)
        <tr>
          <td class="px-3 py-2 border">{{ $a->user_id ? 'User' : 'Department' }}</td>
          <td class="px-3 py-2 border">
            {{ $a->user_id ? ('User#'.$a->user_id) : ('Dept#'.$a->department_id) }}
          </td>
          <td class="px-3 py-2 border">{{ $a->perm }}</td>
          <td class="px-3 py-2 border">
            <form method="POST" action="{{ route('admin.documents.acl.revoke', [$document, $a->id]) }}"
                  onsubmit="return confirm('Hapus akses ini?')">
              @csrf @method('DELETE')
              <button class="px-3 py-1 rounded bg-red-600 text-white">Hapus</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endcan
