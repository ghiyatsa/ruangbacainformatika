# Plan Perbaikan Website Ruang Baca Informatika

Hasil audit menyeluruh (public pages + member flow). Eksekusi berurutan P0 → P3.
Verifikasi tiap batch: `php artisan test --compact` (filter terkait) + `vendor/bin/pint --dirty --format agent` + `npm run types:check`.

---

## P0 — Security & Correctness

### 1. Guard Member Key (role member + eligibility)
Lokasi: `app/Http/Controllers/Settings/MemberKeyController.php`

Saat ini siapapun yang login + profil lengkap bisa mint kiosk verification token (`routes/settings.php:13-14` hanya `auth + profile.completed`).

Perubahan:
- Tambah helper `redirectIfIneligible(User $user): ?RedirectResponse`
  - return null jika `$user->canStartLoanRequest()` (sudah mencakup role member, email kampus, approval, WA verified, profil lengkap, tidak sedang dibatasi keterlambatan — paritas dengan alur kiosk borrow)
  - jika tidak: flash toast warning dengan pesan dari `$user->borrowingBlockReason(true)['message']`, redirect ke `settings.profile.edit`
- Panggil di awal `show()` dan `generate()`. Return type `show` jadi `Response|RedirectResponse`.
- Pola sama persis dengan `LoanRequestController::show()` baris 27-41.

Test baru di `tests/Feature/MemberKeyTest.php`:
- user tanpa role member GET show → redirect profile edit
- user tanpa role member POST generate → redirect, `KioskBorrowVerificationService::current($user)` null

### 2. Unique index `return_drafts.token_hash`
Lokasi: `database/migrations/2026_05_26_033720_create_return_drafts_table.php:18` (tanpa unique; `loan_drafts` punya unique).

- Migration baru: hapus duplikat dulu (keep latest per hash) lalu `->unique()` pada kolom.
- Lookup memakai `first()` di `ReturnDraftService.php` jadi aman setelah index.

### 3. Throttle `similarity.check`
Lokasi: `routes/web.php:65-66`.

- Tambah middleware throttle (pattern limiter existing seperti `global-search` di `AppServiceProvider`/bootstrap).
- Alasan: tiap miss menghit external API (`app/Actions/Similarity/CheckSimilarity.php:51`).

### 4. WA skip flag expiry
Lokasi: `app/Services/Auth/AuthenticationRedirector.php:77-79`.

Session flag `whatsapp_verification_skipped` tak pernah di-clear → gate WA tertekan sepanjang sesi.
- Clear flag saat login sukses baru dan/atau verifikasi WA sukses (`WhatsAppVerificationController`).
- Tambah test untuk endpoint `register.whatsapp.skip` (saat ini nol referensi test) + clear-after-verify.

### 5. Robots noindex auth/kiosk
Lokasi: `resources/js/pages/auth/login.tsx:7`, `verify-whatsapp.tsx:7`, `register-profile.tsx:7`, `features/kiosk/components/KioskPage.tsx:74`.

- `<Head>` tambah meta robots noindex,nofollow (blade fallback defaultnya indexable).

### 6. Error page semua kode HTTP
Lokasi: `app/Providers/AppServiceProvider.php:283-291`.

- Whitelist `[403,404,419,429,500,503]` → fallback generik untuk semua status >= 400 agar `pages/error/index.tsx` selalu terpakai (kecuali skip local utk 500/503 yang tetap).

### 7. Fix error handling LoanRequestPage
Lokasi: `resources/js/features/loans/components/LoanRequestPage.tsx:271-317`.

- Backend (`LoanDraftQrRequest`/service) mengembalikan error scalar `book_ids` atau `draft`; frontend buru key `book_ids.N` yang tak pernah ada.
- Sederhanakan: baca `qrForm.errors.book_ids ?? qrForm.errors.draft`, tampilkan sebagai pesan tunggal.

---

## P1 — UX Nanggung

### 8. Konsistensi info kontak
- Alamat footer "Jl. Cot Tengku Nie, Reuleut…" (`FooterBrand.tsx:32`) ≠ contact page "Kampus Bukit Indah…" (`ContactPage.tsx:204-210`). Samakan sumber: SiteSettings/shared prop.
- Email hardcode 3x (`FooterBrand.tsx:37`, `FooterBottom.tsx:25`, `ContactPage.tsx:219-221`) → pakai `site.contactEmail`.

### 9. Link halaman `/search`
Halaman lengkap tapi orphan (hanya via GlobalSearchDialog). Tambah tautan di nav/footer.

### 10. Halaman notifikasi penuh
`notifications.index` JSON-only max 10 (`NotificationController.php:23`). Buat halaman `/notifications` (list + read-all + link "lihat semua" dari dropdown).

### 11. Sitemap & suggestions
- Tambah `/about` di `SitemapController.php:34-81`.
- Hapus query `SearchHistory` duplikat di `SearchController.php:289-309`.
- Hapus magic number 15 duplikat: kirim limit dari backend atau konstanta bersama (`search/index.tsx:288` vs `SearchController.php:123` dst).

### 12. Aksesibilitas blog
- Alt text cover kosong: `BlogPostCard.tsx:53,90`, `BlogShowPage.tsx:~415` → alt = judul post.

Catatan (ponytail): data tim about hardcode (`AboutTeamPage.tsx:17-102`) dibiarkan — pindah DB saat tim sering berubah.

---

## P2 — Admin Blind Spot (Filament)

### 13. Resource baru
- `MemberRegistrationClaim` review UI (kiosk claim flow invisible ke admin saat ini).
- `WhatsAppMessageLog` viewer (log OTP terkirim tak terlihat).
- Ikuti pola resource existing di `app/Filament/Resources`.

---

## P3 — Cruft Removal

### 14. Hapus kode mati
- `GUEST_SERVICE_LINKS` (`components/layout/footer/constants.ts:84-92`)
- `components/ui/tabs.tsx`, `components/animated/AnimatedList.tsx`, `hooks/use-has-mounted.ts`
- `app/Policies/PostTagPolicy.php` (tak direferensikan)
- Dead branch memberKey di `ProfilePage.tsx:8-44` + prop tak dikirim (`ProfileController.php:24-27`)
- Unused shared props: `auth.requiresWhatsAppVerification`, `auth.homeUrl` (`HandleInertiaRequests.php:69-75`) + tipe di `types/auth.ts:31,33`; `loanRequestCart.hasActiveQr` (JS hanya menulis fallback)
- Stale wayfinder artifacts `routes/password/*` (Fortify features `[]`, `config/fortify.php:145`)
- Redundan arg `ReturnDraftService.php:63` (`whereNull('returned_at', 'and', false)`)

### 15. JANGAN dihapus (perlu konfirmasi)
- Legacy QR prefix compat `RB-KIOSK-BORROW-VERIFY-` / `RB-LOAN-`: masih diterima extractor; baru aman dihapus setelah QR cetak lama dipastikan pensiun.
- Dashboard panel member (`DashboardPanelProvider.php`) — keputusan produk apakah dipertahankan.
