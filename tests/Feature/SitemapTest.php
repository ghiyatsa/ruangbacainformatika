<?php

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Post;
use App\Models\Skripsi;
use App\Models\StaticPage;
use App\Models\Thesis;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\get;

it('generates the sitemap correctly', function () {
    Queue::fake();
    // Seed test records
    $staticPage = StaticPage::factory()->create([
        'title' => 'Custom Page',
        'slug' => 'custom-page',
        'is_active' => true,
    ]);

    $book = Book::factory()->published()->create([
        'title' => 'Sample Book Title',
        'slug' => 'sample-book-title',
    ]);

    $post = Post::factory()->published()->create([
        'title' => 'Sample Blog Post',
        'slug' => 'sample-blog-post',
    ]);

    $skripsi = Skripsi::factory()->create([
        'student_id' => '10203040',
        'title' => 'Sample Skripsi Title',
    ]);

    $report = InternshipReport::factory()->create([
        'student_id' => '20304050',
        'title' => 'Sample Internship Report Title',
    ]);

    $thesis = Thesis::factory()->create([
        'student_id' => '30405060',
        'title' => 'Sample Thesis Title',
    ]);

    // Send request to /sitemap.xml
    $response = get(route('sitemap'))
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'application/xml');

    $content = $response->getContent();

    // Verify main landing pages exist
    expect($content)->toContain(route('home'))
        ->toContain(route('books.index'))
        ->toContain(route('blog.index'))
        ->toContain(route('skripsi.index'))
        ->toContain(route('internship-reports.index'))
        ->toContain(route('thesis.index'))
        ->toContain(route('about-team'))
        ->toContain(route('contact'));

    // Verify dynamic model pages exist
    expect($content)->toContain($staticPage->publicUrl())
        ->toContain(route('books.show', $book->slug))
        ->toContain(route('blog.show', $post->slug))
        ->toContain(route('skripsi.show', $skripsi->student_id))
        ->toContain(route('internship-reports.show', $report->student_id))
        ->toContain(route('thesis.show', $thesis->student_id));
});
