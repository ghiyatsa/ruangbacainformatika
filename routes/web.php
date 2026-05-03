<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SimilarityController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/books', CatalogController::class)->name('books.index');
Route::get('/books/categories/{category:slug}', [CategoryController::class, 'show'])->name('books.categories.show');
Route::get('/books/{book:slug}', [BookController::class, 'show'])->name('books.show');
Route::get('/similarity', [SimilarityController::class, 'index'])->name('similarity.index');
Route::post('/similarity/check', [SimilarityController::class, 'check'])->name('similarity.check');
require __DIR__.'/kiosk.php';
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
