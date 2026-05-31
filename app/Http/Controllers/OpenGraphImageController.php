<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
use App\Support\OpenGraphImage;
use Illuminate\Http\Response;

class OpenGraphImageController extends Controller
{
    public function __construct(
        protected OpenGraphImage $openGraphImage,
    ) {}

    public function site(): Response
    {
        return $this->svgResponse($this->openGraphImage->renderSite());
    }

    public function book(Book $book): Response
    {
        $book->loadMissing('authors:id,name');

        return $this->svgResponse($this->openGraphImage->renderCatalogDetail(
            label: 'Katalog Buku',
            title: $book->title,
            author: $book->authors->pluck('name')->filter()->implode(', ') ?: 'Ruang Baca Informatika',
        ));
    }

    public function skripsi(Skripsi $skripsi): Response
    {
        return $this->svgResponse($this->openGraphImage->renderCatalogDetail(
            label: 'Skripsi',
            title: $skripsi->title,
            author: $skripsi->author_name,
        ));
    }

    public function thesis(Thesis $thesis): Response
    {
        return $this->svgResponse($this->openGraphImage->renderCatalogDetail(
            label: 'Tesis',
            title: $thesis->title,
            author: $thesis->author_name,
        ));
    }

    public function internshipReport(InternshipReport $internshipReport): Response
    {
        return $this->svgResponse($this->openGraphImage->renderCatalogDetail(
            label: 'Laporan Kerja Praktik',
            title: $internshipReport->title,
            author: $internshipReport->author_name,
        ));
    }

    protected function svgResponse(string $svg): Response
    {
        return response($svg, 200, [
            'Content-Type' => OpenGraphImage::MIME_TYPE,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
