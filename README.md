# 📖 Ruang Baca Informatika

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![React](https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react)](https://react.dev)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-v3-9553E9?style=for-the-badge&logo=inertia)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=for-the-badge&logo=tailwindcss)](https://tailwindcss.com)
[![Filament](https://img.shields.io/badge/Filament-v5-F59E0B?style=for-the-badge&logo=laravel)](https://filamentphp.com)

**Ruang Baca Informatika** adalah aplikasi sistem informasi perpustakaan dan layanan mandiri (self-service kiosk) yang dirancang khusus untuk Program Studi Teknik Informatika, Universitas Malikussaleh. Sistem ini mempermudah pencarian katalog buku, manajemen karya ilmiah (skripsi, tesis, laporan magang), kunjungan harian, serta alur peminjaman dan pengembalian buku secara mandiri dan aman.

---

## 🌟 Fitur Utama

Aplikasi ini dirancang dengan arsitektur modern untuk mendukung kebutuhan akademis harian:

- **🔍 Katalog Publik & Pencarian Pintar**: Modul pencarian cepat dan komprehensif untuk buku cetak dan karya ilmiah (Skripsi, Tesis, Laporan Magang).
- **🔑 Google Authentication & Single Sign-On**: Registrasi dan login aman menggunakan Google Account terverifikasi.
- **📱 Layanan Mandiri (Self-Service Kiosk)**:
  - Pencatatan buku kunjungan harian pengunjung.
  - Klaim dan cetak kartu anggota.
  - Peminjaman dan pengembalian mandiri berbasis pemindaian QR Code.
- **💬 Verifikasi WhatsApp OTP**: Integrasi pengiriman kode OTP melalui WhatsApp (Fonnte API) untuk validasi nomor aktif anggota.
- **🛡️ Panel Admin Filament**:
  - Manajemen katalog terintegrasi (buku, penulis, penerbit).
  - Monitoring riwayat peminjaman aktif, keterlambatan, dan denda.
  - Verifikasi dan persetujuan (approval) pendaftaran anggota baru.
  - Pengaturan hak akses (Filament Shield) untuk petugas dan administrator.

---

## 🛠️ Stack Teknologi

Berikut adalah kombinasi teknologi premium yang digunakan dalam proyek ini:

| Komponen | Teknologi | Keterangan |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 13 | Framework PHP modern untuk backend & routing aman |
| **Bahasa Backend** | PHP 8.3 | Dukungan pengetikan statis, constructor promotion, & efisiensi memori |
| **Frontend Framework** | React 19 + TypeScript | Library UI dinamis dengan pengetikan aman (static typing) |
| **Glue Layer** | Inertia.js v3 | Menghubungkan Laravel & React tanpa kerumitan REST API tradisional |
| **Styling Engine** | Tailwind CSS v4 | Utilitas CSS modern super cepat dengan performa optimal |
| **Admin Dashboard** | Filament v5 | Panel admin berbasis Livewire yang kaya fitur |
| **Database** | MySQL | Penyimpanan data relasional berkinerja tinggi |
| **Testing Suite** | Pest v4 | Framework testing PHP yang ekspresif dan cepat |

---

## 🚦 Aturan Bisnis Utama (Business Rules)

Untuk memastikan kelancaran pengembangan, harap pahami beberapa aturan alur bisnis berikut:

1. **Autentikasi Utama**: Pengguna hanya dapat masuk dan mendaftar menggunakan akun Google. Tidak disediakan login password lokal reguler untuk anggota biasa.
2. **Hak Peminjaman Buku**: Anggota baru yang berhasil login tidak otomatis mendapatkan hak pinjam. Hak pinjam aktif jika memenuhi syarat berikut:
   - Menggunakan email institusi/eligible.
   - Melengkapi profil pada halaman onboarding.
   - Melakukan verifikasi nomor WhatsApp menggunakan OTP.
   - Mendapatkan persetujuan (approval) dari admin/petugas.
   - Memiliki role `member`.
3. **Modus Kiosk**: Layanan Kiosk dirancang untuk perangkat fisik di perpustakaan. Kiosk dilindungi oleh pembatasan Subnet IP (Allowlist) dan PIN default untuk mencegah akses tidak sah.
4. **Alur Transaksi (Draft QR)**: Peminjaman dan pengembalian dilakukan dengan mengajukan draft di aplikasi pengguna, menghasilkan QR Code, lalu dipindai di Kiosk atau dikonfirmasi oleh petugas.

---

## 🚀 Panduan Instalasi & Setup

Ikuti langkah-langkah di bawah ini untuk menyiapkan lingkungan pengembangan lokal Anda:

### 1. Prasyarat Sistem
Pastikan perangkat Anda sudah terinstal:
- **PHP >= 8.3**
- **Composer** (untuk dependensi PHP)
- **Node.js >= 24** & **NPM** (untuk dependensi frontend)
- **MySQL** / MariaDB database server

### 2. Setup Awal Otomatis
Ruang Baca Informatika telah menyediakan script setup untuk mengotomatisasi instalasi:
```bash
composer setup
```
*Script di atas akan melakukan:*
- Instalasi dependensi composer (`composer install`)
- Menyalin `.env.example` menjadi `.env` (jika belum ada)
- Membuat kunci aplikasi baru (`php artisan key:generate`)
- Menjalankan migrasi database (`php artisan migrate --force`)
- Menginstal dependensi NPM (`npm install`)
- Melakukan kompilasi aset frontend awal (`npm run build`)

### 3. Konfigurasi Environment (`.env`)
Buka file `.env` dan lengkapi konfigurasi berikut untuk mengaktifkan fitur eksternal:
```ini
# Database Connection
DB_DATABASE=ruangbaca_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Google OAuth (Wajib untuk login/register)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback

# Fonnte WhatsApp API (Untuk OTP verifikasi WhatsApp)
FONNTE_API_URL=https://api.fonnte.com
FONNTE_API_TOKEN=your_fonnte_token

# Cloudflare Turnstile CAPTCHA (Untuk keamanan form Kiosk)
TURNSTILE_SITE_KEY=your_turnstile_site_key
TURNSTILE_SECRET_KEY=your_turnstile_secret_key

# Super Admin Awal
APP_SUPER_ADMIN_NAME="Administrator Utama"
APP_SUPER_ADMIN_EMAIL="admin-email@gmail.com" # Ganti dengan email Google Anda untuk login awal
```

### 4. Database Seeding & Akun Super Admin
Setelah melengkapi `.env`, jalankan perintah berikut untuk mengisi database dengan data pengujian dan mendaftarkan akun Super Admin:
```bash
php artisan db:seed
```
> [!NOTE]
> Jika environment Anda bernilai `local` atau `development`, seeder secara otomatis akan mengisikan katalog buku contoh, skripsi, tesis, dan penulis fiktif untuk membantu pengujian UI.

---

## 💻 Menjalankan Aplikasi

Aplikasi ini menggunakan Vite dan Laravel Server secara bersamaan. Untuk menjalankan server development lengkap:

```bash
composer run dev
```
*Perintah di atas akan menjalankan secara paralel backend server (`php artisan serve`), queue worker (`php artisan queue:listen`), dan Vite dev server.*

Jika Anda ingin menjalankannya secara terpisah:
```bash
# Server Backend saja
php artisan serve

# Queue Worker saja
php artisan queue:listen

# Frontend Vite Server saja
npm run dev
```

---

## 🧪 Pengujian & Standar Kode

Harap pastikan semua pengujian lulus dan kode terformat dengan baik sebelum melakukan commit perubahan:

- **Menjalankan PHP Test Suite (Pest)**:
  ```bash
  php artisan test --compact
  ```
- **Type Checking Frontend (TypeScript)**:
  ```bash
  npm run types:check
  ```
- **Linting Frontend**:
  ```bash
  npm run lint:check
  ```
- **Formatting PHP (Laravel Pint)**:
  ```bash
  vendor/bin/pint
  ```

---

*Dibuat dengan ❤️ untuk kemajuan akademis Program Studi Teknik Informatika Universitas Malikussaleh.*
