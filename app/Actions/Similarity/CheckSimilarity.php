<?php

namespace App\Actions\Similarity;

use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckSimilarity
{
    private const CACHE_VERSION_KEY = 'similarity_check_version';

    public function __construct(
        protected SimilarityApiService $api,
    ) {}

    /**
     * Invalidate semua hasil cek kemiripan yang ter-cache (panggil setelah data tersinkron).
     */
    public static function invalidateCache(): int
    {
        $version = ((int) Cache::get(self::CACHE_VERSION_KEY, 0)) + 1;

        Cache::forever(self::CACHE_VERSION_KEY, $version);

        return $version;
    }

    public function execute(string $title, ?string $documentType = null, ?int $userId = null): JsonResponse
    {
        Log::info('Similarity check requested', [
            'user_id' => $userId,
            'document_type' => $documentType,
            'title_words' => str_word_count($title),
        ]);

        if (str_word_count($title) < 5) {
            return response()->json([
                'message' => 'Judul terlalu singkat. Masukkan minimal 5 kata agar pengecekan lebih akurat.',
            ], 422);
        }

        $version = Cache::get(self::CACHE_VERSION_KEY, 0);
        $cacheKey = "similarity_check_v{$version}_".hash('sha256', mb_strtolower($title).'_'.($documentType ?? 'all'));
        $result = Cache::get($cacheKey);

        if ($result === null) {
            $result = $this->api->checkSimilarity($title, documentType: $documentType);

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

        $skripsiIds = [];
        $internshipIds = [];

        foreach ($result['results'] as $item) {
            if (! is_numeric($item['document_id'] ?? null)) {
                continue;
            }

            if (($item['document_type'] ?? 'skripsi') === 'internship_report') {
                $internshipIds[] = (int) $item['document_id'];
            } else {
                $skripsiIds[] = (int) $item['document_id'];
            }
        }

        $skripsisById = Skripsi::query()->whereIn('id', $skripsiIds)->get(['id', 'title', 'author_name', 'student_id'])->keyBy('id');
        $internshipsById = InternshipReport::query()->whereIn('id', $internshipIds)->get(['id', 'title', 'author_name', 'student_id'])->keyBy('id');

        $result['results'] = array_map(function (array $item) use ($skripsisById, $internshipsById): array {
            $type = $item['document_type'] ?? 'skripsi';
            $docId = $item['document_id'] ?? null;
            $isInternship = $type === 'internship_report';

            $record = is_numeric($docId)
                ? ($isInternship ? $internshipsById : $skripsisById)->get((int) $docId)
                : null;

            $similarityPercent = $item['similarity_persen']
                ?? $item['similarity_percent']
                ?? null;

            if ($similarityPercent === null && isset($item['similarity_score']) && is_numeric($item['similarity_score'])) {
                $similarityPercent = round(((float) $item['similarity_score']) * 100, 1);
            }

            if (is_string($similarityPercent)) {
                $similarityPercent = (float) str_replace('%', '', $similarityPercent);
            }

            $item['document_id'] = $record->id ?? $docId;
            $item['skripsi_id'] = ! $isInternship ? ($record->id ?? $docId) : null;
            $item['document_type'] = $type;
            $item['judul'] = $record->title ?? ($item['judul'] ?? $item['title'] ?? 'Data tidak ditemukan');
            $item['nama_mahasiswa'] = $record->author_name ?? ($item['nama_mahasiswa'] ?? $item['author_name'] ?? 'Tidak diketahui');
            $item['student_id'] = $record->student_id ?? null;
            $item['similarity_persen'] = is_numeric($similarityPercent) ? round((float) $similarityPercent, 1) : 0.0;
            $item['is_local_record_found'] = $record !== null;

            if (isset($item['level']) && is_string($item['level'])) {
                $item['level'] = strtoupper($item['level']);
            }

            return $item;
        }, $result['results']);

        return $result;
    }
}
