<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/catalog', CatalogController::class)->name('catalog');

require __DIR__.'/kiosk.php';
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
