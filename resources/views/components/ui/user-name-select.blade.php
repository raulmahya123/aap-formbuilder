@props(['users', 'name', 'value' => null])
<x-ui.form-select :name="$name" {{ $attributes }}>
    <option value="">Pilih pengguna</option>
    @php($legacyValue = filled($value) && !$users->contains('name', $value))
    @if($legacyValue)
        <option value="{{ $value }}" selected>Data lama — {{ $value }}</option>
    @endif
    @foreach($users->unique('name') as $person)
        <option value="{{ $person->name }}" @selected($value === $person->name)>{{ $person->name }}</option>
    @endforeach
</x-ui.form-select>
@if($legacyValue)
    <p class="mt-1 text-xs text-amber-700">Nilai PIC lama ini bukan nama akun pengguna. Pilih nama pengguna dari daftar untuk menggantinya.</p>
@endif
