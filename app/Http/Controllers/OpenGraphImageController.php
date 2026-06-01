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
        return $this->imageResponse($this->openGraphImage->renderSite());
    }

    public function book(Book $book): Response
    {
        $book->loadMissing('authors:id,name');

        return $this->imageResponse($this->openGraphImage->renderCatalogDetail(
            label: 'Katalog Buku',
            title: $book->title,
            author: $book->authors->pluck('name')->filter()->implode(', ') ?: 'Ruang Baca Informatika',
            views: $book->view_count,
        ));
    }

    public function skripsi(Skripsi $skripsi): Response
    {
        return $this->imageResponse($this->openGraphImage->renderCatalogDetail(
            label: 'Skripsi',
            title: $skripsi->title,
            author: $skripsi->author_name,
            views: $skripsi->view_count,
        ));
    }

    public function thesis(Thesis $thesis): Response
    {
        return $this->imageResponse($this->openGraphImage->renderCatalogDetail(
            label: 'Tesis',
            title: $thesis->title,
            author: $thesis->author_name,
            views: $thesis->view_count,
        ));
    }

    public function internshipReport(InternshipReport $internshipReport): Response
    {
        return $this->imageResponse($this->openGraphImage->renderCatalogDetail(
            label: 'Laporan Kerja Praktik',
            title: $internshipReport->title,
            author: $internshipReport->author_name,
            views: $internshipReport->view_count,
        ));
    }

    protected function imageResponse(string $content): Response
    {
        return response($content, 200, [
            'Content-Type' => OpenGraphImage::MIME_TYPE,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
