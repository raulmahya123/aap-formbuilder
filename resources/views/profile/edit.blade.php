@extends('layouts.app')

@section('title', 'Akun Saya')

@push('styles')
@endpush

@section('content')
<div
  x-data="profileManager({
    isSuperAdmin: {{ $user->isSuperAdmin() ? 'true' : 'false' }},
    userId: {{ $user->id }},
    currentTab: 'personal',
    avatarUrl: '{{ $user->avatar_path ? asset('storage/'.$user->avatar_path) : '' }}',
    routes: {
      updateProfile: '{{ route('profile.update') }}',
      uploadAvatar: '{{ route('profile.avatar.upload') }}',
      deleteAvatar: '{{ route('profile.avatar.delete') }}',
      updateEmployment: '{{ route('profile.employment.update') }}',
      updatePassword: '{{ route('password.update') }}',
      deleteAccount: '{{ route('profile.destroy') }}',
    }
  })"
  class="ui-page min-w-0 space-y-6"
>

  <!-- ========================================== -->
  <!-- 2. PAGE HEADER & BREADCRUMB               -->
  <!-- ========================================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#E2E8F0] pb-5">
    <div>
      <nav class="flex items-center gap-1.5 text-xs text-[#475569] mb-1.5" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-[#0057FF] transition">Dashboard</a>
        <span>/</span>
        <span>Pengaturan</span>
        <span>/</span>
        <span class="text-[#111827] font-semibold">Akun Saya</span>
      </nav>
      <h1 class="text-2xl sm:text-[28px] font-bold text-[#111827] tracking-tight">Akun Saya</h1>
      <p class="text-xs sm:text-sm text-[#475569] mt-0.5">Kelola informasi pribadi, pekerjaan, serta keamanan akun Anda.</p>
    </div>
  </div>

  <!-- Status Alert Messages -->
  @if (session('status') === 'profile-updated')
    <div class="p-3.5 bg-[#DCFCE7] border border-[#BBF7D0] text-[#166534] rounded-xl text-xs font-semibold flex items-center gap-2">
      <x-ui-icon name="check" class="w-4 h-4 text-[#166534]" />
      <span>Profil berhasil diperbarui.</span>
    </div>
  @endif

  @if (session('status') === 'password-updated')
    <div class="p-3.5 bg-[#DCFCE7] border border-[#BBF7D0] text-[#166534] rounded-xl text-xs font-semibold flex items-center gap-2">
      <x-ui-icon name="check" class="w-4 h-4 text-[#166534]" />
      <span>Password berhasil diubah.</span>
    </div>
  @endif

  <!-- ========================================== -->
  <!-- 2. PROFILE SUMMARY CARD                   -->
  <!-- ========================================== -->
  <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-5">
    <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-4 sm:gap-5">
      <!-- Circular Avatar with Camera Trigger -->
      <div class="relative group shrink-0">
        <template x-if="avatarUrl">
          <img :src="avatarUrl" alt="{{ $user->name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-2 border-[#E2E8F0] shadow-xs">
        </template>
        <template x-if="!avatarUrl">
          <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-[#EFF6FF] border-2 border-[#BFDBFE] text-[#0057FF] font-bold text-2xl flex items-center justify-center">
            {{ strtoupper(substr($user->name, 0, 2)) }}
          </div>
        </template>

        <!-- Camera overlay button -->
        <button
          type="button"
          @click="openAvatarModal = true"
          class="absolute bottom-0 right-0 p-2 bg-[#0057FF] hover:bg-[#0046CC] text-white rounded-full shadow-md transition transform group-hover:scale-105"
          title="Ubah Foto Profil"
          aria-label="Ubah Foto Profil"
        >
          <x-ui-icon name="camera" class="w-3.5 h-3.5 text-white" />
        </button>
      </div>

      <!-- Info Details -->
      <div class="space-y-1">
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
          <h2 class="text-lg sm:text-xl font-bold text-[#111827]">{{ $user->name }}</h2>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $user->is_active ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#FEE2E2] text-[#991B1B]' }}">
            {{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}
          </span>
        </div>
        <p class="text-xs text-[#475569]">{{ $user->email }}</p>
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 pt-1 text-xs text-[#334155]">
          <span class="font-medium text-[#111827]">{{ $user->position ?: 'Pegawai' }}</span>
          <span class="text-[#CBD5E1]">•</span>
          <span class="capitalize px-2 py-0.5 rounded bg-[#F1F5F9] font-semibold text-[11px] text-[#475569]">{{ str_replace('_', ' ', $user->role ?? 'user') }}</span>
          <span class="text-[#CBD5E1]">•</span>
          <span>Site: <strong class="text-[#111827]">{{ $user->isSuperAdmin() ? 'Semua Site (HO)' : ($user->sites->pluck('code')->join(', ') ?: 'Belum Ada Site') }}</strong></span>
        </div>
      </div>
    </div>

    <!-- Quick action buttons -->
    <div class="flex sm:flex-col items-center justify-center gap-2 shrink-0">
      <button
        type="button"
        @click="openAvatarModal = true"
        class="w-full sm:w-auto px-4 py-2 border border-[#E2E8F0] hover:bg-[#F8FAFC] text-xs font-semibold text-[#111827] rounded-xl transition inline-flex items-center justify-center gap-2"
      >
        <x-ui-icon name="camera" class="w-4 h-4 text-[#475569]" />
        <span>Ganti Foto</span>
      </button>
      @if($user->avatar_path)
        <x-ui.button variant="danger" icon="trash-2"
          type="button"
          @click="deleteAvatar()"
        >Hapus Foto</x-ui.button>
      @endif
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 3. TAB NAVIGATION                         -->
  <!-- ========================================== -->
  <div class="border-b border-[#E2E8F0] overflow-x-auto chat-scroll">
    <div class="flex items-center gap-8 min-w-max">
      <button
        type="button"
        @click="switchTab('personal')"
        class="pb-3 text-xs sm:text-sm font-semibold transition relative"
        :class="currentTab === 'personal' ? 'text-[#0057FF] border-b-2 border-[#0057FF]' : 'text-[#475569] hover:text-[#111827] border-b-2 border-transparent'"
      >
        Informasi Pribadi
      </button>

      <button
        type="button"
        @click="switchTab('employment')"
        class="pb-3 text-xs sm:text-sm font-semibold transition relative"
        :class="currentTab === 'employment' ? 'text-[#0057FF] border-b-2 border-[#0057FF]' : 'text-[#475569] hover:text-[#111827] border-b-2 border-transparent'"
      >
        Informasi Pekerjaan
      </button>

      <button
        type="button"
        @click="switchTab('security')"
        class="pb-3 text-xs sm:text-sm font-semibold transition relative"
        :class="currentTab === 'security' ? 'text-[#0057FF] border-b-2 border-[#0057FF]' : 'text-[#475569] hover:text-[#111827] border-b-2 border-transparent'"
      >
        Keamanan Akun
      </button>

      <button
        type="button"
        @click="switchTab('activity')"
        class="pb-3 text-xs sm:text-sm font-semibold transition relative"
        :class="currentTab === 'activity' ? 'text-[#0057FF] border-b-2 border-[#0057FF]' : 'text-[#475569] hover:text-[#111827] border-b-2 border-transparent'"
      >
        Aktivitas Akun
      </button>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 4. TAB 1: INFORMASI PRIBADI               -->
  <!-- ========================================== -->
  <div x-show="currentTab === 'personal'" x-transition class="space-y-6">
    <form method="POST" action="{{ route('profile.update') }}" class="sm:p-8">
      @csrf
      @method('patch')

      <div class="mb-6 pb-4 border-b border-[#E2E8F0]">
        <h3 class="text-base sm:text-lg font-bold text-[#111827]">Identitas & Data Pribadi</h3>
        <p class="text-xs text-[#475569] mt-0.5">Lengkapi identitas diri Anda untuk keperluan operasional perusahaan.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
        <!-- Nama Lengkap -->
        <div>
          <label class="block">Nama Lengkap <span class="text-[#DC2626]">*</span></label>
          <input
            type="text"
            name="name"
            value="{{ old('name', $user->name) }}"
            required
            class="w-full ui-input"
          />
          @error('name') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Nama Panggilan -->
        <div>
          <label class="block">Nama Panggilan</label>
          <input
            type="text"
            name="nickname"
            value="{{ old('nickname', $user->nickname) }}"
            placeholder="Panggilan akrab"
            class="w-full ui-input"
          />
          @error('nickname') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- NIK -->
        <div>
          <label class="block">NIK (Nomor Induk Kependudukan - 16 Digit)</label>
          <input
            type="text"
            name="nik"
            maxlength="16"
            value="{{ old('nik', $user->nik) }}"
            placeholder="16 digit angka sesuai KTP"
            class="w-full ui-input"
          />
          @error('nik') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Jenis Kelamin -->
        <div>
          <label class="block">Jenis Kelamin</label>
          <select
            name="gender"
            class="w-full ui-input"
          >
            <option value="">Pilih Jenis Kelamin</option>
            <option value="L" @selected(old('gender', $user->gender) === 'L')>Laki-laki</option>
            <option value="P" @selected(old('gender', $user->gender) === 'P')>Perempuan</option>
          </select>
          @error('gender') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Tempat Lahir -->
        <div>
          <label class="block">Tempat Lahir</label>
          <input
            type="text"
            name="birth_place"
            value="{{ old('birth_place', $user->birth_place) }}"
            placeholder="Kota / Kabupaten kelahiran"
            class="w-full ui-input"
          />
          @error('birth_place') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Tanggal Lahir -->
        <div>
          <label class="block">Tanggal Lahir</label>
          <input
            type="date"
            name="birth_date"
            value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}"
            class="w-full ui-input"
          />
          @error('birth_date') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Email -->
        <div>
          <label class="block">Email Perusahaan / Login <span class="text-[#DC2626]">*</span></label>
          <input
            type="email"
            name="email"
            value="{{ old('email', $user->email) }}"
            required
            class="w-full ui-input"
          />
          @error('email') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Nomor Telepon -->
        <div>
          <label class="block">Nomor Telepon Seluler</label>
          <input
            type="tel"
            name="phone"
            value="{{ old('phone', $user->phone) }}"
            placeholder="Contoh: 081234567890"
            class="w-full ui-input"
          />
          @error('phone') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- WhatsApp -->
        <div>
          <label class="block">Nomor WhatsApp Aktif</label>
          <input
            type="tel"
            name="whatsapp"
            value="{{ old('whatsapp', $user->whatsapp) }}"
            placeholder="Contoh: 081234567890"
            class="w-full ui-input"
          />
          @error('whatsapp') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Alamat Lengkap -->
        <div class="md:col-span-2">
          <label class="block">Alamat Domisili Lengkap</label>
          <textarea
            name="address"
            rows="2"
            placeholder="Alamat jalan, nomor rumah, RT/RW..."
            class="w-full ui-input"
          >{{ old('address', $user->address) }}</textarea>
          @error('address') <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Provinsi & Kota -->
        <div>
          <label class="block">Provinsi</label>
          <input
            type="text"
            name="province"
            value="{{ old('province', $user->province) }}"
            placeholder="Contoh: Kalimantan Timur, DKI Jakarta..."
            class="w-full ui-input"
          />
        </div>

        <div>
          <label class="block">Kota / Kabupaten</label>
          <input
            type="text"
            name="city"
            value="{{ old('city', $user->city) }}"
            placeholder="Contoh: Balikpapan, Samarinda, Jakarta Selatan..."
            class="w-full ui-input"
          />
        </div>

        <!-- Kontak Darurat -->
        <div class="md:col-span-2 pt-4 border-t border-[#E2E8F0]">
          <h4 class="font-bold text-[#111827] mb-3">Kontak Darurat (Emergency Contact)</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block">Nama Kontak Darurat</label>
              <input
                type="text"
                name="emergency_contact_name"
                value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}"
                placeholder="Nama keluarga terdekat / pasangan"
                class="w-full ui-input"
              />
            </div>
            <div>
              <label class="block">Nomor Telepon Darurat</label>
              <input
                type="tel"
                name="emergency_contact_phone"
                value="{{ old('emergency_contact_phone', $user->emergency_contact_phone) }}"
                placeholder="Nomor yang dapat dihubungi saat darurat"
                class="w-full ui-input"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-8 pt-5 border-t border-[#E2E8F0] flex items-center justify-end gap-3">
        <a href="{{ route('profile.edit') }}" class="px-5 py-2.5 text-xs font-semibold text-[#475569] hover:bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl transition">
          Batal
        </a>
        <button
          type="submit"
          class="px-6 py-2.5 text-xs font-semibold text-white bg-[#0057FF] hover:bg-[#0046CC] rounded-xl transition shadow-xs inline-flex items-center gap-2"
        >
          <x-ui-icon name="save" class="w-4 h-4 text-white" />
          <span>Simpan Perubahan</span>
        </button>
      </div>
    </form>
  </div>

  <!-- ========================================== -->
  <!-- 5. TAB 2: INFORMASI PEKERJAAN             -->
  <!-- ========================================== -->
  <div x-show="currentTab === 'employment'" x-transition class="space-y-6">
    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 sm:p-8 shadow-xs">
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#E2E8F0]">
        <div>
          <h3 class="text-base sm:text-lg font-bold text-[#111827]">Informasi Pekerjaan & Organisasi</h3>
          <p class="text-xs text-[#475569] mt-0.5">Penugasan, site penempatan, struktur divisi, dan wewenang akun.</p>
        </div>
        @if($user->isSuperAdmin())
          <button
            type="button"
            @click="openEditEmploymentModal()"
            class="px-4 py-2 text-xs font-semibold text-[#0057FF] bg-[#EFF6FF] border border-[#BFDBFE] hover:bg-[#DBEAFE] rounded-xl transition inline-flex items-center gap-1.5"
          >
            <x-ui-icon name="edit" class="w-3.5 h-3.5 text-[#0057FF]" />
            <span>Ubah Data Pekerjaan (Super Admin)</span>
          </button>
        @endif
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-xs">
        <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
          <span class="text-[11px] font-semibold text-[#94A3B8] uppercase tracking-wider">Employee ID / NIK</span>
          <p class="text-sm font-bold text-[#111827] mt-1">{{ $user->nik ?: '-' }}</p>
        </div>

        <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
          <span class="text-[11px] font-semibold text-[#94A3B8] uppercase tracking-wider">Jabatan / Position</span>
          <p class="text-sm font-bold text-[#111827] mt-1">{{ $user->position ?: 'Staff Operasional' }}</p>
        </div>

        <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
          <span class="text-[11px] font-semibold text-[#94A3B8] uppercase tracking-wider">Role Kewenangan</span>
          <p class="text-sm font-bold text-[#0057FF] mt-1 capitalize">{{ str_replace('_', ' ', $user->role ?? 'User') }}</p>
        </div>

        <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
          <span class="text-[11px] font-semibold text-[#94A3B8] uppercase tracking-wider">Departemen</span>
          <p class="text-sm font-bold text-[#111827] mt-1">{{ $userDepartment ? $userDepartment->name : 'Operasional Umum' }}</p>
        </div>

        <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
          <span class="text-[11px] font-semibold text-[#94A3B8] uppercase tracking-wider">Divisi</span>
          <p class="text-sm font-bold text-[#111827] mt-1">{{ $user->division ?: 'Divisi Lapangan' }}</p>
        </div>

        <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
          <span class="text-[11px] font-semibold text-[#94A3B8] uppercase tracking-wider">Status Karyawan</span>
          <p class="text-sm font-bold text-[#111827] mt-1">{{ $user->employee_status ?: 'PKWT' }}</p>
        </div>

        <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] sm:col-span-2 lg:col-span-3">
          <span class="text-[11px] font-semibold text-[#94A3B8] uppercase tracking-wider">Assigned Sites (Hak Akses Site)</span>
          <div class="mt-2 flex flex-wrap gap-2">
            @if($user->isSuperAdmin())
              <span class="px-3 py-1 rounded-lg bg-[#DBEAFE] text-[#1E40AF] font-bold text-xs">
                Akses Penuh Seluruh Site (Super Administrator)
              </span>
            @else
              @forelse($user->sites as $site)
                <span class="px-3 py-1 rounded-lg bg-white border border-[#CBD5E1] text-[#111827] font-semibold text-xs">
                  {{ $site->code }} — {{ $site->name }}
                </span>
              @empty
                <span class="text-[#94A3B8] italic">Belum ada site yang ditugaskan kepada akun ini.</span>
              @endforelse
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 7. TAB 3: KEAMANAN AKUN                   -->
  <!-- ========================================== -->
  <div x-show="currentTab === 'security'" x-transition class="space-y-6">
    <!-- Form Update Password -->
    <form method="POST" action="{{ route('password.update', [], false) }}" class="sm:p-8">
      @csrf
      @method('put')

      <div class="mb-6 pb-4 border-b border-[#E2E8F0]">
        <h3 class="text-base sm:text-lg font-bold text-[#111827]">Ubah Kata Sandi</h3>
        <p class="text-xs text-[#475569] mt-0.5">Pastikan akun Anda menggunakan kombinasi kata sandi yang aman dan tidak mudah ditebak.</p>
      </div>

      <div class="max-w-xl space-y-4 text-xs" x-data="{ showPass1: false, showPass2: false, showPass3: false }">
        <!-- Current Password -->
        <div>
          <label class="block">Kata Sandi Saat Ini <span class="text-[#DC2626]">*</span></label>
          <div class="relative">
            <input
              :type="showPass1 ? 'text' : 'password'"
              name="current_password"
              required
              autocomplete="current-password"
              placeholder="Masukkan kata sandi saat ini"
              class="w-full ui-input"
            />
            <button type="button" @click="showPass1 = !showPass1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#94A3B8] hover:text-[#475569]">
              <x-ui-icon name="eye" class="w-4 h-4" x-show="!showPass1" />
              <x-ui-icon name="eye-off" class="w-4 h-4" x-show="showPass1" />
            </button>
          </div>
          @if ($errors->updatePassword->has('current_password'))
            <p class="text-[11px] text-[#DC2626] mt-1">{{ $errors->updatePassword->first('current_password') }}</p>
          @endif
        </div>

        <!-- New Password -->
        <div>
          <label class="block">Kata Sandi Baru <span class="text-[#DC2626]">*</span></label>
          <div class="relative">
            <input
              :type="showPass2 ? 'text' : 'password'"
              name="password"
              required
              autocomplete="new-password"
              placeholder="Minimal 8 karakter"
              class="w-full ui-input"
            />
            <button type="button" @click="showPass2 = !showPass2" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#94A3B8] hover:text-[#475569]">
              <x-ui-icon name="eye" class="w-4 h-4" x-show="!showPass2" />
              <x-ui-icon name="eye-off" class="w-4 h-4" x-show="showPass2" />
            </button>
          </div>
          @if ($errors->updatePassword->has('password'))
            <p class="text-[11px] text-[#DC2626] mt-1">{{ $errors->updatePassword->first('password') }}</p>
          @endif
        </div>

        <!-- Confirm Password -->
        <div>
          <label class="block">Konfirmasi Kata Sandi Baru <span class="text-[#DC2626]">*</span></label>
          <div class="relative">
            <input
              :type="showPass3 ? 'text' : 'password'"
              name="password_confirmation"
              required
              autocomplete="new-password"
              placeholder="Ulangi kata sandi baru"
              class="w-full ui-input"
            />
            <button type="button" @click="showPass3 = !showPass3" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#94A3B8] hover:text-[#475569]">
              <x-ui-icon name="eye" class="w-4 h-4" x-show="!showPass3" />
              <x-ui-icon name="eye-off" class="w-4 h-4" x-show="showPass3" />
            </button>
          </div>
        </div>

        <div class="pt-4">
          <button
            type="submit"
            class="px-6 py-2.5 text-xs font-semibold text-white bg-[#0057FF] hover:bg-[#0046CC] rounded-xl transition shadow-xs inline-flex items-center gap-2"
          >
            <x-ui-icon name="lock" class="w-4 h-4 text-white" />
            <span>Perbarui Kata Sandi</span>
          </button>
        </div>
      </div>
    </form>

    <!-- Danger Zone (Delete Account) -->
    <div class="bg-white border border-[#FEE2E2] rounded-2xl p-6 sm:p-8 shadow-xs">
      <div class="mb-4">
        <h4 class="text-base font-bold text-[#DC2626]">Danger Zone — Hapus Akun</h4>
        <p class="text-xs text-[#475569] mt-0.5">
          Setelah akun dihapus, seluruh data, sesi login, dan hak akses yang terkait dengan akun ini akan dinonaktifkan secara permanen.
        </p>
      </div>

      <button
        type="button"
        @click="openDeleteModal = true"
        class="px-5 py-2.5 text-xs font-semibold text-white bg-[#DC2626] hover:bg-[#B91C1C] rounded-xl transition shadow-xs inline-flex items-center gap-2"
      >
        <x-ui-icon name="trash" class="w-4 h-4 text-white" />
        <span>Hapus Akun Ini</span>
      </button>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 8. TAB 4: AKTIVITAS AKUN                  -->
  <!-- ========================================== -->
  <div x-show="currentTab === 'activity'" x-transition class="space-y-6">
    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 sm:p-8 shadow-xs">
      <div class="mb-6 pb-4 border-b border-[#E2E8F0]">
        <h3 class="text-base sm:text-lg font-bold text-[#111827]">Riwayat Sesi & Aktivitas Akun</h3>
        <p class="text-xs text-[#475569] mt-0.5">Pantau sesi aktif dan catatan keamanan login perangkat Anda.</p>
      </div>

      <div class="overflow-x-auto chat-scroll border border-[#E2E8F0] rounded-xl">
        <table class="w-full text-left text-xs text-[#111827] divide-y divide-[#E2E8F0]">
          <thead class="bg-[#F8FAFC] text-[11px] font-semibold text-[#475569] uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Waktu</th>
              <th class="px-4 py-3">Aktivitas</th>
              <th class="px-4 py-3">Perangkat / Browser</th>
              <th class="px-4 py-3">IP Address</th>
              <th class="px-4 py-3">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F1F5F9] bg-white">
            <tr>
              <td class="px-4 py-3 text-[#475569]">Hari Ini, {{ now()->format('H:i') }}</td>
              <td class="px-4 py-3 font-semibold text-[#111827]">Sesi Aktif Sekarang</td>
              <td class="px-4 py-3 text-[#475569]">{{ request()->userAgent() ? Str::limit(request()->userAgent(), 45) : 'Web Browser' }}</td>
              <td class="px-4 py-3 text-[#475569]">{{ request()->ip() }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#DCFCE7] text-[#166534]">
                  Online
                </span>
              </td>
            </tr>
            <tr>
              <td class="px-4 py-3 text-[#475569]">{{ $user->updated_at?->format('d M Y H:i') ?: '-' }}</td>
              <td class="px-4 py-3 font-semibold text-[#111827]">Pembaruan Data Akun</td>
              <td class="px-4 py-3 text-[#475569]">Mandala Web Client</td>
              <td class="px-4 py-3 text-[#475569]">{{ request()->ip() }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EFF6FF] text-[#1E40AF]">
                  Success
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 9. MODALS                                 -->
  <!-- ========================================== -->

  <!-- Modal: Upload / Ganti Foto Profil -->
  <div
    x-show="openAvatarModal"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
  >
    <div
      @click.outside="openAvatarModal = false"
      class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-[#E2E8F0] overflow-hidden"
    >
      <div class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
        <h4 class="font-bold text-sm text-[#111827]">Ubah Foto Profil</h4>
        <button type="button" @click="openAvatarModal = false" class="text-[#94A3B8] hover:text-[#475569]">
          <x-ui-icon name="x" class="w-5 h-5" />
        </button>
      </div>

      <div class="p-6 text-center space-y-4 text-xs">
        <!-- Preview Box -->
        <div class="w-28 h-28 mx-auto rounded-full overflow-hidden border-2 border-dashed border-[#CBD5E1] flex items-center justify-center bg-[#F8FAFC] relative">
          <template x-if="avatarPreview">
            <img :src="avatarPreview" class="w-full h-full object-cover" alt="Preview" />
          </template>
          <template x-if="!avatarPreview">
            <div class="text-[#94A3B8] flex flex-col items-center">
              <x-ui-icon name="camera" class="w-8 h-8 mb-1" />
              <span class="text-[10px]">Pilih file</span>
            </div>
          </template>
        </div>

        <div>
          <label class="inline-block">
            <span>Pilih Berkas Foto</span>
            <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="previewFile($event)" />
          </label>
          <p class="text-[10px] text-[#94A3B8] mt-2">Format: JPG, PNG, atau WebP (Maksimal 3MB)</p>
        </div>
      </div>

      <div class="px-5 py-3.5 bg-[#F8FAFC] border-t border-[#E2E8F0] flex justify-end gap-2 text-xs">
        <button type="button" @click="openAvatarModal = false; avatarPreview = null" class="px-4 py-2 font-semibold text-[#475569] hover:bg-[#E2E8F0] rounded-xl transition">
          Batal
        </button>
        <button
          type="button"
          @click="submitAvatarUpload()"
          :disabled="!avatarFile || uploadingAvatar"
          class="px-5 py-2 font-semibold text-white bg-[#0057FF] hover:bg-[#0046CC] disabled:opacity-50 rounded-xl transition"
        >
          <span x-show="!uploadingAvatar">Simpan Foto</span>
          <span x-show="uploadingAvatar">Menyimpan...</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Modal: Super Admin Ubah Data Pekerjaan -->
  <div
    x-show="openEmploymentModal"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
  >
    <div
      @click.outside="openEmploymentModal = false"
      class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-[#E2E8F0] overflow-hidden"
    >
      <div class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
        <div>
          <h4 class="font-bold text-sm text-[#111827]">Ubah Informasi Pekerjaan</h4>
          <p class="text-xs text-[#475569]">Khusus otorisasi Super Administrator</p>
        </div>
        <button type="button" @click="openEmploymentModal = false" class="text-[#94A3B8] hover:text-[#475569]">
          <x-ui-icon name="x" class="w-5 h-5" />
        </button>
      </div>

      <div class="p-6 space-y-4 text-xs overflow-y-auto max-h-[70vh] chat-scroll">
        <div>
          <label class="block">Jabatan / Position</label>
          <input type="text" x-model="employmentForm.position" class="w-full ui-input" />
        </div>

        <div>
          <label class="block">Divisi</label>
          <input type="text" x-model="employmentForm.division" class="w-full ui-input" />
        </div>

        <div>
          <label class="block">Departemen</label>
          <select x-model="employmentForm.department_id" class="w-full ui-input">
            <option value="">Pilih Departemen</option>
            @foreach($allDepartments as $dept)
              <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block">Role Akun</label>
          <select x-model="employmentForm.role" class="w-full ui-input">
            <option value="user">User</option>
            <option value="admin_ho">Admin HO</option>
            <option value="admin_site">Admin Site</option>
            <option value="super_admin">Super Admin</option>
          </select>
        </div>

        <div>
          <label class="block">Assigned Sites</label>
          <div class="grid grid-cols-2 gap-2 p-3 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl max-h-36 overflow-y-auto">
            @foreach($allSites as $s)
              <label class="flex items-center">
                <input type="checkbox" value="{{ $s->id }}" x-model="employmentForm.site_ids" class="w-full ui-input" />
                <span class="truncate">{{ $s->code }} — {{ $s->name }}</span>
              </label>
            @endforeach
          </div>
        </div>
      </div>

      <div class="px-5 py-3.5 bg-[#F8FAFC] border-t border-[#E2E8F0] flex justify-end gap-2 text-xs">
        <button type="button" @click="openEmploymentModal = false" class="px-4 py-2 font-semibold text-[#475569] hover:bg-[#E2E8F0] rounded-xl transition">
          Batal
        </button>
        <x-ui.button variant="primary" icon="save" type="button" @click="submitEmploymentUpdate()">Simpan Perubahan</x-ui.button>
      </div>
    </div>
  </div>

  <!-- Modal: Konfirmasi Hapus Akun -->
  <div
    x-show="openDeleteModal"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
  >
    <div
      @click.outside="openDeleteModal = false"
      class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-[#FEE2E2] overflow-hidden"
    >
      <form method="POST" action="{{ route('profile.destroy') }}">
        @csrf
        @method('delete')

        <div class="p-6 text-center space-y-3">
          <div class="w-12 h-12 mx-auto rounded-full bg-[#FEF2F2] text-[#DC2626] flex items-center justify-center">
            <x-ui-icon name="warning" class="w-6 h-6 text-[#DC2626]" />
          </div>
          <h4 class="font-bold text-base text-[#111827]">Hapus Akun Anda?</h4>
          <p class="text-xs text-[#475569]">
            Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi akun Anda untuk mengonfirmasi penghapusan.
          </p>

          <div class="pt-2 text-left">
            <label class="block">Kata Sandi Konfirmasi</label>
            <input
              type="password"
              name="password"
              required
              placeholder="Masukkan kata sandi akun Anda"
              class="w-full ui-input"
            />
          </div>
        </div>

        <div class="px-5 py-3.5 bg-[#F8FAFC] border-t border-[#E2E8F0] flex justify-end gap-2 text-xs">
          <button type="button" @click="openDeleteModal = false" class="px-4 py-2 font-semibold text-[#475569] hover:bg-[#E2E8F0] rounded-xl transition">
            Batal
          </button>
          <button type="submit" class="px-5 py-2 font-semibold text-white bg-[#DC2626] hover:bg-[#B91C1C] rounded-xl transition">
            Ya, Hapus Permanen
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
function profileManager(config) {
  return {
    isSuperAdmin: config.isSuperAdmin,
    userId: config.userId,
    currentTab: 'personal',
    avatarUrl: config.avatarUrl,
    routes: config.routes,

    // Modals
    openAvatarModal: false,
    openEmploymentModal: false,
    openDeleteModal: false,

    // Avatar state
    avatarFile: null,
    avatarPreview: null,
    uploadingAvatar: false,

    // Employment Form state
    employmentForm: {
      position: '{{ addslashes($user->position ?? '') }}',
      division: '{{ addslashes($user->division ?? '') }}',
      department_id: '{{ $user->department_id ?? ($userDepartment ? $userDepartment->id : '') }}',
      role: '{{ $user->role ?? 'user' }}',
      site_ids: [{{ $user->sites->pluck('id')->join(',') }}],
    },

    switchTab(tab) {
      this.currentTab = tab;
    },

    previewFile(e) {
      const file = e.target.files[0];
      if (file) {
        this.avatarFile = file;
        this.avatarPreview = URL.createObjectURL(file);
      }
    },

    async submitAvatarUpload() {
      if (!this.avatarFile) return;
      this.uploadingAvatar = true;
      const formData = new FormData();
      formData.append('avatar', this.avatarFile);

      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(this.routes.uploadAvatar, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken },
          body: formData,
        });
        const data = await res.json();
        if (data.success) {
          this.avatarUrl = data.avatar_url;
          this.openAvatarModal = false;
          this.avatarPreview = null;
          this.avatarFile = null;
          alert('Foto profil berhasil diperbarui.');
          window.location.reload();
        } else {
          alert(data.message || 'Gagal mengunggah foto.');
        }
      } catch (e) {
        alert('Terjadi kesalahan saat mengunggah foto.');
      } finally {
        this.uploadingAvatar = false;
      }
    },

    async deleteAvatar() {
      if (!confirm('Apakah Anda yakin ingin menghapus foto profil dan kembali ke inisial nama?')) return;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(this.routes.deleteAvatar, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken },
        });
        const data = await res.json();
        if (data.success) {
          this.avatarUrl = '';
          alert('Foto profil telah dihapus.');
          window.location.reload();
        }
      } catch (e) {
        alert('Gagal menghapus foto.');
      }
    },

    openEditEmploymentModal() {
      this.openEmploymentModal = true;
    },

    async submitEmploymentUpdate() {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(this.routes.updateEmployment, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            user_id: this.userId,
            position: this.employmentForm.position,
            division: this.employmentForm.division,
            department_id: this.employmentForm.department_id || null,
            role: this.employmentForm.role,
            site_ids: this.employmentForm.site_ids,
          }),
        });
        const data = await res.json();
        if (data.success) {
          alert('Data pekerjaan berhasil diperbarui.');
          window.location.reload();
        } else {
          alert(data.message || 'Gagal memperbarui data.');
        }
      } catch (e) {
        alert('Terjadi kesalahan koneksi.');
      }
    }
  }
}
</script>
@endpush
