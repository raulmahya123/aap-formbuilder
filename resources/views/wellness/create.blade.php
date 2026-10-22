<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">
  <title>Health Assesment Checklist · {{ config('app.name', 'AAP Form Builder') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="health-check-page">
  <main class="health-check">
    <header class="health-check__header">
      <a href="{{ route('wellness.create') }}" aria-label="Health Assesment Checklist" class="health-check__brand">
        <img src="{{ asset('assets/images/logomandala.png') }}" alt="Mandala Management System Andalan" width="144" height="64">
      </a>
      <span class="health-check__eyebrow"><x-ui.lucide name="shield" /> Pemeriksaan sebelum shift</span>
      <h1>Health Assesment<br>Checklist</h1>
      <p>Awali shift dengan mengenali kondisi Anda.<br>Isi sesuai keadaan yang Anda rasakan saat ini.</p>
      <div class="health-check__meta"><span><x-ui.lucide name="clipboard-list" /> Tanpa login</span><span><span class="health-check__required">*</span> Wajib diisi</span></div>
    </header>
    <form id="page-primary-form" method="POST" action="{{ route('wellness.store') }}" class="health-check__form">
  @csrf
      <section class="health-check__section">
        <div class="health-check__section-heading"><span class="health-check__step">01</span><div><h2>Data karyawan</h2><p>Lengkapi identitas dan lokasi kerja Anda.</p></div></div>
        <div class="health-check__fields">
          @foreach([['name','Nama Lengkap','Nama lengkap'],['nik','NIK (Nomor Induk Karyawan)','Nomor induk karyawan'],['position','Jabatan','Jabatan'],['site','Site','Nama site']] as [$field,$label,$placeholder])
          <x-ui.form-field :name="$field" :label="$label" required><x-ui.form-input :id="$field" :name="$field" :value="old($field)" required :placeholder="$placeholder" /></x-ui.form-field>
          @endforeach
        </div>
      </section>

      <section class="health-check__section">
        <div class="health-check__section-heading"><span class="health-check__step">02</span><div><h2>Kondisi sebelum shift</h2><p>Setiap jawaban membantu pemantauan kesiapan kerja.</p></div></div>
        <div class="space-y-5">
          <div><div class="mb-3 block text-sm font-medium">Shift <span class="text-red-500">*</span></div><div class="health-check__choices">@foreach(['pagi'=>'Shift Pagi','malam'=>'Shift Malam'] as $value=>$label)<label class="cursor-pointer rounded-lg border border-slate-200 p-3 has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="shift" value="{{ $value }}" class="mr-2" @checked(old('shift') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><div class="mb-3 block text-sm font-medium">Berapa lama Anda tidur sebelum shift ini? <span class="text-red-500">*</span></div><div class="health-check__choices">@foreach(['lebih_6_jam'=>'Lebih dari 6 jam','6_jam'=>'6 jam','kurang_6_jam'=>'Kurang dari 6 jam'] as $value=>$label)<label class="cursor-pointer rounded-lg border border-slate-200 p-3 text-center text-sm has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="sleep_before_shift" value="{{ $value }}" class="health-check__radio" @checked(old('sleep_before_shift') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div x-data="{ quality: @js(old('sleep_quality', '')) }" class="space-y-4">
            <x-ui.form-field name="sleep_quality" label="Bagaimana kualitas tidur Anda?" required>
              <x-ui.form-select id="sleep_quality" name="sleep_quality" x-model="quality" required :invalid="$errors->has('sleep_quality')">
                <option value="">Pilih kualitas tidur</option>
                @foreach(['baik'=>'Baik','cukup'=>'Cukup','buruk'=>'Buruk'] as $value=>$label)
                  <option value="{{ $value }}" @selected(old('sleep_quality') === $value)>{{ $label }}</option>
                @endforeach
              </x-ui.form-select>
            </x-ui.form-field>
            <div x-show="quality === 'buruk'" x-cloak>
              <x-ui.form-field name="sleep_quality_reason" label="Alasan kualitas tidur buruk" required>
                <x-ui.form-textarea id="sleep_quality_reason" name="sleep_quality_reason" rows="3" maxlength="2000" x-bind:required="quality === 'buruk'" x-bind:disabled="quality !== 'buruk'" :invalid="$errors->has('sleep_quality_reason')" placeholder="Ceritakan penyebab tidur Anda kurang baik.">{{ old('sleep_quality_reason') }}</x-ui.form-textarea>
              </x-ui.form-field>
            </div>
          </div>
          <x-ui.form-field name="heavy_drowsiness" label="Apakah Anda merasa mengantuk berat atau sulit berkonsentrasi?" required>
            <x-ui.form-select id="heavy_drowsiness" name="heavy_drowsiness" required :invalid="$errors->has('heavy_drowsiness')">
              <option value="">Pilih jawaban</option>
              <option value="ya" @selected(old('heavy_drowsiness') === 'ya')>Ya</option>
              <option value="tidak" @selected(old('heavy_drowsiness') === 'tidak')>Tidak</option>
            </x-ui.form-select>
          </x-ui.form-field>
          <div x-data="{ complaint: @js(old('health_complaint', '')) }" class="space-y-4">
            <x-ui.form-field name="health_complaint" label="Apakah Anda sedang mengalami keluhan kesehatan saat ini?" required>
              <div class="health-check__choices">
                @foreach(['tidak'=>'Tidak','ya'=>'Iya'] as $value=>$label)
                  <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm has-[:checked]:border-[#713F12]">
                    <input type="radio" name="health_complaint" value="{{ $value }}" x-model="complaint" @checked(old('health_complaint') === $value) required> {{ $label }}
                  </label>
                @endforeach
              </div>
            </x-ui.form-field>
            <div x-show="complaint === 'ya'" x-cloak>
              <x-ui.form-field name="health_complaint_details" label="Keterangan keluhan / gejala" required helper="Jelaskan keluhan, gejala, dan penyebabnya jika diketahui.">
                <x-ui.form-textarea id="health_complaint_details" name="health_complaint_details" rows="3" maxlength="2000" x-bind:required="complaint === 'ya'" x-bind:disabled="complaint !== 'ya'" :invalid="$errors->has('health_complaint_details')" placeholder="Contoh: sakit kepala sejak pagi dan badan terasa lemas.">{{ old('health_complaint_details') }}</x-ui.form-textarea>
              </x-ui.form-field>
            </div>
          </div>
          <div><div class="mb-3 block text-sm font-medium">Apakah sedang mengonsumsi obat dengan efek samping kantuk / menurunkan konsentrasi? <span class="text-red-500">*</span></div><div class="health-check__choices">@foreach(['ya'=>'Ya','tidak'=>'Tidak'] as $value=>$label)<label class="cursor-pointer rounded-lg border border-slate-200 p-3 has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="drowsy_medication" value="{{ $value }}" class="mr-2" @checked(old('drowsy_medication') === $value) required>{{ $label }}</label>@endforeach</div></div>
          <div><div class="mb-3 block text-sm font-medium">Apakah Anda siap untuk bekerja? <span class="text-red-500">*</span></div><div class="health-check__choices">@foreach(['ya'=>'Ya, siap bekerja','tidak'=>'Tidak siap bekerja','off_day'=>'Off day'] as $value=>$label)<label class="cursor-pointer rounded-lg border border-slate-200 p-3 has-[:checked]:border-[#8d624f] has-[:checked]:bg-[#faf3ee]"><input type="radio" name="work_readiness" value="{{ $value }}" class="mr-2" @checked(old('work_readiness') === $value) required>{{ $label }}</label>@endforeach</div></div>
        </div>
      </section>

      <footer class="health-check__submit">
        <p>Pastikan jawaban sesuai kondisi Anda sebelum dikirim.</p>
        <x-ui.button type="submit" variant="primary" icon="circle-check">Kirim checklist</x-ui.button>
        <span>Data digunakan untuk pemantauan kesiapan kerja.</span>
      </footer>
    </form>
    <p class="health-check__footer">MANDALA <span>Management System Andalan</span></p>
  </main>
  <x-ui.toast-provider />
</body>
</html>
