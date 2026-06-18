# Product

## Register

brand

## Users
Pengguna campuran yang datang ke halaman welcome Ruang Baca Informatika: mahasiswa Teknik Informatika, dosen, calon anggota, dan pengunjung umum yang ingin melihat katalog, karya ilmiah, serta layanan ruang baca dengan cepat dan jelas.

## Product Purpose
Halaman welcome memperkenalkan Ruang Baca Informatika sebagai layanan perpustakaan kampus yang modern, tertata, dan mudah dipakai. Halaman ini harus membantu pengunjung memahami nilai layanan, menelusuri katalog, dan percaya bahwa proses mencari referensi atau meminjam buku terasa sederhana.

## Brand Personality
Clean, calm, premium, structured. Suasananya rapi, tenang, kokoh, dan terstruktur dengan garis pembatas tegas (grid-line based) tanpa terasa seperti landing page startup yang agresif, ramai, atau glossy.

## Anti-references
- Hindari gaya startup generik yang penuh gradient, glow, dan kartu metrik berlebihan.
- Hindari halaman yang terlalu ramai, terlalu banyak aksen visual, terlalu glossy, atau terlalu sibuk hingga mengalihkan perhatian dari isi katalog dan layanan.
- Hindari pemakaian efek blur atau kaca buram (*backdrop blur*) pada header atau panel utama.
- Hindari pemakaian efek perbesaran (*hover scale* / `hover:scale-105`) pada ikon-ikon navigasi, tombol, dan kartu.
- Hindari komponen melayang dengan sudut membulat berlebihan (*floating rounded components*) yang memutus kontinuitas garis vertikal layout.

## Design Principles
- **Kejelasan dan Struktur**: Kejelasan lebih penting daripada efek visual. Kesan terstruktur dibangun lewat pembatas garis grid yang tegas dan konsisten.
- **Grid-Line Layout**: Struktur halaman dipandu oleh garis vertikal (batas kontainer `max-w-7xl`) dan garis horizontal yang tegas.
- **Geometri Arsir**: Jarak senggang atau jeda antar-section diisi dengan pola geometri diarsir (*hatched pattern* menggunakan `repeating-linear-gradient`) untuk memberikan ritme tanpa warna latar belakang yang mengalihkan perhatian.
- **Desain Datar (*Flat Components*)**: Pertahankan elemen kartu, spotlight, dan daftar buku tetap datar (*flat*) tanpa *shadow* atau garis pembatas ganda yang redundan di dalamnya.
- **Aksesibilitas Kontras Tinggi**: Warna latar belakang harus solid (`bg-background`) tanpa efek transparansi tinggi atau blur agar kontras teks selalu prima.
- **Tipografi dan Ritme**: Kesan premium dibangun lewat tipografi, ritme, dan pemanfaatan ruang kosong yang presisi.

## Accessibility & Inclusion
Gunakan kontras yang jelas, tipografi yang mudah dibaca, dan motion yang ringan. Hormati preferensi reduced motion dan hindari efek blur atau glow berat yang mengurangi keterbacaan. Garis pembatas grid membantu pengguna memahami struktur konten dengan lebih mudah.
