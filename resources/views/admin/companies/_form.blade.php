@php
  // Flag editing & safety $company
  $editing = isset($company) && $company?->exists;
  if (!isset($company)) {
    $company = new \App\Models\Company;
  }
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  {{-- KIRI --}}
  <div class="space-y-3">
    <div>
      <label class="block text-sm font-medium">Kode <span class="text-red-500">*</span></label>
      <input type="text" name="code" value="{{ old('code', $company->code ?? '') }}" class="w-full rounded-xl border px-3 py-2" required>
      @error('code')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Nama <span class="text-red-500">*</span></label>
      <input type="text" name="name" value="{{ old('name', $company->name ?? '') }}" class="w-full rounded-xl border px-3 py-2" required>
      @error('name')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Nama Badan Hukum</label>
      <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name ?? '') }}" class="w-full rounded-xl border px-3 py-2">
      @error('legal_name')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $company->slug ?? '') }}" class="w-full rounded-xl border px-3 py-2">
        @error('slug')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
      <div>
        <label class="block text-sm font-medium">Industri</label>
        <input type="text" name="industry" value="{{ old('industry', $company->industry ?? '') }}" class="w-full rounded-xl border px-3 py-2">
        @error('industry')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium">NPWP</label>
        <input type="text" name="npwp" value="{{ old('npwp', $company->npwp ?? '') }}" class="w-full rounded-xl border px-3 py-2">
        @error('npwp')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
      <div>
        <label class="block text-sm font-medium">NIB</label>
        <input type="text" name="nib" value="{{ old('nib', $company->nib ?? '') }}" class="w-full rounded-xl border px-3 py-2">
        @error('nib')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium">Email</label>
        <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}" class="w-full rounded-xl border px-3 py-2">
        @error('email')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
      <div>
        <label class="block text-sm font-medium">Telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $company->phone ?? '') }}" class="w-full rounded-xl border px-3 py-2">
        @error('phone')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium">Website</label>
      <input type="text" name="website" value="{{ old('website', $company->website ?? '') }}" class="w-full rounded-xl border px-3 py-2">
      @error('website')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
  </div>

  {{-- KANAN --}}
  <div class="space-y-3">
    <div>
      <label class="block text-sm font-medium">Alamat Kantor Pusat</label>
      <textarea name="hq_address" rows="3" class="w-full rounded-xl border px-3 py-2">{{ old('hq_address', $company->hq_address ?? '') }}</textarea>
      @error('hq_address')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium">Kota</label>
        <input type="text" name="city" value="{{ old('city', $company->city ?? '') }}" class="w-full rounded-xl border px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium">Provinsi</label>
        <input type="text" name="province" value="{{ old('province', $company->province ?? '') }}" class="w-full rounded-xl border px-3 py-2">
      </div>
    </div>

    <div class="grid grid-cols-3 gap-3">
      <div>
        <label class="block text-sm font-medium">Kode Pos</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $company->postal_code ?? '') }}" class="w-full rounded-xl border px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium">Negara (ISO-2)</label>
        <input type="text" name="country" value="{{ old('country', $company->country ?? 'ID') }}" class="w-full rounded-xl border px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium">Mata Uang (ISO-3)</label>
        <input type="text" name="currency" value="{{ old('currency', $company->currency ?? 'IDR') }}" class="w-full rounded-xl border px-3 py-2">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium">Zona Waktu</label>
        <input type="text" name="timezone" value="{{ old('timezone', $company->timezone ?? 'Asia/Jakarta') }}" class="w-full rounded-xl border px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium">Status</label>
        @php($cur = old('status', $company->status ?? 'active'))
        <select name="status" class="w-full rounded-xl border px-3 py-2">
          @foreach(['active'=>'Aktif','inactive'=>'Nonaktif','archived'=>'Arsip'] as $val => $label)
            <option value="{{ $val }}" @selected($cur === $val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium">Logo</label>
      @if($editing && $company->logo_url)
        <div class="mb-2 flex items-center gap-3">
          <img src="{{ $company->logo_url }}" class="h-12 w-12 rounded object-cover">
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="remove_logo" value="1" class="rounded border"> Hapus logo
          </label>
        </div>
      @endif
      <input type="file" name="logo" class="w-full rounded-xl border px-3 py-2 bg-white" accept="image/png,image/jpeg,image/webp">
      @error('logo')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
  </div>
</div>
