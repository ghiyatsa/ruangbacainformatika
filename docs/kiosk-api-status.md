# Implementasi API Kiosk (Flutter) — Status

> Dokumen ini melacak implementasi **Fase A** dari `docs/kiosk-api-spec.md`.
> Terakhir diperbarui: 2026-09-16

## Status: SELESAI (11 endpoint) ✅

Seluruh endpoint pada spesifikasi sudah diimplementasikan, teruji otomatis (16 test), dan diverifikasi
end-to-end terhadap server produksi.

---

## Yang diimplementasikan

| Berkas | Keterangan |
|---|---|
| `routes/api.php` | 11 route di bawah prefix `api/kiosk`, dua grup middleware |
| `app/Http/Middleware/EnsureKioskDeviceTokenIsValid.php` | Auth device token (alias `kiosk.device`) |
| `app/Http/Controllers/Api/Kiosk/KioskApiController.php` | Controller JSON — memanggil Action yang sudah ada |
| `database/migrations/2026_09_16_131056_add_kiosk_device_id_to_member_registration_claims_table.php` | Kolom `kiosk_device_id` untuk registrasi tanpa session |
| `bootstrap/app.php` | Registrasi `api` route + alias `kiosk.device` |
| `tests/Feature/Kiosk/KioskApiTest.php` | 16 test (61 assertion) |

**Tidak ada logika bisnis yang diduplikasi** — controller hanya menjembatani HTTP ↔ Action
(`BorrowBooksFromKiosk`, `ReturnBooksFromKiosk`, `SearchKioskBooks`) dan Service yang sama.

---

## Verifikasi end-to-end (produksi, 2026-09-16)

| Uji | Hasil |
|---|---|
| Activate PIN salah | 422 ✅ |
| Activate PIN benar | 200 + `device_token` (64 char) ✅ |
| Bootstrap tanpa token | 401 ✅ |
| Bootstrap dengan token | 200 (opsi form + stats) ✅ |
| Cari buku | 200 (data nyata) ✅ |
| `members/find` | `hasEmail` + `emailDomain`, **tanpa email penuh** ✅ |
| Lock | 200 ✅ |
| Bootstrap setelah lock | 401 (token mati) ✅ |

Diverifikasi juga: `activate` di luar jam operasional → **403** (sesuai perilaku kiosk web).

---

## Keputusan & catatan operasional

### Jaringan
Kiosk Flutter berjalan di **jaringan kampus yang sama**, sehingga middleware `kiosk.network`
(CIDR `kiosk.allowed_networks`) **tetap dipertahankan** — inilah lapis pertahanan terkuat.

> ⚠️ **Penting:** saat ini `kiosk.allowed_networks` **kosong** = izinkan semua. Sebelum
> kiosk web dihapus, isi dengan CIDR jaringan perpustakaan agar guard benar-benar aktif.

### Jam operasional
`activate` menolak di luar `kiosk.operating_open_time`–`operating_close_time`
(default **08:00–17:00**, zona `Asia/Jakarta`). Aplikasi Flutter akan menampilkan 403
sebagai pesan "di luar jam operasional", bukan crash.

### Rate limit
Seluruh endpoint memakai limiter yang sudah ada (`kiosk-pin`, `kiosk-submit`,
`kiosk-book-search`, `kiosk-member-status`, `kiosk-member-lookup`) — tanpa konfigurasi baru.

### Registrasi anggota
State claim tidak lagi bergantung pada session web. Claim ditautkan ke `kiosk_device_id`,
sehingga perangkat mana pun (Flutter atau web) dapat memantau status claim-nya sendiri.

---

## ⚠️ Catatan deploy (PENTING)

Server produksi memakai OPcache dengan **`validate_timestamps=Off`**. Artinya:

> **Setiap deploy kode baru WAJIB diikuti `sudo systemctl reload php8.4-fpm`.**
> Tanpa reload, PHP-FPM akan terus menjalankan kode lama dari cache meskipun berkas di disk
> sudah berubah. Juga hapus `bootstrap/cache/routes-v7.php` bila daftar route berubah.

---

## Belum dikerjakan (menunggu keputusan)

1. **Idempotency-Key** untuk borrow/return (§5 spesifikasi) — belum, menunggu kebutuhan nyata
   dari sisi Flutter.
2. **Penghapusan kiosk web** — hanya setelah aplikasi Flutter terbukti stabil.
3. **Pengisian `kiosk.allowed_networks`** — sebelum kiosk web dihapus.
