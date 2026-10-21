# Mandala Production Handoff

Dokumen ini merangkum langkah rilis dan pemeriksaan setelah perubahan UI/UX Phase 1–11.

## Rilis frontend dan Laravel

```bash
npm install
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
sudo systemctl restart apache2
```

Server saat ini menggunakan Apache dengan PHP module. Tidak perlu menjalankan service `php8.3-fpm` apabila service tersebut tidak terpasang.

## Permission runtime

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage -type d -exec chmod 775 {} \;
sudo find storage -type f -exec chmod 664 {} \;
sudo find bootstrap/cache -type d -exec chmod 775 {} \;
sudo find bootstrap/cache -type f -exec chmod 664 {} \;
```

## Konfigurasi wajib

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` menggunakan HTTPS domain production
- `ASSET_URL` menggunakan HTTPS domain production
- Credential database dan NAS hanya disimpan di `.env` server
- Jangan commit `.env` atau credential ke repository

## Pemeriksaan cepat

```bash
curl -I https://mandala.andalan-nusantara.id/login
curl -I https://mandala.andalan-nusantara.id/build/manifest.json
sudo apachectl configtest
sudo tail -n 100 /var/log/apache2/error.log
tail -n 100 storage/logs/laravel.log
```

## Masalah umum

- `ERR_TOO_MANY_REDIRECTS`: jangan menambahkan redirect HTTPS kedua di `public/.htaccess` ketika SSL sudah selesai di proxy/load balancer.
- Mixed content: pastikan `APP_URL`, `ASSET_URL`, dan cache config sudah menggunakan HTTPS.
- `Permission denied` pada log/cache: ulangi permission runtime di atas dan jalankan cache Laravel sebagai `www-data`.
- Asset 404 setelah build: pastikan folder `public/build` dan `public/build/manifest.json` ikut ter-deploy.
- `Class ...ServiceProvider not found`: jalankan `composer install --no-dev --optimize-autoloader` sebelum menjalankan perintah Artisan.

## Checklist acceptance

- Login dan logout berjalan melalui HTTPS.
- Sidebar, navbar, profile dropdown, tabel, modal, toast, dan pagination tampil konsisten.
- Form Wellness publik dapat dibuka tanpa login dan dapat dikirim.
- Upload profile tidak mengarah ke URL HTTP.
- Site scoping dan RBAC tetap membatasi data sesuai akses user.
- Perubahan frontend sudah melewati `npm run build` dan `git diff --check`.
