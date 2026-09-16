# Spesifikasi API Kiosk — untuk Aplikasi Flutter (Desktop Windows)

> **Status:** Rancangan (untuk ditinjau sebelum implementasi)
> **Versi dokumen:** 1.0
> **Tanggal:** 2026-09-16
> **Tujuan:** Memungkinkan aplikasi Flutter mandiri menggantikan halaman kiosk web, sehingga seluruh route `/kiosk` dapat dihapus dari web publik.

---

## 1. Ringkasan & Prinsip

Aplikasi Flutter menjadi **klien API** untuk perpustakaan. Logika bisnis **tidak diduplikasi** — Flutter memanggil endpoint yang menjalankan Action yang sudah ada (`BorrowBooksFromKiosk`, `ReturnBooksFromKiosk`, `SearchKioskBooks`).

| Prinsip | Keterangan |
|---|---|
| **Tanpa sesi web** | Auth memakai *device token* jangka panjang, bukan cookie/session Laravel |
| **Tanpa Inertia** | Respons murni JSON, tidak merender halaman |
| **Logika bisnis di server** | Flutter tidak pernah menghitung aturan pinjam/kembali sendiri |
| **Dua lapis auth** | (1) Network guard CIDR, (2) Device token |
| **Idempoten aman** | Token QR sekali pakai; submit pinjam/kembali punya idempotency key |

### Alur besar

```
Flutter (Windows)                  Laravel (server perpustakaan)
   │                                    │
   │  1. POST /api/kiosk/devices/activate  (PIN + fingerprint perangkat)
   │ ─────────────────────────────────► │  → device_token (simpan di secure storage)
   │ ◄───────────────────────────────── │
   │                                    │
   │  2. GET  /api/kiosk/bootstrap       (jam operasional, opsi form, limit)
   │ ─────────────────────────────────► │
   │ ◄───────────────────────────────── │
   │                                    │
   │  3. POST /api/kiosk/visits          (input kunjungan)
   │  4. GET  /api/kiosk/books/search    (cari buku)
   │  5. POST /api/kiosk/loans/borrow    (scan QR → pinjam)
   │  6. POST /api/kiosk/loans/return    (scan QR → kembali)
   │  7. POST /api/kiosk/members         (registrasi anggota baru → QR)
   └────────────────────────────────────┘
```

---

## 2. Autentikasi

### 2.1 Dua lapis pertahanan

**Lapis 1 — Network Guard (sudah ada, dipertahankan).**
`EnsureKioskNetworkIsAllowed` menolak request di luar CIDR yang diizinkan (`kiosk.allowed_networks`). Jika kosong → semua diizinkan (untuk dev). Endpoint API baru **wajib** memakai middleware ini.

**Lapis 2 — Device Token.**

| Aspek | Rancangan |
|---|---|
| Format | `kiosk_devices.device_token` — 64 char acak (`Str::random(64)`) |
| Penyimpanan klien | Windows **secure storage** (DPAPI / `flutter_secure_storage`) |
| Pengiriman | Header `X-Kiosk-Device-Token: <token>` |
| Masa berlaku | 24 jam sejak `last_active_at` (sama dengan perilaku cookie sekarang) |
| Rotasi | Token di-*regenerate* setiap aktivasi PIN berhasil |
| Pembatalan | `kiosk_pin` diubah / `session_version` dinaikkan → semua device token mati |

> **Catatan implementasi:** middleware baru `kiosk.device` membaca header `X-Kiosk-Device-Token`, mencari `KioskDevice`, memvalidasi `last_active_at > now()-24h` dan `session_version` cocok. Berbeda dari `KioskPinManager` saat ini yang membaca cookie + session; versi API harus bebas session.

### 2.2 Aktivasi perangkat

PIN kiosk **tidak pernah** disimpan di Flutter. Hanya dipakai sekali untuk menukar device token.

`POST /api/kiosk/devices/activate`

```json
// Request
{
  "pin": "123456",
  "device_name": "Kiosk Perpustakaan Lt.1"
}

// 200 Response
{
  "device_token": "9f2c...64chars",
  "expires_at": "2026-09-17T09:00:00+07:00",
  "session": {
    "timezone": "Asia/Jakarta",
    "operating_open_time": "07:00",
    "operating_close_time": "21:00",
    "within_operating_hours": true,
    "session_expires_at": "2026-09-16T09:30:00+07:00"
  }
}

// 422 — PIN salah
{ "message": "PIN kiosk tidak valid.", "errors": { "pin": ["PIN kiosk tidak valid."] } }

// 403 — di luar jam operasional
{ "message": "Sesi kiosk hanya dapat dimulai pada jam operasional perpustakaan." }

// 503 — PIN belum dikonfigurasi
{ "message": "PIN kiosk belum tersedia. Silakan hubungi petugas perpustakaan." }
```

**Rate limit:** `kiosk-pin` (sudah ada).

### 2.3 Bootstrap

`GET /api/kiosk/bootstrap` — dipanggil saat app dibuka / sesi baru.

```json
{
  "loan_max_books": 3,
  "visitor_type_options": [ { "value": "mahasiswa", "label": "Mahasiswa" }, ... ],
  "purpose_options": [ { "value": "reading", "label": "Membaca" }, ... ],
  "session": { /* sama seperti di atas */ },
  "stats": {
    "visits_today": 12,
    "books_borrowed_today": 4,
    "books_returned_today": 2,
    "active_loans": 8
  }
}
```

> `stats` bersifat opsional — boleh dihilangkan bila layar Flutter tidak menampilkan statistik.

### 2.4 Kunci perangkat

`POST /api/kiosk/lock` → menghapus device token server-side.
```json
{ "locked": true }
```

---

## 3. Endpoint

Semua endpoint di bawah ini memerlukan header `X-Kiosk-Device-Token` + middleware `kiosk.network`.

### 3.1 Catat Kunjungan — `POST /api/kiosk/visits`

```json
// Request
{
  "name": "Ahmad Fauzi",
  "visitor_type": "mahasiswa",         // salah satu visitor_type_options[].value
  "identity_number": "230170101",       // nullable
  "institution": "Universitas Malikussaleh", // nullable
  "phone": "081234567890",              // nullable
  "purpose": "reading",                 // salah satu purpose_options[].value
  "notes": null                         // nullable
}

// 201 Response
{ "visit": { "id": 1234, "name": "Ahmad Fauzi", "visited_at": "2026-09-16T13:05:00+07:00" } }
```

**Rate limit:** `kiosk-submit`

---

### 3.2 Cari Buku — `GET /api/kiosk/books/search`

Query: `q`, `mode` (`borrow`|`return`), `member_identifier` (wajib untuk `mode=return`).

```
GET /api/kiosk/books/search?q=algoritma&mode=borrow
GET /api/kiosk/books/search?q=&mode=return&member_identifier=230170101
```

```json
// 200 Response
{
  "books": [
    {
      "id": 42,
      "title": "Algoritma dan Pemrograman",
      "authors": [ { "id": 7, "name": "Rinaldi Munir" } ],
      "available_items_count": 2,
      "items_count": 3,
      "cover_url": "https://.../cover.jpg"
    }
  ]
}
```

> **Bentuk `books[]` mengikuti `BookResource` yang sudah ada** — jangan bikin resource baru agar konsisten dengan web.

**Rate limit:** `kiosk-book-search`. Maksimal 8 hasil.

---

### 3.3 Pinjam Buku — `POST /api/kiosk/loans/borrow`

Alur: anggota membuka aplikasi web/mobile miliknya → dapat QR (`MK-...`, berlaku **1 menit**) → kiosk scan QR → kirim payload.

```json
// Request
{
  "verification_payload": "MK-xxxxxxxx...",  // hasil scan QR
  "member_identifier": "230170101",           // NIM/email/HP yang diketik di kiosk
  "book_ids": [42, 43]                        // maksimal = loan_max_books
}

// 201 Response
{
  "loan": {
    "id": 555,
    "member": { "name": "Ahmad Fauzi" },
    "books_count": 2,
    "borrowed_at": "2026-09-16T13:10:00+07:00",
    "due_at": "2026-09-23T13:10:00+07:00"
  },
  "message": "Peminjaman untuk Ahmad Fauzi berhasil disimpan. Bukti akan dikirim ke WhatsApp anggota."
}

// 422 — identitas tidak cocok dengan QR
{ "message": "...", "errors": { "member_identifier": ["Identitas anggota tidak sesuai dengan member key yang discan."] } }

// 422 — QR kedaluwarsa/sudah dipakai
{ "errors": { "verification_payload": ["QR verifikasi peminjaman sudah kedaluwarsa. Buat ulang dari akun anggota."] } }
```

**Aturan validasi (dari `BorrowBookRequest`, jangan diubah):**
- `member_identifier`: email valid \| NIM 9 digit \| HP `08xx`
- `book_ids`: array, min 1, distin, harus ada di tabel `books`
- Token QR: sekali pakai, kedaluwarsa 1 menit

**Idempotency:** kiosk offline lalu retry. Sertakan header `Idempotency-Key: <uuid>` → request dengan key sama mengembalikan respons pertama (mencegah pinjam ganda).

**Rate limit:** `kiosk-submit`

---

### 3.4 Kembalikan Buku — `POST /api/kiosk/loans/return`

```json
// Request — sama seperti borrow
{ "verification_payload": "MK-...", "member_identifier": "230170101", "book_ids": [42] }

// 200 Response
{
  "returned_count": 1,
  "member": { "id": 9, "name": "Ahmad Fauzi" },
  "message": "1 buku berhasil dikembalikan."
}
```

**Rate limit:** `kiosk-submit`

---

### 3.5 Registrasi Anggota Baru — `POST /api/kiosk/members`

> ⚠️ **Perlu penyesuaian kecil (sudah diverifikasi).** `MemberRegistrationClaim` **sudah berupa model DB**, jadi data claim aman. Yang memakai session hanyalah *pointer* (`kiosk.member_registration_claim` menyimpan snapshot claim agar halaman web tahu claim mana yang sedang ditampilkan). **Solusi:** tambahkan kolom `kiosk_device_id` (nullable, FK) ke `member_registration_claims`, lalu endpoint API menanyakan claim aktif berdasarkan device, bukan session. Lihat §5.

```json
// Request
{
  "name": "Siti Aminah",
  "email": "siti@mhs.unimal.ac.id",
  "whatsapp": "081234567890",
  "address": "Lhokseumawe"
}

// 201 Response
{
  "claim": {
    "id": 88,
    "link_url": "https://.../link-google?token=...",
    "qr_svg": "<svg .../>",
    "status": "pending",
    "expires_at": "2026-09-16T13:20:00+07:00"
  },
  "message": "QR siap digunakan. Scan dari ponsel untuk menautkan akun Google."
}
```

### 3.6 Status Registrasi — `GET /api/kiosk/members/status`

Polling setiap 3 detik oleh Flutter.

```json
{
  "claim": {
    "id": 88, "status": "claimed",       // pending|claimed|expired|failed
    "approval_pending": true,
    "last_error_message": null
  }
}
```
**Rate limit:** `kiosk-member-status`

### 3.7 Batal Registrasi — `POST /api/kiosk/members/cancel`
```json
{ "cancelled": true }
```

### 3.8 Cari Anggota — `GET /api/kiosk/members/find?identifier=230170101`

```json
{
  "member": {
    "name": "Ahmad Fauzi",
    "has_email": true,
    "email_domain": "mhs.unimal.ac.id",
    "whatsapp_masked": "0857******75"
  }
}
```

> **JANGAN kirim email penuh.** Ini sudah diperbaiki di PR #83 — email penuh adalah kebocoran enumerasi. Format di atas adalah yang benar.

**Rate limit:** `kiosk-member-lookup`

---

## 4. Format Error (standar)

```json
// 422 validasi
{ "message": "Data yang diberikan tidak valid.", "errors": { "field": ["pesan"] } }

// 401 device token tidak valid
{ "message": "Perangkat kiosk tidak terautentikasi." }

// 403 di luar jaringan/jam operasional
{ "message": "Akses kiosk hanya diizinkan dari jaringan internal perpustakaan." }

// 429
{ "message": "Terlalu banyak permintaan." }
```

---

## 5. Perubahan yang Dibutuhkan di Backend

| # | Perubahan | Ukuran |
|---|---|---|
| 1 | `routes/api.php` baru + registrasi di `bootstrap/app.php` | Kecil |
| 2 | Middleware `EnsureKioskDeviceTokenIsValid` (`kiosk.device`) | Kecil |
| 3 | `Api\Kiosk\*Controller` — tipis, memanggil Action yang sudah ada | Kecil |
| 4 | `KioskDeviceResource` / `VisitResource` / `LoanResource` | Kecil |
| 5 | **Registrasi anggota:** tambah kolom `kiosk_device_id` (nullable FK) di `member_registration_claims`; endpoint API cari claim aktif per device, bukan per session | Kecil–Sedang |
| 6 | `IdempotencyKey` middleware untuk borrow/return | Sedang |
| 7 | Test feature untuk seluruh endpoint | Sedang |

**Yang TIDAK berubah:** semua Action, Service, dan aturan bisnis (`KioskBorrowVerificationService`, `KioskLoanService`, `KioskNetworkGuard`, `KioskIdlePolicy`).

---

## 6. Rencana Migrasi (tanpa downtime)

| Fase | Tindakan | Kiosk web |
|---|---|---|
| **1** | Implementasi API + test | tetap hidup |
| **2** | Bangun app Flutter, uji di samping kiosk web | tetap hidup |
| **3** | Ganti kiosk fisik dengan Flutter | tetap hidup (cadangan) |
| **4** | Hapus `routes/kiosk.php`, `KioskController`, komponen React kiosk | **dimatikan** |
| **5** | Bersihkan `withoutSsr`, bundle chunk, test kiosk web | — |

**Jangan lompat ke fase 4 sebelum fase 3 terbukti stabil.**

---

## 7. Ringkasan Endpoint

| Method | Path | Auth | Rate limit |
|---|---|---|---|
| POST | `/api/kiosk/devices/activate` | network + PIN | `kiosk-pin` |
| GET | `/api/kiosk/bootstrap` | device | — |
| POST | `/api/kiosk/lock` | device | — |
| POST | `/api/kiosk/visits` | device | `kiosk-submit` |
| GET | `/api/kiosk/books/search` | device | `kiosk-book-search` |
| POST | `/api/kiosk/loans/borrow` | device | `kiosk-submit` |
| POST | `/api/kiosk/loans/return` | device | `kiosk-submit` |
| POST | `/api/kiosk/members` | device | `kiosk-submit` |
| GET | `/api/kiosk/members/status` | device | `kiosk-member-status` |
| POST | `/api/kiosk/members/cancel` | device | — |
| GET | `/api/kiosk/members/find` | device | `kiosk-member-lookup` |

---

## 8. Pertanyaan Terbuka (untuk keputusan user)

1. **Jaringan Flutter** — kiosk Flutter akan berjalan di jaringan perpustakaan yang sama? Kalau ya, network guard tetap efektif. Kalau tidak, guard harus dilonggarkan (kurang aman).
2. **App update** — bagaimana Flutter diperbarui di kiosk fisik? (manual / auto-update)
3. **Registrasi anggota** — apakah layar ini dipakai? Kalau jarang, bisa dihilangkan dari Flutter v1 (mengurangi pekerjaan §5).
4. **Offline** — perlu antrean saat jaringan putus, atau tolak dengan pesan jelas?
