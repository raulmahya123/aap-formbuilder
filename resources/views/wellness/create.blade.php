<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Wellness Check-in · {{ config('app.name', 'AAP Form Builder') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f6f1eb] text-slate-800">
  <main class="mx-auto max-w-3xl px-4 py-8 sm:py-14">
    <div class="mb-6 rounded-3xl bg-[#6f4b3e] p-6 text-white shadow-lg sm:p-9">
      <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#f1d6b8]">AAP Form Builder</p>
      <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">Wellness Check-in</h1>
      <p class="mt-3 max-w-xl text-sm leading-6 text-white/80">Luangkan satu menit untuk membagikan kondisi Anda hari ini. Data ini digunakan sebagai bahan pemantauan kesejahteraan tim.</p>
    </div>

    @if(session('success'))
      <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('wellness.store') }}" class="space-y-5 rounded-3xl bg-white p-6 shadow-sm sm:p-9">
      @csrf
      <div class="grid gap-5 sm:grid-cols-2">
        <div>
          <label class="mb-2 block text-sm font-medium">Nama <span class="text-red-500">*</span></label>
          <input name="name" value="{{ old('name') }}" required class="w-full rounded-xl border-slate-200 px-4 py-3" placeholder="Nama lengkap">
          @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium">Email <span class="text-slate-400">(opsional)</span></label>
          <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border-slate-200 px-4 py-3" placeholder="nama@perusahaan.com">
        </div>
        <div class="sm:col-span-2">
          <label class="mb-2 block text-sm font-medium">Departemen / Unit</label>
          <input name="department" value="{{ old('department') }}" class="w-full rounded-xl border-slate-200 px-4 py-3" placeholder="Contoh: Operasional">
        </div>
      </div>

      <div class="border-t border-slate-100 pt-5">
        <label class="mb-3 block text-sm font-medium">Bagaimana perasaan Anda hari ini? <span class="text-red-500">*</span></label>
        <div class="grid gap-3 sm:grid-cols-3">
          @foreach(['baik' => '😊 Baik', 'cukup' => '😐 Cukup', 'kurang' => '😔 Kurang'] as $value => $label)
            <label class="cursor-pointer rounded-xl border border-slate-200 p-3 text-center has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="mood" value="{{ $value }}" class="sr-only" @checked(old('mood') === $value)>{{ $label }}</label>
          @endforeach
        </div>
      </div>

      <div class="grid gap-5 sm:grid-cols-2">
        <div>
          <label class="mb-2 block text-sm font-medium">Tingkat stres (1–5) <span class="text-red-500">*</span></label>
          <select name="stress_level" required class="w-full rounded-xl border-slate-200 px-4 py-3">
            <option value="">Pilih tingkat</option>
            @for($i = 1; $i <= 5; $i++)<option value="{{ $i }}" @selected((string)old('stress_level') === (string)$i)>{{ $i }} — {{ [1=>'Sangat ringan',2=>'Ringan',3=>'Sedang',4=>'Tinggi',5=>'Sangat tinggi'][$i] }}</option>@endfor
          </select>
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium">Jam tidur terakhir</label>
          <input type="number" min="0" max="24" step="0.5" name="sleep_hours" value="{{ old('sleep_hours') }}" class="w-full rounded-xl border-slate-200 px-4 py-3" placeholder="Contoh: 7.5">
        </div>
        <div class="sm:col-span-2">
          <label class="mb-2 block text-sm font-medium">Aktivitas fisik hari ini</label>
          <select name="physical_activity" class="w-full rounded-xl border-slate-200 px-4 py-3"><option value="">Pilih aktivitas</option>@foreach(['tidak_ada'=>'Tidak ada','ringan'=>'Ringan','sedang'=>'Sedang','aktif'=>'Aktif'] as $value=>$label)<option value="{{ $value }}" @selected(old('physical_activity') === $value)>{{ $label }}</option>@endforeach</select>
        </div>
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium">Catatan atau dukungan yang dibutuhkan <span class="text-slate-400">(opsional)</span></label>
        <textarea name="notes" rows="4" class="w-full rounded-xl border-slate-200 px-4 py-3" placeholder="Tulis secara singkat jika ada yang ingin disampaikan...">{{ old('notes') }}</textarea>
      </div>

      <button class="w-full rounded-xl bg-[#6f4b3e] px-5 py-3.5 font-medium text-white transition hover:bg-[#57392f]">Kirim Check-in Wellness</button>
      <p class="text-center text-xs text-slate-400">Mohon isi dengan jujur. Respons akan terlihat oleh admin terkait.</p>
    </form>
  </main>
</body>
</html>
