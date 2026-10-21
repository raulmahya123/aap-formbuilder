<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">
  <title>Wellness Check-in · {{ config('app.name', 'AAP Form Builder') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-[#111111]">
  <main class="mx-auto max-w-3xl px-4 py-8 sm:py-14">
    <div class="mb-6 rounded-lg bg-[#603813] p-6 text-white shadow-soft sm:p-9">
      <img src="{{ asset('assets/images/logomandala.png') }}" alt="Mandala" class="mb-5 h-10 w-auto brightness-0 invert">
      <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/80">AAP Form Builder</p>
      <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">Wellness Check-in</h1>
      <p class="mt-3 max-w-xl text-sm leading-6 text-white/80">Silakan isi pemeriksaan kesiapan sebelum bekerja. Form ini tidak membutuhkan login.</p>
    </div>

    @if(session('success'))<div class="toast toast-success mb-5">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="toast toast-error mb-5">Mohon periksa kembali isian form.</div>@endif

    <form method="POST" action="{{ route('wellness.store') }}" class="space-y-6 rounded-lg border border-[#C5C5C5] bg-white p-6 shadow-soft sm:p-9">
      @csrf
      <section>
        <h2 class="mb-4 text-lg font-semibold text-[#603813]">Data Karyawan</h2>
        <div class="grid gap-5 sm:grid-cols-2">
          @foreach([['name','Nama Lengkap','Nama lengkap'],['nik','NIK (Nomor Induk Karyawan)','Nomor induk karyawan'],['position','Jabatan','Jabatan'],['site','Site','Nama site']] as [$field,$label,$placeholder])
          <div><label class="mb-2 block text-sm font-medium">{{ $label }} <span class="text-red-500">*</span></label><input name="{{ $field }}" value="{{ old($field) }}" required class="w-full rounded-lg border-[#C5C5C5] px-4 py-3" placeholder="{{ $placeholder }}">@error($field)<p class="form-error">{{ $message }}</p>@enderror</div>
          @endforeach
        </div>
      </section>

      <section class="border-t border-slate-100 pt-6">
        <h2 class="mb-4 text-lg font-semibold text-[#603813]">Kondisi Sebelum Shift</h2>
        <div class="space-y-5">
          <div><label class="mb-3 block text-sm font-medium">Shift <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-2">@foreach(['pagi'=>'Shift Pagi','malam'=>'Shift Malam'] as $value=>$label)<label class="cursor-pointer rounded-xl border border-slate-200 p-3 has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="shift" value="{{ $value }}" class="mr-2" @checked(old('shift') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><label class="mb-3 block text-sm font-medium">Berapa lama Anda tidur sebelum shift ini? <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-3">@foreach(['lebih_6_jam'=>'Lebih dari 6 jam','6_jam'=>'6 jam','kurang_6_jam'=>'Kurang dari 6 jam'] as $value=>$label)<label class="cursor-pointer rounded-xl border border-slate-200 p-3 text-center text-sm has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="sleep_before_shift" value="{{ $value }}" class="sr-only" @checked(old('sleep_before_shift') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><label class="mb-3 block text-sm font-medium">Apakah Anda sedang mengalami keluhan kesehatan saat ini? <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-3">@foreach(['tidak_ada'=>'Tidak ada keluhan','ringan'=>'Keluhan ringan (pusing, batuk, nyeri ringan)','berat'=>'Keluhan berat (demam tinggi, nyeri hebat, dll)'] as $value=>$label)<label class="cursor-pointer rounded-xl border border-slate-200 p-3 text-sm has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="health_complaint" value="{{ $value }}" class="mr-2" @checked(old('health_complaint') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><label class="mb-3 block text-sm font-medium">Apakah sedang mengonsumsi obat dengan efek samping kantuk / menurunkan konsentrasi? <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-2">@foreach(['ya'=>'Ya','tidak'=>'Tidak'] as $value=>$label)<label class="cursor-pointer rounded-xl border border-slate-200 p-3 has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="drowsy_medication" value="{{ $value }}" class="mr-2" @checked(old('drowsy_medication') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><label class="mb-3 block text-sm font-medium">Apakah Anda siap untuk bekerja? <span class="text-red-500">*</span></label><div class="grid gap-3 sm:grid-cols-3">@foreach(['ya'=>'Ya, siap bekerja','tidak'=>'Tidak siap bekerja','off_day'=>'Off day'] as $value=>$label)<label class="cursor-pointer rounded-xl border border-slate-200 p-3 has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="work_readiness" value="{{ $value }}" class="mr-2" @checked(old('work_readiness') === $value) required>{{ $label }}</label>@endforeach</div></div>
        </div>
      </section>
      <button class="btn-primary w-full">Kirim Form Wellness</button>
      <p class="text-center text-xs text-slate-400">Data digunakan untuk pemantauan kesiapan kerja oleh admin terkait.</p>
    </form>
  </main>
</body>
</html>
