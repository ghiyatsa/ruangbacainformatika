<?php

use App\Http\Controllers\KioskController;
use App\Http\Controllers\KioskLoanDraftController;
use Illuminate\Support\Facades\Route;

Route::middleware('kiosk.network')->group(function () {
    Route::get('/kiosk', [KioskController::class, 'show'])->name('kiosk.index');
    Route::post('/kiosk/visits', [KioskController::class, 'store'])
        ->middleware('kiosk.pin')
        ->name('kiosk.visits.store');
    Route::post('/kiosk/loans/borrow', [KioskController::class, 'borrow'])
        ->middleware('kiosk.pin')
        ->name('kiosk.loans.borrow');
    Route::post('/kiosk/loans/drafts/consume', [KioskLoanDraftController::class, 'store'])
        ->middleware('kiosk.pin')
        ->name('kiosk.loan-drafts.consume');
    Route::get('/kiosk/books/search', [KioskController::class, 'searchBooks'])
        ->middleware('kiosk.pin')
        ->name('kiosk.books.search');
    Route::post('/kiosk/loans/return', [KioskController::class, 'storeReturn'])
        ->middleware('kiosk.pin')
        ->name('kiosk.loans.return');

    Route::post('/kiosk/members', [KioskController::class, 'storeMember'])
        ->middleware('kiosk.pin')
        ->name('kiosk.members.store');
    Route::get('/kiosk/members/status', [KioskController::class, 'memberRegistrationStatus'])
        ->middleware('kiosk.pin')
        ->name('kiosk.members.status');
    Route::post('/kiosk/members/cancel', [KioskController::class, 'cancelMemberRegistration'])
        ->middleware('kiosk.pin')
        ->name('kiosk.members.cancel');

    Route::post('/kiosk/pin', [KioskController::class, 'verifyPin'])
        ->middleware('throttle:8,1')
        ->name('kiosk.pin.store');
});
