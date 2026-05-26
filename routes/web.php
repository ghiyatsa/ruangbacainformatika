<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CatalogReportController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InternshipReportController;
use App\Http\Controllers\LoanHistoryController;
use App\Http\Controllers\LoanRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReturnDraftController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SimilarityController;
use App\Http\Controllers\SkripsiController;
use App\Http\Controllers\ThesisController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/books', CatalogController::class)->name('books.index');
Route::get('/books/{book:slug}', [BookController::class, 'show'])->name('books.show');
Route::post('/catalog-reports', [CatalogReportController::class, 'store'])->name('catalog-reports.store');
Route::get('/skripsi', [SkripsiController::class, 'index'])->name('skripsi.index');
Route::get('/skripsi/{skripsi:student_id}', [SkripsiController::class, 'show'])->name('skripsi.show');
Route::get('/internship-reports', [InternshipReportController::class, 'index'])->name('internship-reports.index');
Route::get('/internship-reports/{internshipReport:student_id}', [InternshipReportController::class, 'show'])->name('internship-reports.show');
Route::get('/thesis', [ThesisController::class, 'index'])->name('thesis.index');
Route::get('/thesis/{thesis:student_id}', [ThesisController::class, 'show'])->name('thesis.show');

Route::get('/search', SearchController::class)->name('search');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/about/team', [PageController::class, 'aboutTeam'])->name('about-team');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact.store');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-of-service', [PageController::class, 'termsOfService'])->name('terms-of-service');
Route::middleware('auth')->group(function () {
    Route::get('/similarity', [SimilarityController::class, 'index'])->name('similarity.index');
    Route::post('/similarity/check', [SimilarityController::class, 'check'])->name('similarity.check');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/loans/request', [LoanRequestController::class, 'show'])->name('loans.request');
    Route::post('/loans/request/books', [LoanRequestController::class, 'storeBook'])->name('loans.request.books.store');
    Route::delete('/loans/request/books/{book}', [LoanRequestController::class, 'destroyBook'])->name('loans.request.books.destroy');
    Route::post('/loans/request/qr', [LoanRequestController::class, 'generateQr'])->name('loans.request.qr');
});

Route::middleware(['auth', 'profile.completed'])->group(function () {
    Route::get('/loans/history', LoanHistoryController::class)->name('loans.history');
    Route::post('/loans/history/qr', [ReturnDraftController::class, 'generateQr'])->name('loans.history.qr');
});

require __DIR__.'/kiosk.php';
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
