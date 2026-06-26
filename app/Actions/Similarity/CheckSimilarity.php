<?php

namespace App\Actions\Similarity;

use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckSimilarity
{
    public function __construct(
        protected SimilarityApiService $api,
    ) {}

    public function execute(string $title, ?int $userId = null): JsonResponse
    {
        Log::info('Similarity check requested', [
            'user_id' => $userId,
            'title_words' => str_word_count($title),
        ]);

        if (str_word_count($title) < 5) {
            return response()->json([
                'message' => 'Judul terlalu singkat. Masukkan minimal 5 kata agar pengecekan lebih akurat.',
            ], 422);
        }

        $cacheKey = 'similarity_check_'.hash('sha256', mb_strtolower($title));
        $result = Cache::get($cacheKey);

        if ($result === null) {
            $result = $this->api->checkSimilarity($title);

            if ($result === null) {
                if (! $this->api->isHealthy()) {
                    return response()->json([
                        'message' => 'Layanan pemindaian kemiripan sedang tidak tersedia atau sedang "Sleep". Silakan coba lagi dalam beberapa detik (tunggu sekitar 30-60 detik untuk bangun).',
                    ], 503);
                }

                return response()->json([
                    'message' => 'Gagal melakukan pemindaian kemiripan. Jika masalah berlanjut, silakan hubungi tim pengelola perpustakaan.',
                ], 500);
            }

            $result = $this->withLocalSkripsiData($result);

            Cache::put($cacheKey, $result, now()->addDay());
        }

        return response()->json($result);
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    protected function withLocalSkripsiData(array $result): array
    {
        if (empty($result['results']) || ! is_array($result['results'])) {
            return $result;
        }

        $skripsiIds = collect($result['results'])
            ->map(fn (array $item): ?int => match (true) {
                isset($item['id']) && is_numeric($item['id']) => (int) $item['id'],
                isset($item['skripsi_id']) && is_numeric($item['skripsi_id']) => (int) $item['skripsi_id'],
                default => null,
            })
            ->filter()
            ->values()
            ->all();

        $studentIds = collect($result['results'])
            ->pluck('nim')
            ->filter(fn ($nim) => is_string($nim) && filled($nim))
            ->values()
            ->all();

        $skripsis = Skripsi::query()
            ->whereIn('id', $skripsiIds)
            ->orWhereIn('student_id', $studentIds)
            ->get(['id', 'title', 'author_name', 'student_id']);

        $skripsisById = $skripsis->keyBy('id');
        $skripsisByStudentId = $skripsis->whereNotNull('student_id')->keyBy('student_id');

        $result['results'] = array_map(function ($item) use ($skripsisById, $skripsisByStudentId) {
            $skripsiId = match (true) {
                isset($item['id']) && is_numeric($item['id']) => (int) $item['id'],
                isset($item['skripsi_id']) && is_numeric($item['skripsi_id']) => (int) $item['skripsi_id'],
                default => null,
            };

            $studentId = isset($item['nim']) && is_string($item['nim'])
                ? $item['nim']
                : (isset($item['student_id']) && is_string($item['student_id'])
                    ? $item['student_id']
                    : null);

            $skripsi = $skripsiId !== null
                ? $skripsisById->get($skripsiId)
                : null;

            if ($skripsi === null && $studentId !== null) {
                $skripsi = $skripsisByStudentId->get($studentId);
            }

            $similarityPercent = $item['similarity_persen']
                ?? $item['similarity_percent']
                ?? null;

            if ($similarityPercent === null && isset($item['similarity_score']) && is_numeric($item['similarity_score'])) {
                $similarityPercent = round(((float) $item['similarity_score']) * 100, 1);
            }

            if (is_string($similarityPercent)) {
                $similarityPercent = (float) str_replace('%', '', $similarityPercent);
            }

            if (is_numeric($similarityPercent)) {
                $similarityPercent = round((float) $similarityPercent, 1);
            } else {
                $similarityPercent = 0.0;
            }

            $item['skripsi_id'] = $skripsi?->id ?? $skripsiId;
            $item['judul'] = $skripsi?->title ?? ($item['judul'] ?? $item['title'] ?? 'Data skripsi tidak ditemukan');
            $item['nama_mahasiswa'] = $skripsi?->author_name ?? ($item['nama_mahasiswa'] ?? $item['author_name'] ?? 'Tidak diketahui');
            $item['student_id'] = $skripsi?->student_id ?? $studentId;
            $item['similarity_persen'] = $similarityPercent;
            $item['is_local_record_found'] = $skripsi !== null;

            if (isset($item['level']) && is_string($item['level'])) {
                $item['level'] = strtoupper($item['level']);
            }

            return $item;
        }, $result['results']);

        return $result;
    }
}
