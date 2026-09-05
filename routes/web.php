<?php

use App\Http\Controllers\AcademicFileController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CatalogBookmarkController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CatalogReportController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DocumentDistributionController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InternshipReportController;
use App\Http\Controllers\LoanHistoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpenGraphImageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SimilarityController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SkripsiController;
use App\Http\Controllers\ThesisController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/favicon.svg', [FaviconController::class, 'svg'])->name('favicon.svg');
Route::get('/og/site', [OpenGraphImageController::class, 'site'])->name('og.site');
Route::get('/og/books/{book:slug}', [OpenGraphImageController::class, 'book'])->name('og.books.show');
Route::get('/og/skripsi/{skripsi:student_id}', [OpenGraphImageController::class, 'skripsi'])->name('og.skripsi.show');
Route::get('/og/internship-reports/{internshipReport:student_id}', [OpenGraphImageController::class, 'internshipReport'])->name('og.internship-reports.show');
Route::get('/og/thesis/{thesis:student_id}', [OpenGraphImageController::class, 'thesis'])->name('og.thesis.show');
Route::get('/books', CatalogController::class)->name('books.index');
Route::get('/books/{book:slug}', [BookController::class, 'show'])->name('books.show');
Route::get('/posts', [BlogController::class, 'index'])->name('blog.index');
Route::get('/posts/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/preview/posts/{post:preview_token}', [BlogController::class, 'preview'])->name('blog.preview');
Route::post('/catalog-reports', [CatalogReportController::class, 'store'])
    ->middleware('throttle:catalog-reports')
    ->name('catalog-reports.store');

Route::get('/verify/receipt/{token}', [DocumentDistributionController::class, 'verifyReceipt'])->name('verify.receipt');
Route::get('/distribution/receipt', [DocumentDistributionController::class, 'receipt'])->name('distribution.receipt');

Route::middleware(['auth', 'profile.completed', 'member'])->group(function () {
    Route::get('/skripsi', [SkripsiController::class, 'index'])->name('skripsi.index');
    Route::get('/skripsi/{skripsi:student_id}', [SkripsiController::class, 'show'])->name('skripsi.show');
    Route::get('/skripsi/{skripsi:student_id}/file', [AcademicFileController::class, 'skripsi'])->name('skripsi.file');

    Route::get('/internship-reports', [InternshipReportController::class, 'index'])->name('internship-reports.index');
    Route::get('/internship-reports/{internshipReport:student_id}', [InternshipReportController::class, 'show'])->name('internship-reports.show');
    Route::get('/internship-reports/{internshipReport:student_id}/file', [AcademicFileController::class, 'internshipReport'])->name('internship-reports.file');

    Route::get('/thesis', [ThesisController::class, 'index'])->name('thesis.index');
    Route::get('/thesis/{thesis:student_id}', [ThesisController::class, 'show'])->name('thesis.show');
    Route::get('/thesis/{thesis:student_id}/file', [AcademicFileController::class, 'thesis'])->name('thesis.file');
});

Route::get('/search/suggestions', [SearchController::class, 'suggestions'])
    ->middleware('throttle:search-suggestions')
    ->name('search.suggestions');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/about/team', [PageController::class, 'aboutTeam'])->name('about-team');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:contact-messages')
    ->name('contact.store');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-of-service', [PageController::class, 'termsOfService'])->name('terms-of-service');
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])
    ->middleware('cache.headers:public;max_age=86400;etag')
    ->name('sitemap');
Route::middleware('auth')->group(function () {
    Route::get('/similarity', [SimilarityController::class, 'index'])->name('similarity.index');
    Route::post('/similarity/check', [SimilarityController::class, 'check'])
        ->middleware('throttle:similarity-check')
        ->name('similarity.check');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/bookmarks', [CatalogBookmarkController::class, 'index'])
        ->name('catalog-bookmarks.index');
    Route::put('/bookmarks', [CatalogBookmarkController::class, 'replace'])
        ->middleware('throttle:catalog-bookmarks')
        ->name('catalog-bookmarks.replace');
    Route::get('/notifications/page', [NotificationController::class, 'page'])->name('notifications.page');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::post('/posts/{post:slug}/comments', [PostCommentController::class, 'store'])
        ->middleware('throttle:blog-comments')
        ->name('blog.comments.store');
    Route::delete('/posts/comments/{comment}', [PostCommentController::class, 'destroy'])->name('blog.comments.destroy');
});

Route::middleware(['auth', 'profile.completed'])->group(function () {
    Route::get('/loans/history', LoanHistoryController::class)->name('loans.history');
});

// Serve berkas dokumen submission (PDF/gambar) — hanya untuk pemilik atau staff/admin.
// Berkas disimpan di disk 'documents' (private) — tidak pernah diakses langsung via URL publik.
Route::middleware('auth')->group(function () {
    Route::get('/documents/{submission}/{field}', [DocumentFileController::class, 'show'])
        ->where('field', 'document|endorsement')
        ->name('documents.file');
});

require __DIR__.'/kiosk.php';
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
