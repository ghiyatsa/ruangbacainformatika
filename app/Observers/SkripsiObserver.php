<?php

namespace App\Observers;

use App\Models\Skripsi;
use App\Services\SimilarityApiService;

class SkripsiObserver
{
    public function __construct(
        private readonly SimilarityApiService $api
    ) {}

    private function payload(Skripsi $skripsi): array
    {
        return [
            'laravel_id' => $skripsi->id,
            'judul' => $skripsi->title,
            'abstrak' => $skripsi->abstract,
            'kata_kunci' => $skripsi->keywords,
            'tahun' => $skripsi->year,
            'program_studi' => null, // ruangbaca doesn't have program_studi
            'nim' => $skripsi->student_id,
            'nama_mahasiswa' => $skripsi->author_name,
        ];
    }

    public function created(Skripsi $skripsi): void
    {
        dispatch(function () use ($skripsi) {
            $this->api->upsert($this->payload($skripsi));
        })->afterResponse();
    }

    public function updated(Skripsi $skripsi): void
    {
        dispatch(function () use ($skripsi) {
            $this->api->upsert($this->payload($skripsi));
        })->afterResponse();
    }

    public function deleted(Skripsi $skripsi): void
    {
        dispatch(function () use ($skripsi) {
            $this->api->delete($skripsi->id);
        })->afterResponse();
    }
}
