<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/books', CatalogController::class)->name('books.index');
Route::get('/books/{book:slug}', [BookController::class, 'show'])->name('books.show');

require __DIR__.'/kiosk.php';
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
