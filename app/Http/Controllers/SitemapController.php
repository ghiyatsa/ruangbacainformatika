<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\StaticPage;
use App\Models\Thesis;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate the XML sitemap.
     */
    public function index(): Response
    {
        $urls = [];

        // 1. Static/Main Landing pages
        $urls[] = [
            'loc' => route('home'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];
        $urls[] = [
            'loc' => route('books.index'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];
        $urls[] = [
            'loc' => route('skripsi.index'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];
        $urls[] = [
            'loc' => route('internship-reports.index'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];
        $urls[] = [
            'loc' => route('thesis.index'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];
        $urls[] = [
            'loc' => route('about-team'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];
        $urls[] = [
            'loc' => route('contact'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];

        // 2. Active Static Pages from DB
        $staticPages = StaticPage::active()->get();
        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => $page->publicUrl(),
                'lastmod' => $page->updated_at->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => $page->isSystemPage() ? '0.7' : '0.6',
            ];
        }

        // 3. Books (Only Published)
        $books = Book::published()->select('id', 'slug', 'updated_at')->get();
        foreach ($books as $book) {
            $urls[] = [
                'loc' => route('books.show', $book->slug),
                'lastmod' => $book->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // 4. Skripsi
        $skripsis = Skripsi::select('id', 'student_id', 'updated_at')->get();
        foreach ($skripsis as $skripsi) {
            $urls[] = [
                'loc' => route('skripsi.show', $skripsi->student_id),
                'lastmod' => $skripsi->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // 5. Internship Reports
        $reports = InternshipReport::select('id', 'student_id', 'updated_at')->get();
        foreach ($reports as $report) {
            $urls[] = [
                'loc' => route('internship-reports.show', $report->student_id),
                'lastmod' => $report->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        // 6. Thesis
        $theses = Thesis::select('id', 'student_id', 'updated_at')->get();
        foreach ($theses as $thesis) {
            $urls[] = [
                'loc' => route('thesis.show', $thesis->student_id),
                'lastmod' => $thesis->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $url) {
            $xml .= '    <url>'."\n";
            $xml .= '        <loc>'.htmlspecialchars($url['loc']).'</loc>'."\n";
            $xml .= '        <lastmod>'.$url['lastmod'].'</lastmod>'."\n";
            $xml .= '        <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
            $xml .= '        <priority>'.$url['priority'].'</priority>'."\n";
            $xml .= '    </url>'."\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
