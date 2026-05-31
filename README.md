# Ruang Baca Informatika

Ruang Baca Informatika adalah aplikasi perpustakaan dan layanan mandiri untuk Program Studi Teknik Informatika Universitas Malikussaleh. Aplikasi ini dirancang untuk mempermudah katalogisasi buku, karya ilmiah, serta proses peminjaman dan pengembalian secara mandiri.

## Fitur Utama

- **Katalog Publik**: Akses pencarian dan detail buku serta karya ilmiah (skripsi, tesis, dan laporan magang).
- **Autentikasi Google**: Registrasi dan masuk dengan akun Google terverifikasi.
- **Layanan Mandiri & Kiosk**: Kiosk untuk kunjungan harian, klaim kartu anggota, serta peminjaman dan pengembalian mandiri berbasis QR.
- **Panel Admin**: Manajemen katalog, riwayat peminjaman, data anggota, dan pengaturan sistem secara komprehensif.

## Stack Teknologi

- **Backend**: Laravel 13 (PHP 8.3)
- **Frontend**: Inertia.js v3, React 19, TypeScript, Tailwind CSS v4
- **Database**: MySQL
- **Admin Panel**: Filament v5
- **Testing**: Pest v4

## Cara Menjalankan

### Prasyarat

Pastikan komputer Anda sudah terinstal:
- PHP >= 8.3
- Composer
- Node.js >= 24
- MySQL

### Setup Awal

Jalankan perintah berikut untuk menginisialisasi proyek secara otomatis (instalasi dependensi backend/frontend, pembuatan `.env`, generate key, dan migrasi database):

```bash
composer setup
```

### Menjalankan Mode Development

Jalankan perintah berikut untuk menjalankan server lokal, antrean (queue worker), dan Vite dev server secara bersamaan:

```bash
composer run dev
```

Atau jalankan secara terpisah jika diperlukan:

```bash
# Backend server
php artisan serve

# Frontend dev server
npm run dev
```

## Testing & Penjaminan Mutu

Untuk memastikan aplikasi berjalan dengan baik, Anda dapat menggunakan beberapa perintah berikut:

- **PHP Test Suite**: `php artisan test --compact`
- **Type Checking Frontend**: `npm run types:check`
- **Linting Frontend**: `npm run lint:check`
- **Formatting PHP**: `vendor/bin/pint`

---
*Catatan: Konfigurasi API pihak ketiga (seperti Google OAuth, WhatsApp API, dan Turnstile) dapat disesuaikan pada file `.env` dengan merujuk pada template `.env.example`.*
