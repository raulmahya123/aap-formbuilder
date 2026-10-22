<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">
  <title>Health Assesment Checklist · {{ config('app.name', 'AAP Form Builder') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="health-check-page">
  <div class="health-check-app">
    <!-- Mobile PWA Top App Bar -->
    <header class="pwa-topbar">
      <a href="{{ route('wellness.create') }}" aria-label="Health Assesment Checklist" class="pwa-brand">
        <img src="{{ asset('assets/images/logomandala.png') }}" alt="Mandala Management System Andalan">
      </a>
      <div class="pwa-status-badge">
        <span>Pre-Shift</span>
      </div>
    </header>

    <!-- Hero Card Header -->
    <div class="pwa-hero">
      <div class="pwa-hero__eyebrow">
        <x-ui.lucide name="shield-check" />
        <span>Pemeriksaan Mandiri</span>
      </div>
      <h1>Health Assesment Checklist</h1>
      <p>Awali shift dengan mengenali kondisi fisik & mental Anda hari ini.</p>
      
      <div class="pwa-hero__chips">
        <span class="pwa-chip">
          <x-ui.lucide name="clipboard-check" /> Tanpa Login
        </span>
        <span class="pwa-chip pwa-chip--amber">
          <x-ui.lucide name="alert-circle" /> Wajib Lengkap
        </span>
      </div>
    </div>

    <!-- Form -->
    <form id="page-primary-form" method="POST" action="{{ route('wellness.store') }}" class="pwa-form">
      @csrf

      <!-- Section 1: Data Karyawan -->
      <section class="pwa-card">
        <div class="pwa-card__header">
          <div class="pwa-card__number">01</div>
          <div class="pwa-card__title">
            <h2>Data Karyawan</h2>
            <p>Identitas & lokasi penugasan kerja</p>
          </div>
        </div>
        
        <div class="pwa-fields">
          <x-ui.form-field name="name" label="Nama Lengkap" required>
            <x-ui.form-input id="name" name="name" :value="old('name')" required placeholder="Masukkan nama lengkap Anda" />
          </x-ui.form-field>

          <x-ui.form-field name="nik" label="NIK (Nomor Induk Karyawan)" required>
            <x-ui.form-input id="nik" name="nik" :value="old('nik')" required placeholder="Contoh: 12345678" />
          </x-ui.form-field>

          <x-ui.form-field name="position" label="Jabatan" required>
            <x-ui.form-input id="position" name="position" :value="old('position')" required placeholder="Contoh: Operator / Pengawas / Mekanik" />
          </x-ui.form-field>

          <x-ui.form-field name="site" label="Site / Lokasi Kerja" required>
            <x-ui.form-input id="site" name="site" :value="old('site')" required placeholder="Contoh: Site Mandala / Muara Enim" />
          </x-ui.form-field>
        </div>
      </section>

      <!-- Section 2: Kondisi Sebelum Shift -->
      <section class="pwa-card">
        <div class="pwa-card__header">
          <div class="pwa-card__number">02</div>
          <div class="pwa-card__title">
            <h2>Kesiapan Sebelum Shift</h2>
            <p>Jawab sesuai kondisi nyata yang Anda rasakan</p>
          </div>
        </div>

        <div class="pwa-question-group">
          <!-- Shift -->
          <div class="pwa-question">
            <span class="pwa-question-label">Jadwal Shift Anda <span class="text-red-500">*</span></span>
            <div class="pwa-pill-grid">
              @foreach(['pagi' => ['Shift Pagi', 'sun'], 'malam' => ['Shift Malam', 'moon']] as $value => [$label, $icon])
                <label class="pwa-pill-option">
                  <input type="radio" name="shift" value="{{ $value }}" @checked(old('shift') === $value) required>
                  <x-ui.lucide :name="$icon" class="w-4 h-4 text-slate-500" />
                  <span>{{ $label }}</span>
                </label>
              @endforeach
            </div>
          </div>

          <!-- Durasi Tidur -->
          <div class="pwa-question">
            <span class="pwa-question-label">Berapa lama Anda tidur sebelum shift? <span class="text-red-500">*</span></span>
            <div class="pwa-pill-grid pwa-pill-grid--stack">
              @foreach(['lebih_6_jam' => 'Lebih dari 6 jam (Cukup)', '6_jam' => 'Pas 6 jam', 'kurang_6_jam' => 'Kurang dari 6 jam (Kurang)'] as $value => $label)
                <label class="pwa-pill-option" style="justify-content: flex-start; text-align: left;">
                  <input type="radio" name="sleep_before_shift" value="{{ $value }}" @checked(old('sleep_before_shift') === $value) required>
                  <span>{{ $label }}</span>
                </label>
              @endforeach
            </div>
          </div>

          <!-- Kualitas Tidur -->
          <div x-data="{ quality: @js(old('sleep_quality', '')) }" class="pwa-question">
            <x-ui.form-field name="sleep_quality" label="Bagaimana kualitas tidur Anda?" required>
              <x-ui.form-select id="sleep_quality" name="sleep_quality" x-model="quality" required :invalid="$errors->has('sleep_quality')">
                <option value="">Pilih kualitas tidur...</option>
                <option value="baik" @selected(old('sleep_quality') === 'baik')>Nyenyak & Segar (Baik)</option>
                <option value="cukup" @selected(old('sleep_quality') === 'cukup')>Cukup Baik</option>
                <option value="buruk" @selected(old('sleep_quality') === 'buruk')>Sering Terbangun / Gelisah (Buruk)</option>
              </x-ui.form-select>
            </x-ui.form-field>

            <div x-show="quality === 'buruk'" x-cloak class="pwa-subfield-box">
              <x-ui.form-field name="sleep_quality_reason" label="Apa penyebab tidur kurang baik?" required>
                <x-ui.form-textarea id="sleep_quality_reason" name="sleep_quality_reason" rows="2" maxlength="2000" x-bind:required="quality === 'buruk'" x-bind:disabled="quality !== 'buruk'" :invalid="$errors->has('sleep_quality_reason')" placeholder="Contoh: kebisingan, cemas, atau suhu ruangan panas.">{{ old('sleep_quality_reason') }}</x-ui.form-textarea>
              </x-ui.form-field>
            </div>
          </div>

          <!-- Mengantuk Berat -->
          <div class="pwa-question">
            <span class="pwa-question-label">Apakah Anda merasa mengantuk berat atau sulit berkonsentrasi? <span class="text-red-500">*</span></span>
            <div class="pwa-pill-grid">
              <label class="pwa-pill-option">
                <input type="radio" name="heavy_drowsiness" value="tidak" @checked(old('heavy_drowsiness') === 'tidak') required>
                <span>Tidak Mengantuk</span>
              </label>
              <label class="pwa-pill-option">
                <input type="radio" name="heavy_drowsiness" value="ya" @checked(old('heavy_drowsiness') === 'ya') required>
                <span>Ya, Mengantuk</span>
              </label>
            </div>
          </div>

          <!-- Keluhan Kesehatan -->
          <div x-data="{ complaint: @js(old('health_complaint', '')) }" class="pwa-question">
            <span class="pwa-question-label">Apakah Anda sedang mengalami keluhan kesehatan? <span class="text-red-500">*</span></span>
            <div class="pwa-pill-grid">
              <label class="pwa-pill-option">
                <input type="radio" name="health_complaint" value="tidak" x-model="complaint" @checked(old('health_complaint') === 'tidak') required>
                <span>Tidak Ada (Sehat)</span>
              </label>
              <label class="pwa-pill-option">
                <input type="radio" name="health_complaint" value="ya" x-model="complaint" @checked(old('health_complaint') === 'ya') required>
                <span>Ada Keluhan</span>
              </label>
            </div>

            <div x-show="complaint === 'ya'" x-cloak class="pwa-subfield-box">
              <x-ui.form-field name="health_complaint_details" label="Rincian Keluhan / Gejala" required helper="Jelaskan bagian tubuh atau gejala yang dirasakan.">
                <x-ui.form-textarea id="health_complaint_details" name="health_complaint_details" rows="2" maxlength="2000" x-bind:required="complaint === 'ya'" x-bind:disabled="complaint !== 'ya'" :invalid="$errors->has('health_complaint_details')" placeholder="Contoh: pusing, flu, atau nyeri otot.">{{ old('health_complaint_details') }}</x-ui.form-textarea>
              </x-ui.form-field>
            </div>
          </div>

          <!-- Obat Berefek Kantuk -->
          <div class="pwa-question">
            <span class="pwa-question-label">Apakah sedang minum obat berefek samping mengantuk? <span class="text-red-500">*</span></span>
            <div class="pwa-pill-grid">
              <label class="pwa-pill-option">
                <input type="radio" name="drowsy_medication" value="tidak" @checked(old('drowsy_medication') === 'tidak') required>
                <span>Tidak Minum Obat</span>
              </label>
              <label class="pwa-pill-option">
                <input type="radio" name="drowsy_medication" value="ya" @checked(old('drowsy_medication') === 'ya') required>
                <span>Ya, Sedang Minum</span>
              </label>
            </div>
          </div>

          <!-- Kesiapan Bekerja -->
          <div class="pwa-question">
            <span class="pwa-question-label">Apakah Anda siap dan fit untuk bekerja? <span class="text-red-500">*</span></span>
            <div class="pwa-pill-grid pwa-pill-grid--stack">
              @foreach([
                'ya' => ['Ya, Siap & Fit Bekerja', 'check-circle-2'],
                'tidak' => ['Tidak Siap Bekerja (Kondisi Kurang Sehat)', 'alert-triangle'],
                'off_day' => ['Off Day / Jadwal Libur', 'calendar']
              ] as $value => [$label, $icon])
                <label class="pwa-pill-option" style="justify-content: flex-start; text-align: left;">
                  <input type="radio" name="work_readiness" value="{{ $value }}" @checked(old('work_readiness') === $value) required>
                  <x-ui.lucide :name="$icon" class="w-4 h-4 text-slate-500" />
                  <span>{{ $label }}</span>
                </label>
              @endforeach
            </div>
          </div>
        </div>
      </section>

      <!-- Submit Section -->
      <footer class="pwa-submit-section">
        <button type="submit" class="pwa-submit-btn">
          <x-ui.lucide name="send" class="w-4 h-4" />
          <span>Kirim Health Assessment</span>
        </button>
        <p class="pwa-submit-hint">Pastikan semua data diisi dengan jujur demi keselamatan kerja bersama.</p>
      </footer>
    </form>

    <!-- Footer Branding -->
    <footer class="pwa-footer">
      <div class="pwa-footer__brand">MANDALA</div>
      <div class="pwa-footer__sub">Management System Andalan Nusantara Pratama</div>
    </footer>
  </div>

  <x-ui.toast-provider />
</body>
</html>
