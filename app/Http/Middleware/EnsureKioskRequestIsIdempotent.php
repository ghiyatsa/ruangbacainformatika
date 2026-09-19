<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\KioskIdempotencyRecord;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Idempotensi transaksi kiosk (pinjam/kembali).
 *
 * Masalah yang dicegah: kiosk mengirim permintaan tulis, server memprosesnya
 * sampai commit, tetapi responsnya hilang (timeout/kabel putus). Klien
 * menganggap gagal lalu mencoba lagi — tanpa penjagaan ini, server akan
 * membuat transaksi kedua (mis. pinjaman ganda).
 *
 * Cara kerja: bila klien mengirim header `Idempotency-Key`, respons sukses
 * pertama disimpan. Permintaan berikutnya dengan key yang sama dan muatan
 * yang setara akan menerima respons tersimpan itu apa adanya, tanpa
 * menjalankan ulang logika bisnis.
 *
 * Sidik jari (fingerprint) sengaja TIDAK memuat `verification_payload`:
 * QR Member Key bersifat sekali pakai, sehingga percobaan ulang wajar memakai
 * QR baru. Key yang sama dengan sidik jari berbeda ditolak (409) agar bug
 * klien tidak diam-diam tertutupi.
 */
class EnsureKioskRequestIsIdempotent
{
    public const HEADER = 'Idempotency-Key';

    public const REPLAYED_HEADER = 'Idempotency-Replayed';

    /**
     * Masa simpan respons agar key tidak menumpuk selamanya.
     */
    public const TTL_HOURS = 24;

    /**
     * Bidang muatan yang menentukan kesetaraan permintaan. Bidang yang
     * bersifat sekali pakai (payload QR) sengaja dikecualikan.
     *
     * @var list<string>
     */
    private const FINGERPRINT_FIELDS = ['member_identifier', 'book_ids'];

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->idempotencyKey($request);

        if ($key === null) {
            return $next($request);
        }

        $fingerprint = $this->fingerprint($request);

        // Serialkan permintaan dengan key yang sama agar dua percobaan
        // bersamaan tidak sama-sama menembus ke logika bisnis.
        $lock = Cache::lock("kiosk-idempotency:{$key}", 10);

        return $lock->block(10, function () use ($request, $next, $key, $fingerprint): Response {
            $existing = KioskIdempotencyRecord::query()->where('key', $key)->first();

            if ($existing instanceof KioskIdempotencyRecord) {
                return $this->replay($existing, $fingerprint);
            }

            $response = $next($request);

            $this->remember($key, $fingerprint, $response);

            return $response;
        });
    }

    /**
     * Header idempotency, bila ada dan berupa string tak kosong.
     */
    protected function idempotencyKey(Request $request): ?string
    {
        $key = $request->header(self::HEADER);

        if (! is_string($key)) {
            return null;
        }

        $key = trim($key);

        // Batasi panjang agar cocok dengan kolom dan tahan penyalahgunaan.
        if ($key === '' || mb_strlen($key) > 191) {
            return null;
        }

        return $key;
    }

    /**
     * Sidik jari muatan permintaan yang relevan.
     */
    protected function fingerprint(Request $request): string
    {
        $payload = [];

        foreach (self::FINGERPRINT_FIELDS as $field) {
            $value = $request->input($field);

            if ($field === 'book_ids' && is_array($value)) {
                $value = array_map('intval', $value);
                sort($value);
            } elseif (is_string($value)) {
                $value = mb_strtolower(trim($value));
            }

            $payload[$field] = $value;
        }

        return hash('sha256', json_encode([
            'method' => $request->method(),
            'path' => $request->path(),
            'payload' => $payload,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Putar ulang respons tersimpan untuk key yang sama.
     */
    protected function replay(KioskIdempotencyRecord $record, string $fingerprint): Response
    {
        if (! hash_equals($record->fingerprint, $fingerprint)) {
            return response()->json([
                'message' => 'Idempotency-Key sudah dipakai untuk permintaan yang berbeda.',
            ], Response::HTTP_CONFLICT);
        }

        return response()
            ->json($record->response_body, $record->status_code)
            ->header(self::REPLAYED_HEADER, 'true');
    }

    /**
     * Simpan respons sukses agar percobaan ulang dapat diputar ulang.
     */
    protected function remember(string $key, string $fingerprint, Response $response): void
    {
        // Hanya respons sukses yang layak diputar ulang; galat biarkan
        // diproses ulang agar klien dapat memperbaiki permintaannya.
        if (! $response instanceof JsonResponse || ! $response->isSuccessful()) {
            return;
        }

        $body = $response->getData(true);

        if (! is_array($body)) {
            return;
        }

        KioskIdempotencyRecord::query()->create([
            'key' => $key,
            'fingerprint' => $fingerprint,
            'status_code' => $response->getStatusCode(),
            'response_body' => $body,
            'expires_at' => now()->addHours(self::TTL_HOURS),
        ]);
    }
}
