<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">
  <title>Health Assesment Checklist · {{ config('app.name', 'AAP Form Builder') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="health-check-page">
  <div class="health-check-app">
    <!-- Top Navigation App Bar -->
    <header class="pwa-topbar">
      <div class="pwa-topbar__inner">
        <a href="{{ route('wellness.create') }}" aria-label="Health Assesment Checklist" class="pwa-brand">
          <img src="{{ asset('assets/images/logomandala.png') }}" alt="Mandala Management System Andalan">
        </a>
        <div class="pwa-status-badge">
          <span>Pre-Shift Assessment</span>
        </div>
      </div>
    </header>

    <div class="pwa-container">
      <!-- Hero Banner -->
      <div class="pwa-hero">
        <h1>Health Assesment Checklist</h1>
        <p>Awali shift dengan mengenali kondisi fisik & mental Anda hari ini. Isi sesuai keadaan yang sebenarnya.</p>
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

            <x-ui.form-field name="department_id" label="Departemen" required>
              <x-ui.form-select id="department_id" name="department_id" required :invalid="$errors->has('department_id')">
                <option value="">Pilih departemen</option>
                @foreach($departments as $department)<option value="{{ $department->id }}" @selected((string)old('department_id') === (string)$department->id)>{{ $department->name }}</option>@endforeach
              </x-ui.form-select>
              @if($departments->isEmpty())<p class="mt-2 text-xs text-red-700">Daftar departemen belum tersedia. Hubungi admin.</p>@endif
            </x-ui.form-field>

            <x-ui.form-field name="site_id" label="Site / Lokasi Kerja" required>
              <x-ui.form-select id="site_id" name="site_id" required :invalid="$errors->has('site_id')">
                <option value="">Pilih site</option>
                @foreach($sites as $site)<option value="{{ $site->id }}" @selected((string)old('site_id') === (string)$site->id)>{{ $site->code }} — {{ $site->name }}</option>@endforeach
              </x-ui.form-select>
              @if($sites->isEmpty())<p class="mt-2 text-xs text-red-700">Daftar site belum tersedia. Hubungi admin.</p>@endif
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
              <span class="pwa-question-label">Berapa lama Anda tidur sebelum shift ini? <span class="text-red-500">*</span></span>
              <div class="pwa-pill-grid">
                @foreach(['lebih_6_jam' => 'Lebih dari 6 jam (Cukup)', '6_jam' => 'Pas 6 jam', 'kurang_6_jam' => 'Kurang dari 6 jam (Kurang)'] as $value => $label)
                  <label class="pwa-pill-option">
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
              <span class="pwa-question-label">Apakah Anda sedang mengalami keluhan kesehatan saat ini? <span class="text-red-500">*</span></span>
              <div class="pwa-pill-grid">
                <label class="pwa-pill-option">
                  <input type="radio" name="health_complaint" value="tidak" x-model="complaint" @checked(old('health_complaint') === 'tidak') required>
                  <span>Tidak ada keluhan</span>
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
          <div x-data="{ medication: @js(old('drowsy_medication', '')) }" class="pwa-question">
            <span class="pwa-question-label">Apakah Anda sedang mengonsumsi obat dengan efek samping kantuk / menurunkan konsentrasi? <span class="text-red-500">*</span></span>
            <div class="pwa-pill-grid">
              <label class="pwa-pill-option">
                <input type="radio" name="drowsy_medication" value="tidak" x-model="medication" @checked(old('drowsy_medication') === 'tidak') required>
                <span>Tidak</span>
              </label>
              <label class="pwa-pill-option">
                <input type="radio" name="drowsy_medication" value="ya" x-model="medication" @checked(old('drowsy_medication') === 'ya') required>
                <span>Ya</span>
              </label>
            </div>
            <div x-show="medication === 'ya'" x-cloak class="pwa-subfield-box">
              <x-ui.form-field name="drowsy_medication_details" label="Nama obat yang dikonsumsi" required helper="Tuliskan semua nama obat yang sedang Anda konsumsi.">
                <x-ui.form-textarea id="drowsy_medication_details" name="drowsy_medication_details" rows="2" maxlength="2000" x-bind:required="medication === 'ya'" x-bind:disabled="medication !== 'ya'" :invalid="$errors->has('drowsy_medication_details')" placeholder="Tuliskan nama obat di sini.">{{ old('drowsy_medication_details') }}</x-ui.form-textarea>
              </x-ui.form-field>
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
                <label class="pwa-pill-option">
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
        <button type="submit" class="pwa-submit-btn" @disabled($departments->isEmpty() || $sites->isEmpty())>
          <x-ui.lucide name="send" class="w-4 h-4" />
          <span>Kirim Health Assessment</span>
        </button>
        <p class="pwa-submit-hint">Pastikan semua data diisi dengan jujur demi keselamatan kerja bersama.</p>
      </footer>
    </form>
    </div>

    <!-- Footer Branding -->
    <footer class="pwa-footer">
      <div class="pwa-footer__powered">
        <span class="pwa-footer__powered-label">Powered by</span>
        <img src="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}" alt="Andalan" class="pwa-footer__logo">
      </div>
    </footer>
  </div>

  <x-ui.toast-provider />
</body>
</html>
