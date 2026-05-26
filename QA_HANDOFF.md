# QA Handoff - Ruang Baca Informatika

## 1. Ringkasan Project

Ruang Baca Informatika adalah sistem pengelolaan koleksi, kunjungan, layanan peminjaman mandiri, dan panel administrasi untuk perpustakaan/ruang baca.

Fitur utama yang ada saat ini:

- Katalog publik buku
- Katalog karya ilmiah: skripsi, thesis, dan internship report
- Pencarian global
- Login berbasis Google
- Onboarding profil pengguna
- Verifikasi WhatsApp
- Pengajuan peminjaman buku oleh member
- Riwayat pinjam dan pengembalian
- Kiosk layanan mandiri untuk kunjungan, registrasi anggota, peminjaman, dan pengembalian
- Similarity checker untuk judul skripsi
- Panel admin Filament untuk operasional perpustakaan

Base URL lokal saat ini: `http://localhost:8000`

## 2. Stack Teknis

- Backend: Laravel 13, PHP 8.3
- Frontend: Inertia.js v3 + React 19 + TypeScript
- Styling: Tailwind CSS v4
- Admin panel: Filament v5
- Auth: Laravel Fortify + Laravel Socialite
- Permission/role: Filament Shield + Spatie Permission
- Database: MySQL
- Test: Pest v4
- Build tool: Vite v8

## 3. Cara Menjalankan Project

Prasyarat utama:

- PHP 8.3
- Composer
- Node.js 24
- MySQL

Perintah setup dasar:

```bash
composer setup
php artisan db:seed --no-interaction
composer run dev
```

Catatan:

- `composer setup` akan install dependency, membuat `.env`, generate app key, migrate database, install npm package, lalu build frontend.
- `composer run dev` menjalankan Laravel server, queue listener, dan Vite sekaligus.
- Session, cache, dan queue menggunakan database.
- Timezone aplikasi di config adalah `UTC`, jadi pastikan QA aware saat membaca timestamp.

## 4. Konfigurasi Environment Penting

File referensi: `.env.example`

Konfigurasi inti:

- `APP_URL=http://localhost:8000`
- `DB_*` untuk koneksi MySQL
- `QUEUE_CONNECTION=database`
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`

Integrasi eksternal:

- Google OAuth:
  - `GOOGLE_CLIENT_ID`
  - `GOOGLE_CLIENT_SECRET`
  - `GOOGLE_REDIRECT_URL`
- WhatsApp gateway:
  - `FONNTE_API_URL`
  - `FONNTE_API_TOKEN`
  - `FONNTE_SEND_INTERVAL_SECONDS`
- Similarity API:
  - `SIMILARITY_API_URL`
  - `SIMILARITY_API_SECRET`
  - `SIMILARITY_API_TIMEOUT`
  - `HF_TOKEN`
- Cloudflare Turnstile:
  - `TURNSTILE_SITE_KEY`
  - `TURNSTILE_SECRET_KEY`

Implikasi untuk QA:

- Jika Google OAuth belum diisi, flow login tidak bisa dipakai normal.
- Jika WhatsApp gateway belum diisi, verifikasi OTP dan notifikasi WhatsApp berpotensi gagal.
- Jika Similarity API belum tersedia, fitur similarity bisa menampilkan error `503` atau `500`.
- Turnstile bersifat opsional dan dikontrol dari admin settings.

## 5. Seed Data dan Akun

Seeder utama:

- `RoleSeeder`
- `AppSettingSeeder`
- `ShieldSeeder`
- `SuperAdminSeeder`

Seeder data contoh yang hanya jalan di environment `local` atau `development`:

- `CategorySeeder`
- `AuthorSeeder`
- `PublisherSeeder`
- `BookSeeder`
- `SkripsiSeeder`
- `ThesisSeeder`
- `InternshipReportSeeder`

Role yang tersedia:

- `super_admin`
- `panel_user`
- `staff`
- `member`

Akun admin seed:

- Dibuat dari config `app.super_admin`
- Default email: `admin@example.com`
- Tidak ada password lokal
- Login aplikasi memakai Google, jadi akun admin efektif jika email Google yang dipakai sama dengan email seeded admin

Catatan penting:

- Member tidak otomatis bisa meminjam hanya karena berhasil login.
- Hak pinjam aktif jika user:
  - memakai email kampus yang eligible,
  - WhatsApp sudah terverifikasi,
  - disetujui admin,
  - mendapat role `member`.

## 6. Gambaran Modul dan Route

Ringkasan jumlah route non-vendor:

- `public`: 17 route
- `member-services`: 11 route
- `auth-settings`: 15 route
- `kiosk`: 12 route
- `admin`: 46 route

### 6.1 Public Pages

Route utama:

- `/`
- `/books`
- `/books/{slug}`
- `/skripsi`
- `/skripsi/{student_id}`
- `/thesis`
- `/thesis/{student_id}`
- `/internship-reports`
- `/internship-reports/{student_id}`
- `/search`
- `/about`
- `/about/team`
- `/contact`
- `/privacy-policy`
- `/terms-of-service`

Yang perlu dicek QA:

- Halaman publik bisa dibuka tanpa login
- Pagination dan filter katalog bekerja
- Detail resource tampil benar
- Form contact tersimpan
- Pelaporan katalog (`/catalog-reports`) tersimpan

### 6.2 Auth dan Onboarding

Route penting:

- `/register`
- `/auth/google`
- `/auth/google/callback`
- `/auth/google/one-tap`
- `/register/profile`
- `/register/whatsapp`
- `/account-link/{token}`
- `/logout`

Perilaku utama:

- Route `register` langsung redirect ke Google auth
- Login default mengandalkan Google, bukan form email/password lokal
- Setelah login, user diarahkan sesuai status:
  - ke verifikasi WhatsApp,
  - ke onboarding profil,
  - ke admin dashboard jika admin,
  - atau ke home jika user publik biasa

### 6.3 Member Services

Route utama:

- `/similarity`
- `/similarity/check`
- `/notifications`
- `/loans/request`
- `/loans/request/books`
- `/loans/request/qr`
- `/loans/history`
- `/loans/history/qr`

Perilaku utama:

- Similarity check butuh login
- Loan request butuh login
- Loan history butuh login dan profil lengkap
- Notifikasi bisa dibaca satuan atau mark all as read

### 6.4 Kiosk

Route utama:

- `/kiosk`
- `/kiosk/pin`
- `/kiosk/visits`
- `/kiosk/members`
- `/kiosk/members/status`
- `/kiosk/members/find`
- `/kiosk/members/cancel`
- `/kiosk/books/search`
- `/kiosk/loans/borrow`
- `/kiosk/loans/return`
- `/kiosk/loans/drafts/consume`
- `/kiosk/loans/return-drafts/consume`

Perilaku utama:

- Kiosk bisa dibatasi berdasarkan IP/subnet allowlist
- Kiosk harus di-unlock pakai PIN
- Session device kiosk bisa direset dari admin
- Kiosk mendukung:
  - pencatatan kunjungan,
  - registrasi anggota via QR account linking,
  - pencarian buku,
  - peminjaman,
  - pengembalian

### 6.5 Admin Panel

Base path admin: `/admin`

Resource admin yang tersedia:

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

Widget dashboard:

- Operations Overview
- Pending Member Approvals
- Similarity Sync Overview
- Loan Activity Chart
- Today Visitors
- Contact Messages Table
- Overdue Loan Table

## 7. Pengaturan Admin yang Perlu Diketahui QA

Menu pengaturan di admin:

- Umum
- Aturan Sirkulasi
- Kios
- Integrasi API

Yang bisa diubah dari admin:

- Nama situs, tagline, WhatsApp bantuan
- Maksimal buku dipinjam
- Durasi pinjam
- Suspensi keterlambatan dan cooldown
- PIN kiosk
- Allowlist IP/subnet kiosk
- Judul dan subtitle kiosk
- Endpoint dan secret Similarity API
- Top-K, threshold, dan bobot similarity
- Endpoint dan token WhatsApp API
- Aktivasi Turnstile

Catatan khusus:

- Mengubah PIN kiosk akan mereset semua sesi perangkat kiosk.
- Mengubah bobot similarity seharusnya diikuti full sync skripsi.

## 8. Struktur Data Bisnis

Entitas domain utama:

- `users`
- `roles`, `permissions`
- `books`, `book_items`, `authors`, `publishers`, `categories`
- `skripsis`, `theses`, `internship_reports`
- `loans`, `loan_items`
- `loan_drafts`, `loan_draft_items`
- `return_drafts`, `return_draft_items`
- `visit_logs`
- `kiosk_devices`
- `member_registration_claims`
- `contact_messages`
- `catalog_reports`
- `similarity_sync_statuses`
- `settings`
- `notifications`

Poin penting untuk QA:

- Buku bisa punya banyak copy fisik melalui `book_items`
- Status copy fisik: `available`, `borrowed`, `maintenance`, `reserved`
- Kondisi copy fisik: `good`, `damaged`, `lost`
- Peminjaman dan pengembalian memakai draft + QR flow
- Registrasi anggota kiosk menyimpan claim sementara dengan masa berlaku 5 menit

## 9. Alur Bisnis Penting

### 9.1 Login dan Aktivasi Member

Urutan normal:

1. User login via Google
2. Jika perlu, user verifikasi WhatsApp
3. Jika profil belum lengkap, user isi profil
4. Jika butuh approval manual, admin menyetujui user
5. Setelah memenuhi syarat, user bisa meminjam

Kondisi yang memengaruhi hasil:

- Email kampus eligible lebih diprioritaskan untuk akses member
- Email non-kampus tetap bisa login, tapi belum tentu bisa meminjam
- Admin/staff diarahkan ke panel admin

### 9.2 Peminjaman Buku oleh Member

Prasyarat:

- Sudah login
- Profil lengkap
- Punya akses pinjam
- Ada buku yang `is_borrowable`
- Tersedia `book_items` berstatus `available`

Flow:

1. User buka `/loans/request`
2. Menambah buku ke draft
3. Generate QR
4. QR dikonsumsi di kiosk/admin flow untuk membuat transaksi pinjam

### 9.3 Pengembalian Buku

Flow:

1. User buka `/loans/history`
2. Pilih item yang akan dikembalikan
3. Generate QR return draft
4. QR dikonsumsi pada kiosk

### 9.4 Registrasi Member via Kiosk

Flow:

1. Petugas/pengunjung masuk ke kiosk dan unlock PIN
2. Isi data registrasi
3. Sistem membuat QR account-link
4. User scan dengan ponsel
5. User login Google
6. Akun ditautkan ke data registrasi kiosk
7. Jika perlu, lanjut verifikasi WhatsApp dan approval admin

### 9.5 Similarity Checker

Flow:

1. User login
2. Buka `/similarity`
3. Input judul minimal 5 kata
4. Sistem cek cache, lalu panggil Similarity API
5. Hasil dikaitkan ke data skripsi lokal jika ID cocok

Kemungkinan hasil:

- `422` jika judul terlalu singkat
- `503` jika layanan similarity tidak sehat/sleep
- `500` jika request gagal walau service dianggap sehat

## 10. Area Risiko Tinggi untuk QA

Prioritas pengujian tinggi:

- Redirect auth berdasarkan status user
- Google login dan Google One Tap
- Verifikasi WhatsApp
- Approval member
- Validasi pembatasan pinjam
- Draft QR untuk pinjam dan return
- Kiosk network allowlist
- Kiosk PIN session
- Similarity API failure handling
- Contact form dan catalog report submission
- Akses admin vs non-admin
- Sinkronisasi settings ke perilaku runtime

Potensi edge case:

- User punya email valid tapi belum approve
- User sudah punya WhatsApp tapi belum verified
- User admin mencoba masuk dari One Tap
- Kiosk device token dipakai dari network scope berbeda
- Similarity API down
- WhatsApp gateway gagal kirim
- Borrow request saat stok fisik habis
- User sedang terkena suspensi keterlambatan

## 11. Checklist QA Manual yang Disarankan

### 11.1 Smoke Test Publik

- Buka home page
- Buka katalog buku dan gunakan filter
- Buka detail buku
- Buka katalog skripsi, thesis, internship report
- Jalankan global search
- Kirim contact form
- Kirim report katalog

### 11.2 Smoke Test Auth

- Login dengan Google
- Cek redirect user baru
- Cek redirect admin
- Cek onboarding profil
- Cek verifikasi WhatsApp
- Cek logout

### 11.3 Smoke Test Member

- Tambah buku ke loan draft
- Hapus buku dari draft
- Generate QR pinjam
- Lihat history pinjam
- Generate QR return
- Buka notification center
- Jalankan similarity check

### 11.4 Smoke Test Kiosk

- Akses kiosk dari jaringan yang diizinkan
- Verifikasi PIN
- Simpan visit log
- Registrasi member baru
- Poll status member registration
- Cari member
- Cari buku
- Proses borrow
- Proses return
- Reset session device dari admin lalu pastikan PIN diminta lagi

### 11.5 Smoke Test Admin

- Login sebagai admin
- Buka dashboard
- Buka setiap resource admin minimal index page
- Uji create/edit untuk resource inti jika data dummy tersedia
- Ubah setting umum
- Ubah setting library
- Ubah setting kiosk
- Ubah setting integration
- Pastikan role/permission membatasi akses dengan benar

## 12. Cakupan Test Otomatis yang Sudah Ada

Total file test feature yang teridentifikasi:

- Root `tests/Feature`: 34 file
- `tests/Feature/Auth`: 1 file
- `tests/Feature/Filament`: 2 file
- `tests/Feature/Kiosk`: 4 file
- `tests/Feature/Settings`: 2 file

Topik yang sudah punya coverage:

- Auth Google
- WhatsApp verification
- WhatsApp gateway dan rate limit
- Catalog dan book flow
- Thesis, skripsi, internship report
- Similarity controller dan dispatcher
- Loan request dan loan history
- Return draft
- Kiosk access, borrow, dan consume draft
- Contact message
- Notification center
- Member registration claim
- Admin panel
- Restricted borrower filters
- Integration settings
- Security dan profile update

Nama file test yang relevan untuk referensi QA:

- `GoogleAuthenticationTest`
- `WhatsAppVerificationTest`
- `LoanRequestTest`
- `LoanHistoryTest`
- `ReturnDraftTest`
- `BorrowBookTest`
- `KioskAccessTest`
- `ConsumeLoanDraftTest`
- `ConsumeReturnDraftTest`
- `SimilarityControllerTest`
- `AdminPanelTest`
- `RestrictedBorrowerFiltersTest`

## 13. Hal yang Perlu Dikonfirmasi Sebelum QA Mulai

Checklist operasional:

- Apakah kredensial Google OAuth sudah tersedia?
- Apakah Similarity API memang aktif dan bisa diakses?
- Apakah WhatsApp gateway disiapkan untuk environment QA?
- Apakah data seed contoh sudah dimasukkan?
- Apakah email admin QA sudah sama dengan `APP_SUPER_ADMIN_EMAIL`?
- Apakah network QA termasuk dalam allowlist kiosk jika fitur kiosk ingin dites?
- Apakah queue listener sedang berjalan?
- Apakah frontend asset sudah ter-build atau Vite dev server sedang aktif?

## 14. Saran Eksekusi QA

Urutan yang paling efisien:

1. Validasi setup environment dan login admin
2. Cek pengaturan admin dan seed data
3. Uji modul publik
4. Uji auth dan onboarding
5. Uji member services
6. Uji kiosk
7. Uji integrasi eksternal dan failure handling
8. Cocokkan hasil dengan test automation yang sudah ada

Jika diperlukan, dokumen ini bisa dijadikan dasar untuk membuat test case formal per modul.
