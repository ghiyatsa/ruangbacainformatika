# Implementasi API Kiosk (Flutter) — Status

> Dokumen ini melacak implementasi **Fase A** dari `docs/kiosk-api-spec.md`.
> Terakhir diperbarui: 2026-09-16

## Status: SELESAI (11 endpoint + API key) ✅

Seluruh endpoint pada spesifikasi sudah diimplementasikan, teruji otomatis, dan diverifikasi
end-to-end terhadap server produksi.

---

## Model keamanan (keputusan akhir)

Komunikasi Flutter ↔ server dilindungi **dua lapis**:

| Lapis | Mekanisme | Keterangan |
|---|---|---|
| 1 | **Network guard** (`kiosk.network`) | Hanya CIDR jaringan perpustakaan yang diizinkan |
| 2 | **API key** (`X-Kiosk-Api-Key`) | Key bersama, disimpan sebagai **hash** di settings |

### PIN hanya untuk aktivasi, bukan untuk klien

Keputusan produk: **aplikasi Flutter tidak menampilkan layar PIN.**

- Teknisi mengaktifkan perangkat **sekali** dengan PIN (lewat `devices/activate` atau `kiosk:api-key`).
- Setelah itu Flutter memakai **API key** dari konfigurasi — tidak ada PIN lagi di aplikasi.
- API key **berlaku permanen** sampai dirotasi/dicabut dari server, dan dapat dicabut
  **tanpa membangun ulang** aplikasi.

Device token 24 jam **tetap didukung** untuk aktivasi perangkat lewat `devices/activate`
(teknisinya memakai PIN sekali), lalu klien Flutter memakai API key untuk permintaan berikutnya.

### Mengelola API key

```bash
php artisan kiosk:api-key show       # cek status
php artisan kiosk:api-key generate   # buat / rotasi (plaintext tampil sekali)
php artisan kiosk:api-key revoke     # cabut — semua akses langsung ditolak
```

Masukkan key ke konfigurasi Flutter: header `X-Kiosk-Api-Key: <key>`.

---

## Berkas

| Berkas | Keterangan |
|---|---|
| `routes/api.php` | 11 route di bawah prefix `api/kiosk` |
| `app/Http/Middleware/EnsureKioskDeviceTokenIsValid.php` | Auth API key **atau** device token (alias `kiosk.device`) |
| `app/Http/Controllers/Api/Kiosk/KioskApiController.php` | Controller JSON |
| `app/Console/Commands/KioskApiKeyCommand.php` | Kelola API key |
| `..._add_kiosk_device_id_to_member_registration_claims_table.php` | Kolom `kiosk_device_id` |
| `tests/Feature/Kiosk/KioskApiTest.php` | 16 test (device token) |
| `tests/Feature/Kiosk/KioskApiKeyTest.php` | 8 test (API key) |
| `tests/Feature/Kiosk/KioskLoanApiTest.php` | 14 test (pinjam/kembali) — dipindah dari tes UI web |
| `tests/Feature/Kiosk/KioskValidationApiTest.php` | 10 test (registrasi anggota + buku tamu) — dipindah dari tes UI web |
| `tests/Feature/Kiosk/KioskInfrastructureTest.php` | 3 test (rate limiter + kebijakan jam operasional) — dipindah dari tes UI web |

**Tidak ada logika bisnis yang diduplikasi** — controller hanya menjembatani HTTP ↔ Action
(`BorrowBooksFromKiosk`, `ReturnBooksFromKiosk`, `SearchKioskBooks`) dan Service yang sama.

---

## Verifikasi end-to-end (produksi)

| Uji | Hasil |
|---|---|
| Semua endpoint **dengan API key** | 200/201 ✅ |
| Tanpa key / key salah | 401 ✅ |
| Key dicabut → akses ditolak seketika | 401 ✅ |
| Activate PIN salah / benar | 422 / 200 ✅ |
| `members/find` | `hasEmail` + `emailDomain`, **tanpa email penuh** ✅ |
| Activate di luar jam operasional | 403 ✅ |

---

## ⚠️ Catatan operasional penting

### Wajib isi `kiosk.allowed_networks`

Saat ini **kosong** = izinkan semua. Karena PIN tidak lagi dipakai pada klien, **network guard
menjadi lapis pertahanan utama**. Isi dengan CIDR jaringan perpustakaan sebelum dipakai produksi.

### Deploy wajib reload php-fpm

Server memakai OPcache `validate_timestamps=Off`. Setiap deploy kode **harus** diikuti:

```bash
sudo systemctl reload php8.4-fpm
```

Juga hapus `bootstrap/cache/routes-v7.php` bila daftar route berubah. Tanpa ini, PHP-FPM
terus menjalankan kode lama.

---

## Riwayat migrasi

1. **Halaman admin** untuk kelola perangkat/API key — ✅ **dihapus**. Panel admin kini
   bersih dari UI kiosk (`KioskApiKeySettings`, field PIN, blade view). Pengelolaan API key
   hanya lewat artisan `kiosk:api-key`; komunikasi Flutter ↔ server murni lewat
   `routes/api.php`. PIN tetap dibaca dari settings (`kiosk.pin_hash`, diisi seeder/env
   `KIOSK_DEFAULT_PIN`) untuk `devices/activate`, tanpa UI web.
2. **Idempotency-Key** untuk borrow/return — ✅ **selesai**. Middleware
   `kiosk.idempotent` menyimpan respons sukses pertama per key (tabel
   `kiosk_idempotency_records`, TTL 24 jam) dan memutar ulang respons itu untuk
   percobaan ulang, sehingga koneksi putus di tengah transaksi tidak menghasilkan
   pinjaman/pengembalian ganda. Sidik jari sengaja mengecualikan
   `verification_payload` (QR sekali pakai) dan menolak key sama dengan muatan
   berbeda (409). Klien Flutter memakai `IdempotencyKey` (key stabil antar
   percobaan). Records kedaluwarsa dipangkas oleh `records:prune`.
3. **Penghapusan kiosk web** — ✅ **selesai**. `routes/kiosk.php`, `KioskController`,
   `EnsureKioskPinIsValid`, komponen React `features/kiosk` + `pages/kiosk`, dan rate limiter
   `kiosk-consume` sudah dihapus. Cakupan tes yang menguji logika bisnis yang masih hidup
   dipindahkan ke tes API (`KioskLoanApiTest`, `KioskValidationApiTest`,
   `KioskInfrastructureTest`) — bukan dibuang.
