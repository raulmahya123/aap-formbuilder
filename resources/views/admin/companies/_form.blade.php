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
      <label class="block">Kode <span class="text-red-500">*</span></label>
      <input type="text" name="code" value="{{ old('code', $company->code ?? '') }}" class="w-full ui-input" required>
      @error('code')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block">Nama <span class="text-red-500">*</span></label>
      <input type="text" name="name" value="{{ old('name', $company->name ?? '') }}" class="w-full ui-input" required>
      @error('name')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block">Nama Badan Hukum</label>
      <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name ?? '') }}" class="w-full ui-input">
      @error('legal_name')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $company->slug ?? '') }}" class="w-full ui-input">
        @error('slug')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
      <div>
        <label class="block">Industri</label>
        <input type="text" name="industry" value="{{ old('industry', $company->industry ?? '') }}" class="w-full ui-input">
        @error('industry')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block">NPWP</label>
        <input type="text" name="npwp" value="{{ old('npwp', $company->npwp ?? '') }}" class="w-full ui-input">
        @error('npwp')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
      <div>
        <label class="block">NIB</label>
        <input type="text" name="nib" value="{{ old('nib', $company->nib ?? '') }}" class="w-full ui-input">
        @error('nib')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block">Email</label>
        <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}" class="w-full ui-input">
        @error('email')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
      <div>
        <label class="block">Telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $company->phone ?? '') }}" class="w-full ui-input">
        @error('phone')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>
    </div>

    <div>
      <label class="block">Website</label>
      <input type="text" name="website" value="{{ old('website', $company->website ?? '') }}" class="w-full ui-input">
      @error('website')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
  </div>

  {{-- KANAN --}}
  <div class="space-y-3">
    <div>
      <label class="block">Alamat Kantor Pusat</label>
      <textarea name="hq_address" rows="3" class="w-full ui-input">{{ old('hq_address', $company->hq_address ?? '') }}</textarea>
      @error('hq_address')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block">Kota</label>
        <input type="text" name="city" value="{{ old('city', $company->city ?? '') }}" class="w-full ui-input">
      </div>
      <div>
        <label class="block">Provinsi</label>
        <input type="text" name="province" value="{{ old('province', $company->province ?? '') }}" class="w-full ui-input">
      </div>
    </div>

    <div class="grid grid-cols-3 gap-3">
      <div>
        <label class="block">Kode Pos</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $company->postal_code ?? '') }}" class="w-full ui-input">
      </div>
      <div>
        <label class="block">Negara (ISO-2)</label>
        <input type="text" name="country" value="{{ old('country', $company->country ?? 'ID') }}" class="w-full ui-input">
      </div>
      <div>
        <label class="block">Mata Uang (ISO-3)</label>
        <input type="text" name="currency" value="{{ old('currency', $company->currency ?? 'IDR') }}" class="w-full ui-input">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block">Zona Waktu</label>
        <input type="text" name="timezone" value="{{ old('timezone', $company->timezone ?? 'Asia/Jakarta') }}" class="w-full ui-input">
      </div>
      <div>
        <label class="block">Status</label>
        @php($cur = old('status', $company->status ?? 'active'))
        <select name="status" class="w-full ui-input">
          @foreach(['active'=>'Aktif','inactive'=>'Nonaktif','archived'=>'Arsip'] as $val => $label)
            <option value="{{ $val }}" @selected($cur === $val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div>
      <label class="block">Logo</label>
      @if($editing && $company->logo_url)
        <div class="mb-2 flex items-center gap-3">
          <img src="{{ $company->logo_url }}" class="h-12 w-12 rounded-lg object-cover">
          <label class="inline-flex items-center">
            <input type="checkbox" name="remove_logo" value="1" class="w-full ui-input"> Hapus logo
          </label>
        </div>
      @endif
      <input type="file" name="logo" class="w-full" accept="image/png,image/jpeg,image/webp">
      @error('logo')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
  </div>
</div>
