# Akses fitur per pengguna

Super Admin mengatur daftar fitur di halaman Site, Role & Fitur Pengguna. Role, policy objek, department dan site tetap berlaku; daftar fitur hanya membatasi, tidak menaikkan izin.

## Role akun dan multi-site

- `admin_ho`: akses seluruh site dan fitur; mengelola penugasan. Akun `super_admin` lama tetap didukung. Kontrak Admin HO mengikuti pemilik/viewer, chat pribadi hanya dapat diakses peserta.
- `admin_site`: administrator operasional dengan cakupan site terpilih. Untuk dokumen Mandala tetap membutuhkan keanggotaan department dan akses site. Tidak boleh mengubah penugasan akun lain atau naik menjadi Admin HO.
- `user`: tidak mendapat status admin hanya karena keanggotaan/peran department. Akses mengikuti site, fitur dan policy objek.

Form baru mengirim `site_ids[]` dan `site_selection=replace`: daftar site mengganti penugasan sebelumnya. Admin Site/User memerlukan setidaknya satu site. Admin HO tidak memerlukan site. Endpoint tetap menerima `site_id` lama dengan perilaku tambah tanpa mencabut penugasan lain. Department yang dipilih ditambahkan sebagai keanggotaan; department lain tidak dihapus. Peran department lama tetap tersedia untuk izin department, bukan pengganti role akun.

Migrasi `2026_10_22_000003_add_explicit_admin_roles.php` memperluas kolom role tanpa menghapus akun/penugasan. Akun `admin` lama dan akun `user` dengan pivot `dept_admin` menjadi `admin_site` untuk mempertahankan akses operasional. Tidak menambahkan site atau role Admin HO secara otomatis. Rollback tidak menurunkan role diam-diam.

Master yang tidak memiliki relasi site (misalnya katalog indikator) tetap mengikuti policy modul bersama; multi-site tidak menciptakan partisi site pada tabel yang tidak memilikinya. Data operasional yang memiliki cakupan site tetap menggunakan filter/authorization site yang ada.

- `feature_mode=keep`: tidak mengubah konfigurasi pengguna.
- `feature_mode=custom`: hanya module key yang ada di `feature_access[]` yang diizinkan. Daftar kosong menolak seluruh modul terdaftar; profil dan logout tetap tersedia.
- `feature_mode=default`: kembalikan `feature_access` menjadi NULL dan ikuti izin role/policy lama.
- Super Admin tetap tidak dibatasi daftar fitur. Mode custom untuk target Super Admin ditolak saat penyimpanan.

Konfigurasi disimpan pada pengguna, sehingga berlaku pada seluruh site yang boleh ia akses. Penugasan site, perubahan role dan daftar fitur disimpan dalam transaksi yang sama.

Registry `App\Support\FeatureAccess` mencakup halaman admin/front/user dan API operasional berbasis sesi di web routes. Middleware menolak permintaan ke modul yang tidak diizinkan untuk seluruh HTTP method. Menu sidebar memakai pemeriksaan registry yang sama. Tambahkan route baru ke registry jika memperkenalkan modul baru.

Endpoint publik (termasuk wellness publik dan berkas publik) serta API integrasi dengan kredensial service terpisah tidak diubah menjadi izin pengguna. Daftar fitur bukan pengganti ACL file atau autentikasi API integrasi.

## Deployment

Setelah kode diunggah, jalankan migrasi berikut sebelum membuka halaman penugasan:

```sh
php artisan migrate --path=database/migrations/2026_10_22_000002_add_feature_access_to_users.php --force
php artisan migrate --path=database/migrations/2026_10_22_000003_add_explicit_admin_roles.php --force
php artisan view:clear
```

NULL pada pengguna lama mempertahankan perilaku sebelumnya. Pengujian menggunakan SQLite terisolasi: penolakan GET/POST langsung, akses akun saat daftar kosong, penyembunyian menu, larangan kenaikan hak admin melalui fitur, assignment/reset/validasi daftar, serta kompatibilitas pengguna lama dan Super Admin. Tidak ada perubahan akun atau database produksi dalam pengujian.
