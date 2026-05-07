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
            'turnstileSiteKey' => config('services.turnstile.key'),
        ]);
    }

    public function check(Request $request, SimilarityApiService $api): JsonResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|min:5',
            'cf-turnstile-response' => ['required', 'string', new Turnstile],
        ], [
            'cf-turnstile-response.required' => 'Silakan selesaikan verifikasi keamanan.',
            'cf-turnstile-response.turnstile' => 'Verifikasi keamanan gagal, silakan coba lagi.',
        ]);

        $judul = trim($validated['judul']);

        if (str_word_count($judul) < 5) {
            return response()->json([
                'message' => 'Judul terlalu singkat. Masukkan minimal 3 kata agar pengecekan lebih akurat.',
            ], 422);
        }

        $cacheKey = 'similarity_check_'.md5(strtolower($judul));

        $hasil = Cache::remember($cacheKey, now()->addDay(), function () use ($judul, $api) {
            $hasil = $api->checkSimilarity($judul);

            if (! empty($hasil['results'])) {
                $skripsiIds = collect($hasil['results'])->pluck('skripsi_id')->filter()->toArray();
                $skripsis = Skripsi::whereIn('id', $skripsiIds)
                    ->get(['id', 'student_id'])
                    ->keyBy('id');

                $hasil['results'] = array_map(function ($item) use ($skripsis) {
                    $item['student_id'] = $skripsis->get($item['skripsi_id'])?->student_id;

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

            return $hasil;
        });

        return response()->json($hasil);
    }
}
