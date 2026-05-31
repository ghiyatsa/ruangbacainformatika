# TestSprite Product Specification - Ruang Baca Informatika

## 1. Ringkasan Produk

Ruang Baca Informatika adalah aplikasi layanan perpustakaan dan katalog akademik berbasis web untuk Program Studi Teknik Informatika Universitas Malikussaleh. Aplikasi ini menggabungkan katalog publik, autentikasi Google, onboarding anggota, verifikasi WhatsApp, layanan peminjaman mandiri, kiosk, serta panel admin Filament untuk operasional harian.

Dokumen ini disusun sebagai acuan lokal untuk pengujian terarah, khususnya saat membuat test plan browser atau end-to-end.

## 2. Tujuan Produk

- Menyediakan akses publik ke katalog buku dan karya ilmiah.
- Memudahkan anggota kampus untuk login dan mengaktifkan layanan peminjaman.
- Mendukung alur peminjaman dan pengembalian berbasis draft QR.
- Menyediakan kiosk mandiri untuk kunjungan, registrasi anggota, peminjaman, dan pengembalian.
- Memberi admin panel operasional untuk mengelola koleksi, pengguna, kunjungan, dan pengaturan sistem.

## 3. Persona Utama

- Pengunjung publik: melihat katalog, detail koleksi, dan halaman informasi.
- Mahasiswa: login dengan Google, melengkapi profil, verifikasi WhatsApp, lalu meminjam buku.
- Dosen atau staf kampus: dapat login, tetapi akses pinjam bergantung approval dan aturan bisnis.
- Petugas kiosk: memakai perangkat yang diizinkan untuk layanan mandiri.
- Admin perpustakaan: mengelola katalog, approval member, loan activity, settings, dan monitoring.

## 4. Modul Produk

### 4.1 Publik

- Home page
- Katalog buku
- Detail buku
- Katalog skripsi
- Katalog thesis
- Katalog internship report
- Detail masing-masing karya ilmiah
- Global search
- About, About Team, Contact, Privacy Policy, Terms of Service
- Pelaporan katalog dan contact form

### 4.2 Autentikasi dan Onboarding

- Login Google redirect
- Google One Tap
- Onboarding profil
- Verifikasi WhatsApp
- Redirect berbasis status user
- Logout

### 4.3 Layanan Anggota

- Similarity checker
- Loan request
- Draft buku pinjam
- Generate QR pinjam
- Loan history
- Generate QR return
- Notification center

### 4.4 Kiosk

- Unlock perangkat dengan PIN
- Pencatatan kunjungan
- Registrasi anggota via QR account-link
- Pencarian member
- Pencarian buku
- Borrow consume flow
- Return consume flow

### 4.5 Admin Filament

- Dashboard operasional
- CRUD koleksi dan taxonomy
- CRUD karya ilmiah
- Monitoring contact message dan catalog report
- Approval anggota
- Visit logs
- Settings umum, sirkulasi, kiosk, integrasi
- Role dan permission

## 5. Aturan Bisnis Kritis

- Login utama menggunakan Google, bukan password lokal.
- User kampus dapat login, tetapi akses pinjam tidak otomatis aktif.
- Akses pinjam aktif jika user memenuhi semua syarat:
- Email eligible.
- Profil lengkap.
- WhatsApp terverifikasi.
- Approval sesuai aturan bisnis.
- Role `member` aktif.
- User admin atau staff boleh masuk panel admin, tetapi bukan berarti berhak meminjam.
- Draft QR pinjam dan return adalah gerbang transaksi layanan mandiri.
- Registrasi anggota kiosk menghasilkan account-link token dengan masa aktif terbatas.
- Kiosk dapat dibatasi berdasarkan IP atau subnet allowlist.

## 6. Acceptance Criteria per Alur Utama

### 6.1 Publik

- Semua halaman publik dapat dibuka tanpa login.
- Katalog menampilkan data, filter, dan pagination dengan benar.
- Detail koleksi menampilkan metadata inti dengan layout stabil di mobile dan desktop.
- Contact form dan catalog report dapat dikirim dengan validasi yang jelas.
- Meta SEO publik hadir pada halaman utama dan detail katalog.

### 6.2 Login Google

- Route register mengarah ke login Google.
- Google callback membuat atau memperbarui akun sesuai email.
- Foto profil Google disimpan sebagai avatar bila tersedia.
- Setelah login, user diarahkan sesuai status:
- Ke verifikasi WhatsApp bila belum verifikasi.
- Ke onboarding profil bila data belum lengkap.
- Ke dashboard admin bila admin siap masuk panel.
- Ke home bila user publik biasa.

### 6.3 Verifikasi WhatsApp

- User kampus yang wajib verifikasi diarahkan ke halaman verifikasi.
- OTP dapat dikirim dengan cooldown dan limit yang sesuai.
- OTP valid menyimpan `whatsapp_verified_at`.
- User yang masih butuh approval tetap belum bisa meminjam setelah verifikasi.

### 6.4 Onboarding Profil

- User dapat melengkapi nama, WhatsApp, dan alamat.
- Email tidak dapat diubah dari UI.
- Jika nomor WhatsApp yang sudah terverifikasi diubah, verifikasi ulang diperlukan.
- Avatar pada header dan halaman profile memakai avatar Google bila tersedia, lalu fallback ke inisial.

### 6.5 Loan Request

- User yang belum punya hak pinjam tidak dapat melanjutkan flow pinjam.
- User yang berhak dapat menambah dan menghapus buku dari draft.
- QR pinjam hanya dibuat untuk draft valid.
- Buku yang stok fisiknya habis tidak boleh lolos ke transaksi pinjam final.

### 6.6 Loan History dan Return

- User dapat melihat histori pinjam miliknya sendiri.
- QR return hanya dibuat untuk item pinjam yang memang dapat dikembalikan.
- Return draft dikonsumsi melalui flow kiosk atau petugas.

### 6.7 Kiosk

- Perangkat non-allowlist ditolak jika pembatasan jaringan aktif.
- PIN wajib untuk membuka kiosk yang terkunci.
- Registrasi anggota membuat QR account-link aktif.
- Status registrasi bisa dipolling.
- Borrow dan return consume flow hanya menerima draft valid.

### 6.8 Admin

- Hanya user admin atau staff yang dapat masuk panel admin.
- Resource penting terbuka dan dapat dimuat tanpa error.
- Approval anggota memengaruhi akses pinjam user.
- Perubahan settings memengaruhi perilaku runtime yang terkait.

## 7. Area Risiko Tinggi

- Redirect auth berdasarkan kombinasi status user.
- Google login dan Google One Tap.
- WhatsApp OTP cooldown, limit, dan verifikasi.
- Approval anggota dan dampaknya ke akses pinjam.
- Draft QR pinjam dan return.
- Sinkronisasi data registrasi kiosk ke akun Google.
- Kiosk allowlist network dan PIN session.
- Similarity API error handling.
- Header, menu user, dan halaman settings yang memakai avatar user.
- Security headers dan CSP terhadap integrasi Google serta asset frontend.

## 8. Prioritas Testing

### 8.1 Prioritas Tinggi

- Publik katalog dan detail
- Login Google
- Verifikasi WhatsApp
- Onboarding profil
- Loan request
- Loan history
- Kiosk registration
- Kiosk borrow and return
- Admin dashboard
- Approval member

### 8.2 Prioritas Menengah

- Similarity checker
- Notifications
- Contact form
- Catalog report
- Static content pages
- Settings integrations

### 8.3 Prioritas Rendah

- Cosmetic UI variation yang tidak memengaruhi bisnis
- Edge animation atau visual polish non-kritis

## 9. Data dan Dependensi Penting

- Google OAuth credentials
- WhatsApp gateway credentials
- Similarity API endpoint dan secret
- Queue worker aktif
- Session, cache, dan queue database berjalan baik
- Build frontend tersedia atau Vite dev server aktif
- Seed role, admin, settings, dan data katalog tersedia

## 10. Skenario Uji yang Disarankan

### 10.1 Smoke Publik

- Buka home
- Buka katalog buku
- Buka detail buku
- Buka detail skripsi atau thesis
- Jalankan global search
- Kirim contact form

### 10.2 Smoke Auth

- Login Google user baru
- Login Google user admin
- Login Google user kampus yang belum verifikasi WhatsApp
- Login Google user yang belum lengkap profil
- Logout

### 10.3 Smoke Member

- Tambah buku ke draft pinjam
- Hapus buku dari draft
- Generate QR pinjam
- Buka loan history
- Generate QR return
- Cek notification center

### 10.4 Smoke Kiosk

- Unlock PIN
- Catat kunjungan
- Registrasi anggota baru
- Poll status account-link
- Cari buku
- Borrow consume
- Return consume

### 10.5 Smoke Admin

- Login admin
- Buka dashboard
- Buka resource users
- Buka resource books
- Buka resource contact messages
- Buka resource catalog reports
- Approval user
- Ubah setting penting

## 11. Hasil yang Diharapkan dari Test Plan

Test plan yang dibangun dari dokumen ini sebaiknya:

- Memisahkan skenario publik, member, kiosk, dan admin.
- Memprioritaskan alur bisnis bernilai tinggi sebelum edge case minor.
- Menguji guard, redirect, dan pembatasan akses.
- Memasukkan validasi error state untuk integrasi eksternal.
- Memastikan mobile dan desktop tetap stabil pada halaman publik dan settings.

## 12. Catatan untuk Eksekusi TestSprite

- Type yang direkomendasikan: `frontend`
- Scope yang direkomendasikan: `codebase`
- Mode yang direkomendasikan: `production`
- Target lokal default: `http://127.0.0.1:8000`

Jika diperlukan, dokumen ini dapat dipakai sebagai dasar untuk menyusun test case formal atau checklist QA lanjutan.
