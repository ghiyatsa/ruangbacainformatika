# Panduan Desain UI/UX - Ruang Baca Informatika

## Kepribadian Brand (Brand Personality)
* **Clean, Calm, Premium, Structured**: Suasana antarmuka yang rapi, tenang, kokoh, dan terstruktur dengan garis pembatas tegas (grid-line based). Menghindari gaya visual yang agresif, terlalu ramai, mengkilap (glossy), atau berlebihan.

---

## Prinsip Desain Antarmuka (UI Principles)

### 1. Sistem Tata Letak Garis Kisi (Grid-Line Layout)
* **Kontinuitas Pembatas Tegas**: Struktur halaman dipandu oleh garis vertikal (batas kontainer utama `max-w-7xl` menggunakan `border-x border-border/60`) dan garis horizontal (`border-y border-border/60`) yang tegas dan konsisten.
* **Pola Geometri Arsir (Hatched Pattern)**: Setiap jeda atau jarak senggang antar-section menggunakan arsir geometri (`repeating-linear-gradient`) setinggi `h-6` atau `h-8` untuk memberikan ritme visual yang konsisten tanpa warna latar belakang yang mengalihkan perhatian.
* **Margin Negatif untuk Elemen Full-Width**: Elemen dekoratif seperti garis arsir pemisah harus melebar penuh melampaui padding container utama dengan menerapkan margin negatif (`-mx-4 sm:-mx-6 lg:-mx-8`).

### 2. Desain Komponen Datar (Flat Design)
* **Bebas Bayangan (No Shadows)**: Semua elemen kartu (Card), spotlight, daftar koleksi, tombol, dan kotak dialog harus tetap datar tanpa bayangan (terapkan `shadow-none`).
* **Satu Garis Pembatas (Single Border)**: Hindari garis pembatas ganda (*double borders*) yang redundan di dalam satu kompartemen.
* **Kelurusan Vertikal (Layout Alignment)**: Menghindari pemakaian komponen melayang dengan sudut membulat berlebihan (*excessive rounded floating components*) yang memutus kelurusan vertikal garis batas layout. Batas kebulatan sudut standar adalah `rounded-xl` atau `rounded-2xl` yang menyatu dengan garis kisi.

### 3. Tipografi & Aksesibilitas Kontras Tinggi
* **Keterbacaan & Rasio Kontras (WCAG AA)**: Teks harus memiliki rasio kontras minimal `4.5:1` terhadap latar belakang agar mudah dibaca oleh semua pengguna.
  - Hindari penggunaan warna abu-abu tipis (`text-muted-foreground`) di atas kontainer abu-abu (`bg-muted/50`). Gunakan tingkat kegelapan yang lebih solid (minimal `text-foreground/75` atau `text-muted-foreground/90`).
* **Ritme Tipografi**: Kesan premium dibangun melalui kontras ukuran teks yang presisi dan pemanfaatan ruang kosong (*white space*) sebagai pemisah alami.

---

## Spesifikasi Interaksi (UX & Interaction Specs)

### 1. Responsivitas & Komponen Lembaran (Mobile Sheets)
* **Kinerja Seret Bebas Hambatan (Draggable Sheets)**: Pembaruan tinggi lembaran filter/menu di mobile saat diseret jari harus dimanipulasi langsung ke DOM style (`element.style.height`) menggunakan `requestAnimationFrame` untuk menghindari frame drop (60fps+). State React hanya diperbarui sekali di akhir proses seret (*single commit*).
* **Area Tangkap Sentuh Penuh**: Area navigasi penyeretan lembaran harus mencakup seluruh baris atas kontainer secara horizontal (`w-full`) agar pengguna dapat menyeret dari posisi mana pun dengan mudah.

### 2. Efek Hover Dinamis & Stabil (Hover States)
* **Pelebaran Halus (Expand-on-Hover)**: Tombol ikon kecil (seperti Bookmark atau Tambah Keranjang) dapat melebar ke arah kiri saat di-hover (`hover:w-[86px]`) dengan transisi durasi `300ms ease-in-out`.
* **Stabilitas Posisi Ikon**: Ikon di dalam tombol hover memanjang harus diposisikan secara absolut (`absolute right-[9px]`) agar posisinya tetap diam di koordinat yang sama dan tidak bergeser selama transisi pelebaran lebar tombol berlangsung.
* **Kemunculan Teks Lembut**: Teks yang muncul di sebelah kiri ikon bertransisi secara halus menggunakan `opacity-0` ke `opacity-100` agar tidak menghentak secara visual.

### 3. Kompartemen Pencarian (Search Dialog UX)
* **Posisi Dialog Nyaman**: Dialog pencarian diposisikan lebih ke atas (`top-[15%]! sm:top-[20%]!`) untuk memusatkan fokus mata pengguna.
* **Penyelarasan Presisi**: Ikon pada setiap baris sugesti hasil pencarian harus sejajar secara vertikal dengan ikon input pencarian utama.
* **Pemotongan Teks Dinamis**: Judul buku atau artikel yang terlalu panjang otomatis dipotong di sisi kanan menggunakan elipsis (`truncate`) menyesuaikan dengan batas lebar dialog secara responsif.
* **Penyembunyian Garis Pembatas input**: Garis pembatas bawah input pencarian (`border-b`) hanya muncul jika area sugesti hasil pencarian memanjang ke bawah.
