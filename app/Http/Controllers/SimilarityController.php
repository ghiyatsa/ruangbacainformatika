<?php

namespace App\Http\Controllers;

use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile;

class SimilarityController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('similarity', [
            'turnstileEnabled' => config('services.turnstile.enabled', false),
            'turnstileSiteKey' => config('services.turnstile.key'),
        ]);
    }

    public function check(Request $request, SimilarityApiService $api): JsonResponse
    {
        $rules = [
            'judul' => 'required|string|min:5',
        ];

        if (config('services.turnstile.enabled', false)) {
            $rules['cf-turnstile-response'] = ['required', 'string', new Turnstile];
        }

        $validated = $request->validate($rules, [
            'cf-turnstile-response.required' => 'Silakan selesaikan verifikasi keamanan.',
            'cf-turnstile-response.turnstile' => 'Verifikasi keamanan gagal, silakan coba lagi.',
        ]);

        $judul = trim($validated['judul']);

        if (str_word_count($judul) < 5) {
            return response()->json([
                'message' => 'Judul terlalu singkat. Masukkan minimal 5 kata agar pengecekan lebih akurat.',
            ], 422);
        }

        $cacheKey = 'similarity_check_'.md5(strtolower($judul));

        $hasil = Cache::get($cacheKey);

        if ($hasil === null) {
            $hasil = $api->checkSimilarity($judul);

            if ($hasil === null) {
                if (! $api->isHealthy()) {
                    return response()->json([
                        'message' => 'Layanan pemindaian kemiripan sedang tidak tersedia atau sedang "Sleep". Silakan coba lagi dalam beberapa detik (tunggu sekitar 30-60 detik untuk bangun).',
                    ], 503);
                }

                return response()->json([
                    'message' => 'Gagal melakukan pemindaian kemiripan. Jika masalah berlanjut, silakan hubungi tim pengelola perpustakaan.',
                ], 500);
            }

            if (! empty($hasil['results'])) {
                $skripsiIds = collect($hasil['results'])
                    ->pluck('id')
                    ->filter(fn ($id) => is_numeric($id))
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                $skripsis = Skripsi::whereIn('id', $skripsiIds)
                    ->get(['id', 'title', 'author_name', 'student_id'])
                    ->keyBy('id');

                $hasil['results'] = array_map(function ($item) use ($skripsis) {
                    $skripsiId = isset($item['id']) && is_numeric($item['id'])
                        ? (int) $item['id']
                        : null;
                    $skripsi = $skripsiId !== null ? $skripsis->get($skripsiId) : null;

                    $item['skripsi_id'] = $skripsiId;
                    $item['judul'] = $skripsi?->title ?? 'Data skripsi tidak ditemukan';
                    $item['nama_mahasiswa'] = $skripsi?->author_name ?? 'Tidak diketahui';
                    $item['student_id'] = $skripsi?->student_id;

                    // Normalize similarity_persen to float if it's a string like "98.1%"
                    if (is_string($item['similarity_persen'])) {
                        $item['similarity_persen'] = (float) str_replace('%', '', $item['similarity_persen']);
                    }

                    // Ensure level is uppercase for frontend mapping
                    if (isset($item['level'])) {
                        $item['level'] = strtoupper($item['level']);
                    }

                    return $item;
                }, $hasil['results']);
            }

            Cache::put($cacheKey, $hasil, now()->addDay());
        }

        return response()->json($hasil);
    }
}
