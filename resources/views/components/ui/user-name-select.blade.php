@props(['users', 'name', 'value' => null])
<x-ui.form-select :name="$name" {{ $attributes }}>
    <option value="">Pilih pengguna</option>
    @if($value && !$users->contains('name', $value))
        <option value="{{ $value }}" selected>{{ $value }} (data sebelumnya)</option>
    @endif
    @foreach($users->unique('name') as $person)
        <option value="{{ $person->name }}" @selected($value === $person->name)>{{ $person->name }}</option>
    @endforeach
</x-ui.form-select>
