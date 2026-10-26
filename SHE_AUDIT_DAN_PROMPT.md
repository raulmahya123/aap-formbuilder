# Audit SHE Mandala: form input, aliran data, dan kelengkapan modul

Tanggal audit: 26 Oktober 2026.

## Batas audit

Audit statis terhadap repository lokal: routes web/API, controller, service, model, migration, Blade, dan pengujian yang tersedia. Tidak mengakses database produksi, tidak mengirim transaksi, tidak mengubah aplikasi, dan tidak membuktikan kondisi deployment server. Status “ada” berarti implementasi ditemukan di source code, bukan sertifikasi bahwa fitur sudah benar di produksi.

## Kesimpulan

Menu monitoring tidak harus mempunyai form input sendiri. Tetapi setiap angka harus mempunyai sumber transaksi yang jelas dan dapat ditelusuri. Saat ini ada dashboard, model, dan sebagian form; rantai input → verifikasi → sumber data resmi → KPI → laporan belum konsisten.

Form yang ditemukan: manhours manual/import, target KPI, target SAP, risk assessment, PICA manual/progress/verifikasi, upload/revisi/approval dokumen, HIPO, CCM, input indikator harian, dan form dinamis Mandala.

Belum ditemukan alur CRUD khusus yang lengkap untuk pencatatan insiden terstruktur, inspeksi, PTO, audit/investigasi, training course/record, environment/waste, dan permit dalam modul HSE. Sebagian data insiden/inspeksi/PTO dibuat oleh integrasi FormEntry setelah approval. Ini tidak sama dengan memiliki form domain yang lengkap dan dapat dikoreksi.

## 1. Matriks seluruh menu

Legenda: Baca = analitik/monitoring; Parsial = komponen tersedia tetapi sumber/alur belum lengkap; Input = form dan endpoint tulis ditemukan. Semua masih membutuhkan QA fungsional.

### SHE Performance

| Menu | Kondisi source code | Input/sumber yang dibutuhkan | Gap utama |
|---|---|---|---|
| Performance Dashboard | Baca dari HseDashboardService::safety | Insiden tervalidasi, jam kerja, target | Sumber target ganda, beberapa query agregat tidak mengikuti site |
| Fatality Monitoring | Parsial: drill-down HseIncident | Satu Incident Register dengan klasifikasi Fatality | Belum ditemukan form insiden khusus; relasi reportedBy tidak tersedia pada model |
| LTI Monitoring | Parsial: filter Lost Time Injury | Insiden + tanggal mulai/selesai lost time + hari hilang | Integrasi berbasis judul form rawan salah klasifikasi |
| Injury Non-LTI | Parsial: RWC/MTC/FAC | Insiden + klasifikasi terstruktur | Klasifikasi bukan input teks/judul bebas |
| Property Damage | Parsial: filter tipe Property Damage | Insiden kerusakan aset + aset dan biaya | Cabang mapping integrasi bisa tidak tercapai untuk judul property damage saja |
| PDFR Monitoring | Rate dihitung di service; drill-down masih hitungan kasus | Kasus Property Damage + denominator jam kerja resmi | Unit/count pada drill-down belum sama dengan rate dashboard |
| Property Damage Cost | Parsial: safety mengambil IndicatorDaily kode PD_COST | Biaya insiden terverifikasi atau ledger biaya yang disepakati | Drill-down pd-cost belum memfilter khusus; sumber biaya belum satu |
| Environmental Accident | Parsial: filter Environmental | Insiden lingkungan + klasifikasi dampak | Jangan dicampur dengan transaksi limbah/emisi rutin |
| PAK Monitoring | Parsial: filter PAK | Record PAK dengan akses terbatas, status validasi | Belum ada input domain khusus; data sensitif perlu izin terpisah |
| KAPTK Monitoring | Parsial: filter KAPTK | Definisi bisnis dan klasifikasi resmi KAPTK | Makna, rumus, denominator belum boleh diasumsikan |
| Near Miss | Parsial: HseIncident kategori Near Miss; fallback HipoReport | Satu sumber kejadian + pembeda hazard/HIPO/near miss | Fallback menghitung semua HIPO, bukan hanya near miss; site belum konsisten |
| Manhours Management | Input: SheManhour, submit, validate/return, import | Tanggal, site, department, tenaga kerja, jam kerja, lost hours | Import memilih Site::first(); Excel diproses fgetcsv; belum ada correction/resubmit lengkap |
| KPI Target Management | Input: SheKpiTarget updateOrCreate | KPI, periode, site, nilai, unit, arah target | Dashboard utama memakai HseKpiTarget; target berpotensi tidak memengaruhi dashboard |

### Safety Accountability (SAP)

| Menu | Kondisi source code | Input/sumber yang dibutuhkan | Gap utama |
|---|---|---|---|
| SAP Dashboard | Parsial: inspection, PTO, HIPO + target | Aktivitas valid per orang/site/department/periode | Target default 60/40/100, grafik ranking hard-coded, denominator tidak konsisten |
| Inspection Achievement | Baca HseInspection | Form Inspection dan checklist/temuan | Integrasi form ada; form inspeksi domain khusus belum ditemukan |
| PTO Achievement | Baca HseInspection type PTO | Form PTO: observer, pekerja/tugas, langkah aman, deviasi | Belum ada form domain khusus; inspeksi umum di dashboard dapat turut menghitung PTO |
| Hazard Report Achievement | Baca HipoReport | Hazard Report dengan jenis dan status validasi jelas | Semua HIPO tidak otomatis merupakan hazard report yang sah |
| Employee SAP Performance | Parsial: daftar user | Target individu, actual tiap aktivitas, achievement | Progress bar 85% dan “Aktif Mengisi” statis |
| Department SAP Performance | Parsial: daftar department dan jumlah user | Agregasi aktivitas/target department dengan keanggotaan historis | Persentase rand(80,100), status “Sangat Baik” statis |
| SAP Target Management | Input: SheSapTarget::create | Target per scope dan periode + prioritas override | user_id diterima backend tetapi belum ada pilihan pengguna di form; create dapat menambah target ganda |

### Risk Register

| Menu | Kondisi source code | Input/sumber yang dibutuhkan | Gap utama |
|---|---|---|---|
| Risk Dashboard | Baca HseRiskAssessment/PICA | Assessment yang dapat direview | Penggunaan filter dept_id tidak cocok dengan service |
| Risk Register List | Input assessment dari modal + list | Aktivitas, bahaya, kontrol, L/C inherent/residual, owner, treatment | Create ada; edit, revisi, approval, histori assessment belum lengkap |
| High Risk Activities | Baca High/Extreme dan CCM | Assessment serta keterkaitan aktivitas–critical control | CCM latest list belum tampak dibatasi scope site |
| Risk Assessment Analytics | Baca riskCompliance/risk matrix | Assessment approved dan versi/matrix resmi | Inherent/residual butuh QA rumus dan periode konsisten |
| HIPO Monitoring | Modul HIPO existing dan metrik dashboard | HIPO dengan klasifikasi, risiko, kontrol, evidence | Model HIPO memakai Extreme; sebagian query service memakai Critical |
| Critical Control Management | Form dan CRUD CCM existing | Pemeriksaan per aktivitas/control, evidence, verifikasi | Hubungan master risk–control–CCM perlu eksplisit; bukan sekadar jobsite string |
| Risk Control Monitoring | Baca PICA + form progress | Tindakan mitigasi, PIC, due date, bukti, verifikasi | Endpoint perubahan belum terlihat melakukan authorization objek/site |

### PICA Management

| Menu | Kondisi source code | Input/sumber yang dibutuhkan | Gap utama |
|---|---|---|---|
| PICA Dashboard | Baca HseCorrectiveAction | Satu register tindakan + source record | Dept filter salah key; beberapa ranking/tren perlu scope yang sama |
| All PICA | Input manual dari modal | Source, temuan, akar masalah, korektif/preventif, PIC, due date | source_module teks bebas; relasi sumber belum wajib untuk input manual |
| PICA Audit | Filter source_module mengandung Audit | Audit → finding → PICA | Form audit/temuan audit belum ditemukan lengkap |
| PICA Investigation | Filter Investigation | Insiden → investigasi → root cause → PICA | Integrasi insiden high/critical ada; form investigasi lengkap belum ditemukan |
| PICA Inspection | Filter Inspection | Inspeksi → finding → PICA | Integrasi approved FormEntry ada; perlu checklist/finding terstruktur |
| PICA PTO | Filter PTO | Deviasi observasi tugas → PICA | Integrasi ada; perlu form PTO dan penanggung jawab yang jelas |
| PICA Hazard Report | Filter Hazard | Hazard report valid → PICA | Integrasi berbasis judul dan field bebas, belum pemetaan schema eksplisit |
| PICA Verification | Input approve/reject dari detail | Bukti implementasi, verifier, keputusan, catatan | picaUpdate bisa langsung Closed sehingga melewati jalur verify |
| Overdue PICA | Baca due_date < hari ini dan belum Closed | Due date dan kebijakan SLA | Harus mencakup semua status unresolved; definisi timezone/cutoff perlu seragam |

### Document Repository

| Menu | Kondisi source code | Input/sumber yang dibutuhkan | Gap utama |
|---|---|---|---|
| Document Dashboard | Baca SheDocument | Upload metadata/file, versi, owner, review | Salah key filter dept_id; ada repository Document/Form lain yang perlu dipetakan |
| Policy (Kebijakan) | Input upload, kategori Policy | Metadata, department/site, file dan masa review | Izin objek/file belum dibuktikan; bukan sekadar semua admin boleh baca |
| SOP Prosedur | Input upload, kategori SOP | Register yang sama dengan Policy | Hindari tabel/CRUD terpisah per kategori |
| Instruksi Kerja (IK) | Input upload, kategori IK | Register yang sama | Integrasi dengan Mandala existing belum jelas |
| Form Documents | Input upload, kategori Form | Bedakan template file dan form dinamis yang bisa disubmit | Kategori file bukan otomatis Form Builder |
| Document Approval | Input approve_publish/reject | Reviewer/approver yang ditentukan, histori keputusan | Tidak terlihat guard transisi, otorisasi approver, atau pemisahan uploader/approver |
| Expiring Review Due | Baca Published dengan review_date <= H+30 | Review/revisi, notifikasi, keputusan perpanjangan | Alur obsolete/revisi ada tetapi histori, akses, transaksi file perlu diperkuat |

### Analytics & Reports

| Menu | Kondisi source code | Input/sumber yang dibutuhkan | Gap utama |
|---|---|---|---|
| SHE Overview | Baca banyak model | Sumber transaksi semua modul | Bukan tempat mengisi angka KPI manual |
| Incident & Nearmiss | Baca HseIncident | Incident Register + investigasi; integrasi HIPO yang eksplisit | Belum ditemukan CRUD insiden khusus lengkap |
| Inspection Module | Dashboard HseInspection/HseFinding | Rencana inspeksi, checklist, finding, tindak lanjut | Modul input khusus belum lengkap; bukan cukup grafik saja |
| Training Matrix | Baca HseTrainingCourse/HseTrainingRecord | Master kompetensi, requirement jabatan, jadwal, peserta, hasil, sertifikat | Belum ditemukan CRUD course/record dalam controller aplikasi |
| Environment & Waste | Baca HseEnvironmentalRecord dan insiden | Record limbah, manifest, vendor, izin, ukuran/satuan, bukti | Belum ditemukan CRUD transaksi lingkungan/limbah khusus |
| Site Performance | Baca agregat lintas modul | Data semua modul dengan scope site sama | Perlu rekonsiliasi kartu vs detail vs export |
| SHE Report Center | Input export CSV + riwayat HseReportRun | Agregasi sumber terverifikasi, snapshot filter | Endpoint export ada; PDF/XLSX/approval report belum terbukti tersedia |
| Notification Center | Baca/read state; reminder digenerate saat index | Event transaksi + scheduler + deduplikasi | Tidak ditemukan jadwal reminder di routes/console.php; generate saat membuka halaman bukan otomasi terjadwal |

## 2. Temuan prioritas dan bukti source

### P0 — akses dan integritas

1. Route hse/dashboard dibungkus auth + can:is-admin. Itu belum cukup sebagai izin objek/site/aksi. Method picaDetail, picaUpdate, picaVerify, manhoursValidate, riskAssessmentStore serta document show/revise/approve/obsolete/download/preview perlu policy objek dan scope. Di method yang diperiksa, pengecekan tersebut belum terlihat; pemeriksaan detail bypass lintas site harus diuji. Ini adalah risiko dari source, bukan klaim eksploitasi produksi.
2. Semua route baru hse.dashboard.* dipetakan ke satu feature `hse` oleh FeatureAccess. Izin per modul/aksi belum terpisah. Manage Users dan Input Access harus tetap khusus role literal super_admin sesuai keputusan sebelumnya.
3. File dokumen dan evidence PICA disimpan di disk public. Bila storage publik disajikan langsung web server, tautan file dapat melewati policy controller. Audit lokasi file nyata, URL lama, dan kebutuhan migrasi privat sebelum mengganti storage.
4. picaUpdate menerima Closed dan mengisi verified_by sendiri. picaVerify juga dapat approve tanpa terlihat guard status Pending Verification atau bukti wajib. Harus ditetapkan satu state machine, satu jalur tutup, dan siapa yang berhak memverifikasi.
5. manhoursImport memilih Site::first() dan fallback id 1. File Excel diterima validasi tetapi dibaca sebagai CSV dengan fgetcsv. Tidak ada pemetaan site per baris, preview, error baris, atau deduplikasi yang memadai.

### P1 — data salah atau halaman gagal

6. HseDashboardService::filters mengembalikan `department_id`; SheOperationsController dan SheDocumentController berkali-kali membaca `dept_id`. Berpotensi Undefined array key dan gagal render, atau filter tidak diterapkan. Filter juga belum memakai session active_site_id sebagaimana SiteSelection existing.
7. performanceIndicator eager-load `reportedBy`, sementara HseIncident menyediakan `reporter()`. Ketika ada record, berisiko RelationNotFoundException.
8. SAP department menampilkan rand(80,100) di view; employee menampilkan lebar 85% dan teks statis. SAP dashboard menggunakan data ranking [45,38,30,26,18,12] dan [58,42,35,29,21,15]. Ini bukan perhitungan performa aktual.
9. SAP target menjumlah semua SheSapTarget tanpa scope periode/site dan mengganti nilai nol/tidak ada dengan 60/40/100. Inspeksi total memasukkan PTO, lalu PTO dijumlah lagi pada total actual. Detail dan grafik bulanan belum menggunakan filter/status yang sama.
10. SheKpiTarget dipakai form target baru dan detail indikator; HseKpiTarget dipakai service target dashboard utama. Ada dua sumber target yang belum disatukan.
11. PD_COST mengambil IndicatorDaily tanpa scope site; fallback near miss mengambil semua HIPO ketika jumlah HseIncident nol; HIPO high query menggunakan Critical alih-alih Extreme. Sumber dan klasifikasi perlu direkonsiliasi.
12. Drill-down indicator pd-cost belum mempunyai constraint sendiri; pdfr masih menampilkan actualCount. Detail kasus dan unit KPI tidak boleh disamakan.

### P1 — integrasi form belum andal

13. EntryApprovalController memanggil SheFormIntegrationService hanya pada status approved. Harus dijelaskan bahwa mengisi form saja belum menjamin dashboard berubah.
14. Integrasi mendeteksi jenis dari judul form dengan str_contains. “non-lti” mengandung “lti”; “property damage” saja tidak memenuhi kondisi luar incident; form “checklist” apa pun bisa dianggap inspeksi; judul dengan beberapa keyword bisa menghasilkan beberapa domain record.
15. Mapping mengisi default score 100 serta fatality lost_days 6000 tanpa konfigurasi kebijakan yang terlihat. Jangan menganggap default tersebut aturan perusahaan yang telah disetujui.
16. firstOrCreate mencegah sebagian duplikat tetapi tidak memperbarui record saat sumber dikoreksi. createPica tetap dapat mengirim notifikasi saat record sudah ada.
17. syncEntryToShe menangkap Throwable lalu mengembalikan status error. Caller approval tidak mengecek hasil. Approval dapat tampak sukses walaupun integrasi gagal. Perlu status sinkronisasi, retry, audit dan transaksi/idempotency yang jelas.
18. Cabang hazard mengisi HipoReport dengan sebagian field; cocokkan semua kolom NOT NULL/enum pada migration sebelum menyatakan integrasi berhasil. Perlu uji MySQL dan data representatif, bukan hanya view kosong.

### P2 — kelengkapan alur dan UX

19. Risk punya create, tetapi belum ditemukan lifecycle edit/review/revision/retire lengkap. PICA dan dokumen juga perlu histori transisi terstruktur, bukan hanya string progress_notes.
20. SAP target backend menerima user_id tetapi form hanya memilih site/department/periode; target individu belum dapat diatur dari UI yang diperiksa. create tanpa upsert/unique scope dapat menambah duplikat.
21. Method target dan beberapa query trend/ranking tidak menggunakan scope yang sama dengan halaman utama. “Filter ada di UI” belum membuktikan semua dataset ikut berubah.
22. Banyak halaman baru memakai form/modal/tabel langsung dengan style sendiri dan pagination 20/25. Perlu migrasi ke shared component, default 10 dan pilihan 10/25/50/100 sesuai sistem existing.
23. Reminder digenerate saat Notification Center dibuka; routes/console.php hanya berisi command inspire. Periksa scheduler eksternal bila ada; belum ada bukti otomasi reminder di repository yang diaudit.
24. SheNotification::notifyRole memakai kolom users.is_admin yang tidak ditemukan pada model/migration yang diperiksa. Jika method dipanggil, verifikasi schema server sebelum mengandalkannya.
25. SheFeatureIntegrationTest menggunakan DatabaseTransactions dan User::first(), bukan setup RefreshDatabase mandiri. Keberadaan file tes tidak membuktikan seluruh alur berjalan. Tes tersebut belum dijalankan pada audit ini.

## 3. Arsitektur input yang disarankan

Tidak perlu membuat form “Input Fatality”, “Input PDFR”, dan “Input LTIFR” yang terpisah. Gunakan sumber transaksi berikut, dengan CTA menuju sumber yang relevan dari halaman monitoring:

| Form/register sumber | Data minimum | Mengisi menu |
|---|---|---|
| Incident & Nearmiss Register | Nomor, waktu, lokasi/site, department, pelapor, pihak/aset terdampak, kategori/tipe, narasi, severity, evidence, status investigasi/validasi | Fatality, LTI, Non-LTI, PD, Environmental, Near Miss; klasifikasi PAK/KAPTK setelah definisi disepakati |
| Incident impact / cost / lost time | Relasi incident, klasifikasi dampak, hari hilang terverifikasi, item biaya, currency, bukti, persetujuan | LTISR, Property Damage Cost dan laporan dampak |
| Manhours | Tanggal/periode, site, department, headcount, normal/overtime/lost hours, submitter, validator | Manhours, denominator rate |
| Inspection | Checklist/version, inspector, site, area, jadwal/pelaksanaan, jawaban, score aktual, findings | Inspection Achievement, Inspection Module, PICA Inspection |
| PTO | Observer, orang/tim yang diamati, tugas/langkah, perilaku/kondisi aman, deviasi, rekomendasi, evidence | PTO Achievement dan PICA PTO |
| Hazard/HIPO | Bahaya/kejadian, kategori yang eksplisit, potensi dampak, risiko, kontrol, stop work, PIC, evidence, validasi | Hazard Achievement, HIPO Monitoring dan PICA Hazard |
| Audit & Investigation | Rencana/sumber incident, checklist atau chronology, temuan, root cause, investigator/auditor, hasil approved | PICA Audit dan PICA Investigation |
| Risk Assessment | Aktivitas/bahaya, kategori, inherent/residual L/C, controls, owner, treatment, due date, review/approval | Risk Register/Analytics/High Risk dan PICA mitigation |
| CCM | Master critical risk/control, aktivitas/site, pemeriksa, jawaban, evidence, ineffective-control action | CCM dan Risk Control Monitoring |
| PICA | Source record/finding, akar masalah, corrective/preventive actions, PIC, verifier, deadline, evidence dan keputusan | Semua submenu PICA tanpa duplikasi register |
| KPI/SAP Target | Key terdaftar, scope, periode, nilai, unit/direction, effective dates dan histori | Target management dan comparison semua dashboard |
| Training | Master course/competency, requirement jabatan, jadwal, peserta, hasil, sertifikat/expired | Training Matrix |
| Environment & Waste | Jenis transaksi, site/tanggal, satuan, volume/berat, vendor/manifest/permit, evidence | Environment & Waste |
| Controlled Document | Metadata, jenis, file, owner, reviewer/approver, versi, scope, effective/review date, revision history | Seluruh kategori Document Repository |

Catatan: metadata Form Builder bisa digunakan untuk form domain jika mapping eksplisit dan field wajibnya terjamin. Jangan membuat dua form dan dua tabel yang mencatat kejadian yang sama. Tetapkan sumber resmi sebelum migrasi; simpan referensi legacy, reconciliation report, dan hindari pemindahan data otomatis tanpa pemetaan yang ditinjau.

## 4. Alur operasional yang diharapkan

1. Petugas membuat transaksi dari CTA “Tambah” di modul sumber, atau dari quick access yang menuju form yang sama.
2. Form memuat data master dari backend: site yang diizinkan, department yang valid, user/PIC yang dapat ditugaskan, kategori/indikator/checklist resmi.
3. Draft disimpan; submit memvalidasi field domain dan evidence. Field sistem seperti pelapor, role, approval actor ditentukan server.
4. Reviewer memeriksa dan return/reject dengan alasan. Aturan existing tetap dipakai sampai perubahan bisnis disetujui.
5. Hanya data dengan status yang dinyatakan eligible dalam kamus KPI yang dihitung sebagai actual. Dashboard menampilkan pending terpisah.
6. Temuan yang memerlukan tindak lanjut menghasilkan PICA dengan source id tetap, tanpa duplikat saat retry.
7. PIC mengirim bukti, verifier memutuskan; rejected kembali ke PIC. Penutupan membutuhkan hak dan syarat bukti yang sesuai.
8. KPI dan SAP memakai definisi, scope, periode, timezone, denominator dan target yang sama pada kartu, chart, detail dan export.
9. Tiap angka bisa di-drill-down sampai record sumber dan histori persetujuannya. Zero data dibedakan dari belum ada denominator/target.

## 5. Prioritas implementasi

- P0: policy objek/site dan file, pembatasan aksi, state machine PICA/document, perbaikan import manhours.
- P1: crash relasi/filter, hilangkan actual palsu, satukan KPI target dan definisi source, betulkan integrasi FormEntry.
- P2: lengkapi form Incident, Inspection, PTO, Audit/Investigation dan siklus koreksinya; sambungkan ke dashboard.
- P3: lengkapi Training, Environment/Waste, permit/compliance serta document lifecycle tanpa menduplikasi modul lama.
- P4: laporan, scheduler/queue notification, performa query dan QA lintas modul.

## 6. Prompt pengembangan siap pakai

Salin bagian berikut sebagai instruksi implementasi. Ini spesifikasi pekerjaan selanjutnya, bukan klaim bahwa perubahan sudah dikerjakan pada audit.

---

Anda bertindak sebagai Senior Laravel Engineer, SHE Business Analyst, Data Architect, dan UI/UX Engineer untuk Mandala Management System.

Tujuan: ubah menu SHE Performance, SAP, Risk Register, PICA Management, Document Repository, dan Analytics & Reports menjadi sistem operasional lengkap. Jangan berhenti pada dashboard/menu/chart. Setiap output harus terhubung ke input transaksi yang jelas, tervalidasi, aman, bisa dikoreksi, dan bisa ditelusuri.

### A. Audit awal dan peta sumber

1. Baca SHE_AUDIT_DAN_PROMPT.md dan verifikasi ulang semua temuan terhadap source terbaru.
2. Inventaris setiap menu dari matriks: route, controller/service, model/table, sumber input, action permission, status workflow, output, test dan gap. Jangan menilai “selesai” hanya dari adanya route/view/model.
3. Untuk setiap dashboard, tuliskan data lineage: form/record sumber → validasi → approval → actual aggregation → target → chart → drill-down → export. Tunjukkan missing links sebelum coding.
4. Audit semua jalur input existing: FormEntry/approval, HIPO, CCM, IndicatorDaily, DailyNote, manhours, risk, PICA, dokumen. Reuse fitur existing, jangan duplikasi proses.
5. Pertahankan Laravel, Blade, Tailwind/Alpine dan shared design system. Jangan migrasi framework.

### B. Perbaiki integritas dan otorisasi lebih dahulu

1. Fix department_id vs dept_id dan relasi reportedBy vs reporter. Gunakan satu filter resolver dengan query override dan session site sesuai sistem existing.
2. Setiap GET/POST/PUT/PATCH/DELETE, download, preview, export dan endpoint API yang terkait harus memeriksa izin fitur, aksi, scope site dan objek. Memilih site di UI bukan kontrol keamanan.
3. super_admin: seluruh scope; hanya role ini boleh Manage Users/Input Access. admin_ho: scope seluruh site untuk fitur operasional yang diizinkan, tetap hormati privacy dokumen/kontrak/chat. admin_site: aksi sesuai izin pada assigned sites. user: hanya fitur/aksi dan data yang diizinkan. Jangan menganggap Admin Department sama dengan Admin Site.
4. Izin harus mencakup view/create/update/delete/submit/review/approve/verify/export jika domain membutuhkannya. Menambahkan checkbox fitur tidak boleh memberikan hak approver secara otomatis.
5. Gunakan policy objek dan scope query bersama; jangan mengandalkan nav hidden atau can:is-admin saja. Uji lintas site dengan mengirim id objek langsung.
6. Audit file sensitif pada disk public. Buat akses private/policy-gated sesuai kebutuhan, beserta rencana penanganan file/tautan legacy; jangan memutus link existing tanpa migrasi yang terkontrol.

### C. Lengkapi sumber input

1. Incident Register tunggal: create, detail, update sebelum/selepas submit sesuai status, submit, review/return, investigation, approval dan source history. Klasifikasi mendukung Fatality, LTI, RWC, MTC, FAC, Property Damage, Environmental dan Near Miss. Definisi PAK/KAPTK harus dikonfirmasi dari aturan perusahaan; jangan mengarang definisi atau diagnosis.
2. Input dampak insiden: lost days, item kerusakan/biaya, bukti, periode serta verifikasi. Hindari memasukkan rate hasil hitung sebagai fakta transaksi.
3. Manhours: form manual, koreksi/resubmit, validasi, import sesuai tipe file yang benar, template, preview, mapping site/department/tanggal, error per baris, deduplikasi, transaksi dan rollback. Hapus Site::first(), id 1 dan angka default fiktif. Jangan menerima XLSX jika parser hanya CSV.
4. Inspection: jadwal, checklist versi tertentu, inspector, hasil per pertanyaan, finding/severity, evidence, scoring aktual, submit/review dan PICA. Inspection Achievement harus membedakan inspection biasa dan PTO.
5. PTO: observer, tim/pekerja, tugas, langkah kerja, safe/unsafe observations, deviasi, rekomendasi, evidence, validasi dan PICA.
6. Hazard/HIPO/Near Miss: gunakan alur existing setelah klasifikasi diperjelas; jangan menghitung semua record sebagai setiap jenis sekaligus. Integrasikan ke risk/PICA hanya sesuai aturan domain.
7. Audit/Investigation: form sumber terstruktur, team, temuan/chronology, root cause, rekomendasi, approval dan PICA yang merujuk source record.
8. Risk Assessment: lengkapi create/update/detail/review/revision/retire dengan matrix/version resmi, owner, treatment, due date, inherent/residual scoring dan hubungan critical control.
9. PICA: satu register lintas sumber; source_type/source_id harus valid untuk PICA bersumber. Manual PICA diberi jenis yang jelas, bukan sumber palsu. Lengkapi progress, evidence, submit verification, reject, close, reopen bila didukung. Tutup hanya melalui jalur yang berizin dan simpan history terstruktur. Gunakan nomor unik aman terhadap concurrent request.
10. Document Repository: tentukan integrasi dengan Document/Form/Template existing; jangan membuat repository kembar. Lengkapi upload/metadata, versi/revision, reviewer/approver, submit/return/approve/publish/obsolete dan review due. Semua kategori Policy/SOP/IK/Form memakai register yang sama. Bedakan file form dengan form dinamis.
11. Training: master course/competency, requirement jabatan, jadwal, peserta, hasil, sertifikat, expiry dan evidence. Matrix dihitung dari kewajiban kompetensi, bukan sekadar daftar sertifikat.
12. Environment/Waste: transaksi terstruktur berikut satuan, vendor/manifest/izin yang relevan, evidence dan verifikasi. Definisi metrik mengikuti aturan perusahaan yang tersedia.
13. Semua form wajib punya accessible label, inline validation, helper, field conditional, loading/disabled, pencegahan double submit, error recovery, dirty confirmation jika perlu, master dropdown dan permission-aware actions. Nama/PIC dari user master, site dari assigned-site master. Simpan identitas user secara stabil jika model mendukung; jangan mengandalkan nama saja untuk orang bernama sama.

### D. KPI dan SAP harus data nyata

1. Hapus rand(), hard-coded actual/ranking/score/progress dan fallback target 60/40/100. Jika data/target tidak ada, tampilkan state yang jelas; jangan membuat nilai contoh terlihat seperti produksi.
2. Sediakan kamus KPI: key, nama, unit, numerator, denominator, multiplier, qualifying statuses, period/date column, scope, direction, target precedence dan zero-denominator behavior.
3. Satukan SheKpiTarget/HseKpiTarget melalui service/resolver dan rencana data yang terukur. Jangan menghapus data historis. Target individu/department/site/periode harus punya unique scope, prioritas override dan histori.
4. Target nol valid; bedakan dari target belum ditetapkan. Rasio tanpa denominator tampil N/A, bukan otomatis nol/100%.
5. Fatality/LTI/Non-LTI/PD/Environment/PAK/KAPTK/Near Miss memakai klasifikasi eksplisit. PDFR dan rate lain dihitung dari data yang memenuhi syarat. Nilai multiplier/formula harus mengikuti kebijakan yang telah disepakati; jangan mengubahnya diam-diam.
6. Property Damage Cost, HIPO, near miss, SAP target/actual, ranking, participation dan tren harus mengikuti site/company/department/periode/status yang sama. Tidak boleh ada double count PTO dalam inspection total.
7. Employee dan Department SAP Performance wajib menampilkan target/actual/achievement per jenis aktivitas, periode dan scope. Ranking berasal dari data, bukan urutan database. Tetapkan aturan perpindahan department dan user nonaktif berdasarkan histori.
8. Dashboard, detail dan export harus menghasilkan angka yang bisa direkonsiliasi dari record sumber.

### E. Integrasi Form Builder yang andal

1. Ganti inferensi judul form dengan explicit integration_type dan mapping schema/key/version yang tervalidasi. Mendukung disabled/unmapped state agar form umum tidak otomatis dihitung sebagai inspeksi.
2. Catat source entry id/revision, target domain record id, sync status/error/retry dan audit actor. Jangan memakai field business enum untuk menyimpan sentinel ENTRY-id.
3. Sinkronisasi eligible pada status yang ditetapkan. Tangani perubahan, rejection/revocation dan reapproval sesuai workflow. Idempotent record dan notifikasi pada retry; firstOrCreate saja belum cukup untuk koreksi.
4. Error integrasi tidak boleh hilang di catch sementara approval tampak sukses. Tetapkan transaksi atau outbox/queue dengan status sinkronisasi yang terlihat, retry aman, dan rekonsiliasi.
5. Dukung backfill legacy dengan dry-run, mapping report, dedupe key dan catatan perubahan; jangan otomatis mengubah semua data produksi.

### F. UI dan navigasi

1. Monitoring adalah read view, tetapi harus memiliki CTA yang relevan: Tambah Insiden, Input Manhours, Buat Inspeksi, Input PTO, Laporkan Bahaya, Tambah Risk Assessment, Buat PICA, Upload Dokumen, Tambah Pelatihan atau Catat Limbah sesuai hak pengguna.
2. CTA beberapa menu boleh menuju satu form sumber yang sama dengan preset kategori yang divalidasi backend. Jangan menggandakan transaksi/form per indikator.
3. Gunakan PageHeader, StandardDataTable/shared table, toolbar filters, modal/dialog, form controls, StatusBadge, skeleton, empty/error state, toast dan pagination existing. Default 10; opsi 10/25/50/100. Icon Lucide untuk aksi tabel, tooltip dan label aksesibel.
4. Responsif desktop/tablet/mobile; tidak ada nested horizontal page overflow. Modal besar punya body scroll dan footer mudah diakses. Tidak memakai emoji sebagai icon UI.
5. Tampilkan asal angka, periode, status eligible, filter aktif, last sync dan tautan detail saat membantu pengguna memahami data.

### G. Report dan notification

1. Report Center memakai service perhitungan yang sama, metadata filter, waktu generate, user, format dan history. Tawarkan hanya format yang benar-benar diimplementasikan; jangan menamai CSV sebagai Excel/PDF.
2. Reminder dikirim dari event/scheduler/queue, bukan hanya saat Notification Center dibuka. Deduplikasi penerima/objek/jenis/periode; scope notification mengikuti objek yang boleh diakses penerima.
3. Catat job failures dan retry; mark read hanya untuk pemilik notifikasi. Link tidak boleh melewati policy.

### H. QA wajib dan bukti selesai

1. Buat fixture mandiri dengan RefreshDatabase/DB testing; jangan User::first() di database operasional. Uji schema dan query MySQL yang tidak sama perilakunya dengan SQLite.
2. End-to-end tiap sumber: create → invalid input → submit → return/review → approve/verify → muncul actual KPI/SAP → drill-down → export cocok. Uji koreksi, duplicate submission, race/retry, upload invalid dan rollback.
3. Uji super_admin/admin_ho/admin_site/user, izin fitur/aksi, site A/B, department, id milik site lain, file dan API access.
4. Uji filter pada semua angka: KPI, chart, ranking, badge, table, pagination dan export. Target nol, tanpa target, tanpa data, denominator nol dan periode WIB/UTC.
5. Uji approval bypass, self-approval sesuai kebijakan, status transition invalid, PICA closure tanpa evidence/verifier, revisi dokumen dan tautan file lama.
6. Jalankan test yang relevan, build, pemeriksaan Blade, console/browser dan visual review semua halaman yang diubah. Jangan mengklaim lulus untuk pemeriksaan yang tidak dijalankan.
7. Deliver: audit matrix terbaru, data lineage, migration/backfill plan, daftar perubahan file, hasil test, known gaps dan instruksi deploy termasuk migration, build/assets, cache, storage, scheduler/queue. Pastikan manifest dan assets satu release agar tidak mengulang CSS/JS/font 404.
8. Kerjakan per tahap yang dapat direview. Modul dianggap lengkap hanya jika sumber input, workflow, permission, KPI/linkage, detail dan QA-nya terbukti. Jangan menyatakan seluruh sistem selesai hanya karena dashboard berhasil dibuka.

---

## 7. Sumber utama yang diperiksa

- routes/web.php: group hse/dashboard beserta endpoint manhours, target, SAP, risk, PICA dan documents.
- routes/api.php: HSE dashboard API read endpoints.
- app/Http/Controllers/Hse/SheOperationsController.php: transaksi dan query operasi SHE.
- app/Http/Controllers/Hse/SheDocumentController.php: upload/revision/approval/files.
- app/Http/Controllers/Hse/HseDashboardController.php: dashboard dan export CSV.
- app/Services/Hse/HseDashboardService.php: filter, aggregation, targets, rate dan exposure hours.
- app/Services/Hse/SheFormIntegrationService.php dan Admin/EntryApprovalController.php: integrasi setelah approval.
- app/Models/HseIncident.php, HseInspection.php, HseRiskAssessment.php, HseCorrectiveAction.php, SheManhour.php, SheKpiTarget.php, HseKpiTarget.php, SheSapTarget.php, SheDocument.php, SheNotification.php.
- resources/views/hse/dashboard/* dan resources/views/hse/operations/*: CTA, form, data statis, tabel dan modal.
- app/Support/FeatureAccess.php dan routes/console.php: izin modul dan jadwal repository.
- tests/Feature/SheFeatureIntegrationTest.php: cakupan test yang sudah ditulis, belum dijalankan pada audit ini.
