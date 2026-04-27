<?php

use App\Http\Controllers\KioskController;
use Illuminate\Support\Facades\Route;

Route::middleware('kiosk.device')->group(function () {
    Route::get('/kiosk', [KioskController::class, 'show'])->name('kiosk.index');
    Route::post('/kiosk/visits', [KioskController::class, 'store'])
        ->middleware('kiosk.pin')
        ->name('kiosk.visits.store');
    Route::post('/kiosk/loans/borrow', [KioskController::class, 'borrow'])
        ->middleware('kiosk.pin')
        ->name('kiosk.loans.borrow');
    Route::post('/kiosk/loans/return', [KioskController::class, 'storeReturn'])
        ->middleware('kiosk.pin')
        ->name('kiosk.loans.return');
});

Route::post('/kiosk/pin', [KioskController::class, 'verifyPin'])
    ->middleware('throttle:8,1')
    ->name('kiosk.pin.store');
