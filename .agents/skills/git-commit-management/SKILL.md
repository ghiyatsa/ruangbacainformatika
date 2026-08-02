---
name: git-commit-management
description: 'ACTIVATE whenever working with git commits, pushes, pull requests, Conventional Commits, commitlint, version management, release-please, branch cleanup/hygiene, or any git history operation in this repo. Use when the user asks to commit, push, create a PR, fix a commit message, resolve commitlint/CI failures, clean up branches, release a version, or manage changelogs. Covers: drafting Conventional Commit messages that pass this repo''s commitlint config (including body line limits), validating full messages before commit, setting conventional PR titles (critical because squash merges use the PR title as the commit message), rebasing feature branches onto main, handling deleted/recreated branches after merge, force-push rules, and release-please conventions.'
license: MIT
metadata:
  author: ghiyatsa
---

# Git Commit & Version Management

Pedoman wajib agar kesalahan commitlint/release tidak berulang. Referensi repo: `.agents/workflows/git-commit-automation.md` (workflow lengkap) dan `commitlint.config.js`.

## Aturan Pesan Commit (Conventional Commits)

- Format header: `type(scope): subject` — semua lowercase, subject < 100 char.
- **Tipe yang boleh** (tipe lain DITOLAK CI): `build, chore, ci, docs, feat, fix, perf, refactor, revert, test`. **`style` TIDAK BOLEH** — untuk styling pakai `refactor`/`chore`.
- **Scope yang boleh**: `admin, auth, catalog, deps, github, kiosk, loan, settings, similarity, ui, return, whatsapp`. Jangan pakai `ci`/`test` sebagai scope (pakai `github` untuk workflow CI). `deps` khusus perubahan dependency (composer.json/lock).
- **Body: setiap baris WAJIB ≤ 100 karakter.** Ini sumber kegagalan berulang — header lolos tapi baris body kepanjangan.

## Wajib: Validasi FULL MESSAGE Sebelum Commit (sering dilupakan!)

- JANGAN hanya validasi header. Baris body > 100 char akan men-rolback CI walaupun header lolos.
- Validasi pesan lengkap (header + body) dengan perintah yang sama persis dengan CI:
  ```powershell
  npx commitlint --from <base-commit> --to <head-commit> --verbose
  ```
  Contoh (CI commitlint.yml memakai range ini):
  ```powershell
  npx commitlint --from origin/main --to HEAD --verbose
  ```
- Sebelum commit, validasi draft pesan penuh. **GOTCHA PowerShell**: `echo "pesan" | npx commitlint` menambah BOM/spasi → gagal. Gunakan wrapper CMD, TANPA spasi sebelum `|`:
  ```powershell
  cmd /c "echo type(scope): subject| npx commitlint --verbose"
  ```
- `git commit` memakai beberapa `-m` (tiap paragraf) agar tidak ada masalah newline/BOM:
  ```powershell
  git commit -m "feat(admin): subject" -m "- bullet 1 (max 100 char)" -m "- bullet 2 (max 100 char)"
  ```
  Jangan pernah memasukkan string multi-baris sebagai satu argumen ke perintah native (PowerShell memecahnya) — pakai body-file `-F` bila perlu.

## PR Title = Commit Message Squash (sumber error paling umum)

- GitHub **squash merge memakai judul PR sebagai pesan commit** di main. Judul PR non-conventional → commit di main gagal commitlint → CI merah (kasus `Feat/metadata curation (#30)`).
- Saat membuat PR, WAJIB set title berbentuk `type(scope): subject` yang sama dengan commit utamanya:
  ```powershell
  gh pr create --title "feat(admin): add cron and queue monitor pages to sistem group" --body-file body.md
  ```
- Jangan pakai title auto-suggest GitHub dari nama branch (contoh: branch `feat/metadata-curation` → title `Feat/metadata curation` — huruf kapital + bukan format commit = FAIL).
- `gh pr create` dengan body multi-baris: gunakan `--body-file` (argumen native PowerShell memecah string multi-baris).

## Workflow Commit & Push

1. `git status --short` + `git diff` — pastikan hanya file yang dimaksud.
2. Jalankan quality check: `vendor/bin/pint --dirty --format agent`, `php artisan test --compact`, `npm run types:check`, `npm run lint:check`. Gagal → perbaiki dulu.
3. Stage per kelompok logis: `git add <file...>`.
4. Validasi pesan penuh (lihat aturan di atas) → commit → `git push origin <branch>`.
5. Buat PR dengan title conventional → beri URL PR ke user.

## Branch Hygiene & Rebase

- Sebelum bekerja di feature branch: `git pull --rebase origin main`.
- Setelah PR merge: branch biasanya **dihapus GitHub**. Push berikutnya akan menghidupkannya lagi — cek dulu:
  ```powershell
  git fetch --prune origin; git branch -a; gh pr view <n> --json state,mergedAt,mergeCommit
  ```
- Jika PR sudah di-merge (squash) dan masih ada commit lanjutan di branch: `git switch main; git pull --ff-only origin main; git switch <branch>; git rebase main` — commit yang isinya sudah ada di main otomatis di-drop. Lalu `git push --force-with-lease` + PR baru (jangan lanjutkan PR lama yang sudah merged).
- Hapus branch stale hanya setelah verifikasi konten sudah di main: `git branch -r --merged origin/main`, `git merge-base --is-ancestor <tip> origin/main`, atau `git diff (git merge-base origin/main <tip>) <tip> --stat` untuk melihat work unik. Branch yang berisi work yang di-*revert* di main → tanya user sebelum hapus.

## Force-Push Rules

- `--force-with-lease` SELALU, tidak pernah `--force` polos.
- Hanya lakukan atas persetujuan eksplisit user (branch `main` dilindungi GitHub: `allow_force_pushes=false`; rewrite main butuh user mengaktifkan "Allow force pushes" sementara di Settings → Branches, lalu push, lalu dimatikan lagi).

## Version Management (release-please)

- Release ditangani bot `release-please`: commit `chore(main): release X.Y.Z (#N)` + tag `vX.Y.Z` + `CHANGELOG.md`/`version.txt` diperbarui otomatis di main.
- release-please hanya membaca commit yang PARSEABLE (conventional): `feat` → minor, `fix`/`chore` → patch, `feat!`/`fix!` → major.
- Commit non-conventional di main (mis. hasil squash title jelek) TIDAK terhitung release → bump versi bisa meleset. Perbaiki pesannya (reword + force-push dengan persetujuan) agar release akurat.
- Branch temp `release-please--branches--main` dibuat bot saat release PR; biarkan, bot membersihkannya sendiri.

## Checklist Sebelum Push Akhir

- [ ] Header `type(scope): subject` — type & scope dari daftar resmi
- [ ] SEMUA baris body ≤ 100 char
- [ ] `npx commitlint --from origin/main --to HEAD --verbose` = 0 problems
- [ ] Pint + test suite lulus
- [ ] PR title berbentuk conventional (untuk squash merge)
