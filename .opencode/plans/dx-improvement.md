# Plan DX Improvement — Ruang Baca Informatika

Semua batch A–E disetujui user. Urutan eksekusi di bawah. Verifikasi akhir:
`composer ci:check` hijau + full `php artisan test --compact` hijau.

---

## A. Bug & correctness
1. **Fix Turnstile key mismatch**: `.env.example` + README ganti `TURNSTILE_SECRET_KEY` → `TURNSTILE_SECRET`
   (sesuai `config/services.php:66`). Cek juga kode yang membaca key lain (`TURNSTILE_SITE_KEY`?) agar konsisten.
2. **preventLazyLoading**: di `AppServiceProvider::boot()` →
   `Model::preventLazyLoading(! $this->app->isProduction())`. Pastikan suite tetap hijau
   (fix N+1 kalau muncul).
3. **AGENTS.md**: "PHP 8.3" → 8.4.

## B. Tooling wiring
4. composer.json scripts:
   - `"analyse": "@php artisan route:list >/dev/null && vendor/bin/phpstan"` (atau langsung `phpstan`)
   - masukkan `@analyse` ke `ci:check` dan `test`.
5. composer script `"wayfinder": "@php artisan wayfinder:generate --with-form"`; tambahkan ke `composer setup`
   dan langkah README setelah install.
6. Cleanup stale:
   - `phpunit.xml`: hapus env PULSE_ENABLED/TELESCOPE_ENABLED/NIGHTWATCH_ENABLED.
   - `.editorconfig`: hapus blok `[compose.yaml]`.

## C. CI (.github/workflows)
7. `tests.yml` + `lint.yml`:
   - setup-php dengan `cache: composer` (shivammathur/setup-php), setup-node `cache: npm`,
     plus `npm ci` via cache.
   - hapus step Flux secrets (flux tak terpasang).
   - matrix satu nilai → hilangkan wrapper matrix atau jadikan job biasa.
   - hapus xdebug, ATAU pakai untuk coverage upload artifact (pilih: hapus, sederhana).
   - branch filter: buang `master`, `workos`.
   - tambah `concurrency: group=${{ github.workflow }}-${{ github.ref }}, cancel-in-progress=true`.

## D. .env.example & docs
8. Tambah key: `APP_SUPER_ADMIN_NAME/EMAIL/WHATSAPP/ADDRESS`, `FONNTE_OTP_SEND_INTERVAL_SECONDS`,
   `SIMILARITY_SYNC_DISPATCH`, `INERTIA_SSR_ENABLED`.
9. Trim noise: MEMCACHED_HOST, AWS_*, POSTMARK_*, SLACK_* (stock Laravel default yang tak dipakai).
10. README:
    - bagian "Alur kerja development": husky/commitlint scopes, phpstan, wayfinder regeneration,
      `npm run types:php`, build:ssr variants.
    - peringatan `db:seed` no-op di luar environment local.
11. AGENTS.md versi PHP (sudah di A3).

## E. Opsional (disetujui)
12. ~~Debugbar~~ **DIBATALKAN**: `barryvdh/laravel-debugbar` mewajibkan ext-pcntl,
    tak tersedia di PHP Windows (host dev). Install akan gagal untuk semua kontributor Windows.
    Alternatif yang sudah ada: `php artisan pail`, log viewer Filament, telescope absent by design.
13. **Arsitektur test Pest** ✅ `tests/Arch.php`: larang dd/dump/var_dump/ray di app/,
    env() hanya di config/providers/database/tests, models bebas layer http/view/queue/Filament
    (User di-exempt: kontrak FilamentUser wajib).
14. **Parallel testing** ✅ tanpa dep baru: `php artisan test --parallel` — 530 test
    80s → 27s (12 proses). Didokumentasikan di README.
15. ~~Rector~~ **DIBATALKAN**: alasan sama dengan #12 (butuh ext-pcntl).
    Revisit saat CI-only runner Linux atau PHP native di Windows dengan pcntl.

## Catatan risiko
- preventLazyLoading bisa memunculkan N+1 nyata di flow katalog/kiosk — fix saat muncul, jangan dimatikan lagi.
- Rector berpotensi diff besar; batasi paths/sets, review manual.
- CI caching: pastikan key cache composer memakai hash composer.lock.
