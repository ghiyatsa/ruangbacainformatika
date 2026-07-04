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
    public function __construct(
        protected SimilarityApiService $api,
    ) {}

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

        $cacheKey = 'similarity_check_'.hash('sha256', mb_strtolower($title).'_'.($documentType ?? 'all'));
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

        $results = $result['results'];

        // Extract IDs and student IDs for each type
        $skripsiIds = [];
        $internshipIds = [];
        $skripsiNims = [];
        $internshipNims = [];

        foreach ($results as $item) {
            $type = $item['document_type'] ?? 'skripsi';
            $docId = $item['document_id'] ?? $item['skripsi_id'] ?? $item['id'] ?? null;
            $nim = $item['nim'] ?? $item['student_id'] ?? null;

            if ($type === 'internship_report') {
                if (is_numeric($docId)) {
                    $internshipIds[] = (int) $docId;
                }
                if ($nim) {
                    $internshipNims[] = $nim;
                }
            } else {
                if (is_numeric($docId)) {
                    $skripsiIds[] = (int) $docId;
                }
                if ($nim) {
                    $skripsiNims[] = $nim;
                }
            }
        }

        // Query Skripsi
        $skripsis = Skripsi::query()
            ->whereIn('id', $skripsiIds)
            ->orWhereIn('student_id', $skripsiNims)
            ->get(['id', 'title', 'author_name', 'student_id']);
        $skripsisById = $skripsis->keyBy('id');
        $skripsisByStudentId = $skripsis->whereNotNull('student_id')->keyBy('student_id');

        // Query InternshipReport
        $internships = InternshipReport::query()
            ->whereIn('id', $internshipIds)
            ->orWhereIn('student_id', $internshipNims)
            ->get(['id', 'title', 'author_name', 'student_id']);
        $internshipsById = $internships->keyBy('id');
        $internshipsByStudentId = $internships->whereNotNull('student_id')->keyBy('student_id');

        $result['results'] = array_map(function ($item) use ($skripsisById, $skripsisByStudentId, $internshipsById, $internshipsByStudentId) {
            $type = $item['document_type'] ?? 'skripsi';
            $docId = $item['document_id'] ?? $item['skripsi_id'] ?? $item['id'] ?? null;
            $studentId = $item['nim'] ?? $item['student_id'] ?? null;

            if ($type === 'internship_report') {
                $record = is_numeric($docId) ? $internshipsById->get((int) $docId) : null;
                if ($record === null && $studentId !== null) {
                    $record = $internshipsByStudentId->get($studentId);
                }
            } else {
                $record = is_numeric($docId) ? $skripsisById->get((int) $docId) : null;
                if ($record === null && $studentId !== null) {
                    $record = $skripsisByStudentId->get($studentId);
                }
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

            $item['document_id'] = $record?->id ?? $docId;
            $item['skripsi_id'] = $type === 'skripsi' ? ($record?->id ?? $docId) : null;
            $item['document_type'] = $type;
            $item['judul'] = $record?->title ?? ($item['judul'] ?? $item['title'] ?? 'Data tidak ditemukan');
            $item['nama_mahasiswa'] = $record?->author_name ?? ($item['nama_mahasiswa'] ?? $item['author_name'] ?? 'Tidak diketahui');
            $item['student_id'] = $record?->student_id ?? $studentId;
            $item['similarity_persen'] = $similarityPercent;
            $item['is_local_record_found'] = $record !== null;

            if (isset($item['level']) && is_string($item['level'])) {
                $item['level'] = strtoupper($item['level']);
            }

            return $item;
        }, $result['results']);

        return $result;
    }
}
