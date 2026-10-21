<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">
  <title>Wellness Check-in · {{ config('app.name', 'AAP Form Builder') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-[#18181B]">
  <main class="ui-public mx-auto max-w-3xl">
    <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Wellness Check-in</x-ui.page-title>
      <x-ui.page-description>Silakan isi pemeriksaan kesiapan sebelum bekerja. Form ini tidak membutuhkan login.</x-ui.page-description></div>
  <x-ui.page-actions>
<x-ui.button form="page-primary-form" variant="primary" class="btn-primary w-full">Kirim Form Wellness</x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>



    <form id="page-primary-form" method="POST" action="{{ route('wellness.store') }}" class="ui-card space-y-5 rounded-lg border border-[#D4D4D8] bg-white p-6 shadow-none sm:p-9">
  @csrf
      <section>
        <x-ui.section-title class="mb-4 text-lg font-semibold text-[#713F12]">Data Karyawan</x-ui.section-title>
        <div class="grid gap-5 sm:grid-cols-2">
          @foreach([['name','Nama Lengkap','Nama lengkap'],['nik','NIK (Nomor Induk Karyawan)','Nomor induk karyawan'],['position','Jabatan','Jabatan'],['site','Site','Nama site']] as [$field,$label,$placeholder])
          <x-ui.form-field :name="$field" :label="$label" required><x-ui.form-input :id="$field" :name="$field" :value="old($field)" required :placeholder="$placeholder" /></x-ui.form-field>
          @endforeach
        </div>
      </section>

      <section class="border-t border-slate-100 pt-6">
        <x-ui.section-title class="mb-4 text-lg font-semibold text-[#713F12]">Kondisi Sebelum Shift</x-ui.section-title>
        <div class="space-y-5">
          <div><label class="mb-3 block text-sm font-medium">Shift <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-2">@foreach(['pagi'=>'Shift Pagi','malam'=>'Shift Malam'] as $value=>$label)<label class="cursor-pointer rounded-lg border border-slate-200 p-3 has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="shift" value="{{ $value }}" class="mr-2" @checked(old('shift') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><label class="mb-3 block text-sm font-medium">Berapa lama Anda tidur sebelum shift ini? <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-3">@foreach(['lebih_6_jam'=>'Lebih dari 6 jam','6_jam'=>'6 jam','kurang_6_jam'=>'Kurang dari 6 jam'] as $value=>$label)<label class="cursor-pointer rounded-lg border border-slate-200 p-3 text-center text-sm has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="sleep_before_shift" value="{{ $value }}" class="sr-only" @checked(old('sleep_before_shift') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><label class="mb-3 block text-sm font-medium">Apakah Anda sedang mengalami keluhan kesehatan saat ini? <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-3">@foreach(['tidak_ada'=>'Tidak ada keluhan','ringan'=>'Keluhan ringan (pusing, batuk, nyeri ringan)','berat'=>'Keluhan berat (demam tinggi, nyeri hebat, dll)'] as $value=>$label)<label class="cursor-pointer rounded-lg border border-slate-200 p-3 text-sm has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="health_complaint" value="{{ $value }}" class="mr-2" @checked(old('health_complaint') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><label class="mb-3 block text-sm font-medium">Apakah sedang mengonsumsi obat dengan efek samping kantuk / menurunkan konsentrasi? <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-2">@foreach(['ya'=>'Ya','tidak'=>'Tidak'] as $value=>$label)<label class="cursor-pointer rounded-lg border border-slate-200 p-3 has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="drowsy_medication" value="{{ $value }}" class="mr-2" @checked(old('drowsy_medication') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><label class="mb-3 block text-sm font-medium">Apakah Anda siap untuk bekerja? <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-3">@foreach(['ya'=>'Ya, siap bekerja','tidak'=>'Tidak siap bekerja','off_day'=>'Off day'] as $value=>$label)<label class="cursor-pointer rounded-lg border border-slate-200 p-3 has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="work_readiness" value="{{ $value }}" class="mr-2" @checked(old('work_readiness') === $value) required>{{ $label }}</label>@endforeach</div></div>
        </div>
      </section>

      <p class="text-center text-xs text-slate-400">Data digunakan untuk pemantauan kesiapan kerja oleh admin terkait.</p>
    </form>
  </main>
  <x-ui.toast-provider />
</body>
</html>
