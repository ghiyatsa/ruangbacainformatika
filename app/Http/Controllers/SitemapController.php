<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Post;
use App\Models\StaticPage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate the XML sitemap, cached for 24 hours.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap:xml', 86400, fn () => $this->buildXml());

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    protected function buildXml(): string
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
            'loc' => route('blog.index'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.8',
        ];
        $urls[] = [
            'loc' => route('about'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5',
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

        // 4. Posts (Only Published)
        $posts = Post::published()->select('id', 'slug', 'updated_at')->get();
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
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

        return $xml;
    }
}
