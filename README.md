# Ruang Baca Informatika

Ruang Baca Informatika adalah aplikasi perpustakaan dan layanan mandiri untuk Program Studi Teknik Informatika Universitas Malikussaleh. Repo ini mencakup katalog publik, katalog karya ilmiah, autentikasi Google, verifikasi WhatsApp, peminjaman dan pengembalian berbasis QR, kiosk, serta panel admin Filament.

## Fitur Utama

- Katalog publik buku
- Katalog skripsi, thesis, dan internship report
- Global search
- Login Google dan Google One Tap
- Onboarding profil anggota
- Verifikasi WhatsApp berbasis OTP
- Loan request dan loan history
- Draft QR untuk peminjaman dan pengembalian
- Kiosk kunjungan, registrasi anggota, borrow, dan return
- Panel admin Filament untuk operasional dan pengaturan sistem

## Stack

- PHP 8.3
- Laravel 13
- Inertia.js v3
- React 19 + TypeScript
- Tailwind CSS v4
- Filament v5
- Laravel Fortify
- Laravel Socialite
- Spatie Permission + Filament Shield
- MySQL
- Pest v4
- Vite v8

## Struktur Singkat

- `app/` logic backend Laravel, service, controller, middleware, dan Filament
- `resources/js/` halaman Inertia React, feature modules, dan shared components
- `routes/` route publik, auth, settings, kiosk, dan console
- `tests/` feature dan unit test berbasis Pest
- `public/` asset publik
- `config/` konfigurasi Laravel dan integrasi

## Cara Menjalankan

### Prasyarat

- PHP 8.3
- Composer
- Node.js 24
- MySQL

### Setup Awal

```bash
composer setup
```

Script ini akan:

- install dependency PHP
- membuat `.env` dari `.env.example` bila belum ada
- generate app key
- migrate database
- install dependency frontend
- build asset frontend

### Menjalankan Development Mode

```bash
composer run dev
```

Perintah ini menjalankan:

- `php artisan serve`
- `php artisan queue:listen --tries=1`
- `npm run dev`

Kalau hanya perlu salah satu:

```bash
php artisan serve
npm run dev
```

## Environment Penting

Gunakan `.env.example` sebagai acuan. Beberapa konfigurasi penting:

### Aplikasi dan database

- `APP_URL`
- `DB_*`
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`

### Integrasi Google

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URL`

### Integrasi WhatsApp

- `FONNTE_API_URL`
- `FONNTE_API_TOKEN`
- `FONNTE_SEND_INTERVAL_SECONDS`

### Similarity API

- `SIMILARITY_API_URL`
- `SIMILARITY_API_SECRET`
- `SIMILARITY_API_TIMEOUT`
- `HF_TOKEN`

### Turnstile

- `TURNSTILE_SITE_KEY`
- `TURNSTILE_SECRET_KEY`

## Alur Bisnis Penting

### Autentikasi

- login utama memakai Google
- route `/register` mengarah ke flow Google auth
- user baru dapat diarahkan ke verifikasi WhatsApp atau onboarding profil

### Aktivasi Member

Hak pinjam tidak aktif otomatis setelah login. User harus memenuhi syarat bisnis berikut:

- email eligible
- profil lengkap
- WhatsApp terverifikasi
- approval sesuai aturan bisnis
- role `member`

### Loan Flow

- user menambah buku ke draft pinjam
- sistem membuat QR untuk diproses di kiosk atau petugas
- pengembalian juga memakai return draft QR

### Kiosk Flow

- kiosk dapat dibatasi oleh PIN dan allowlist network
- kiosk mendukung kunjungan, registrasi anggota, borrow, dan return
- registrasi anggota memakai account-link QR dengan masa berlaku terbatas

## Panel Admin

Base path admin:

```text
/admin
```

Resource utama:

- Authors
- Books
- CatalogReports
- Categories
- ContactMessages
- InternshipReports
- Loans
- Publishers
- Roles
- Skripsis
- Theses
- Users
- VisitLogs

Settings utama:

- General Settings
- Library Settings
- Kiosk Settings
- Integration Settings

## Testing dan Quality Checks

### PHP Tests

```bash
php artisan test --compact
```

### Type Check Frontend

```bash
npm run types:check
```

### Lint Frontend

```bash
npm run lint:check
```

### Format PHP

```bash
vendor/bin/pint --dirty --format agent
```

## Dokumentasi Tambahan

- [AGENTS.md](AGENTS.md): instruksi agen dan aturan kerja repo
- [TESTSPRITE_PRODUCT_SPEC.md](TESTSPRITE_PRODUCT_SPEC.md): ringkasan kebutuhan produk untuk pengujian terarah
- `testsprite_tests/tmp/code_summary.yaml`: ringkasan route dan fitur untuk workflow TestSprite lokal

## Catatan

- Jika perubahan frontend tidak muncul, jalankan `npm run build`, `npm run dev`, atau `composer run dev`.
- Integrasi Google, WhatsApp, dan Similarity API membutuhkan kredensial aktif agar flow end-to-end bisa diuji penuh.
- Queue worker perlu berjalan untuk beberapa notifikasi dan flow asynchronous.
