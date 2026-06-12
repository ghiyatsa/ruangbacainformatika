<?php

use App\Http\Controllers\KioskController;
use App\Http\Controllers\KioskLoanDraftController;
use App\Http\Controllers\KioskReturnDraftController;
use Illuminate\Support\Facades\Route;

Route::middleware('kiosk.network')->group(function () {
    Route::get('/kiosk', [KioskController::class, 'show'])->name('kiosk.index');
    Route::post('/kiosk/visits', [KioskController::class, 'store'])
        ->middleware(['kiosk.pin', 'throttle:kiosk-submit'])
        ->name('kiosk.visits.store');
    Route::post('/kiosk/loans/borrow', [KioskController::class, 'borrow'])
        ->middleware(['kiosk.pin', 'throttle:kiosk-submit'])
        ->name('kiosk.loans.borrow');
    Route::post('/kiosk/loans/drafts/consume', [KioskLoanDraftController::class, 'store'])
        ->middleware(['kiosk.pin', 'throttle:kiosk-consume'])
        ->name('kiosk.loan-drafts.consume');
    Route::post('/kiosk/loans/return-drafts/consume', [KioskReturnDraftController::class, 'store'])
        ->middleware(['kiosk.pin', 'throttle:kiosk-consume'])
        ->name('kiosk.return-drafts.consume');
    Route::get('/kiosk/books/search', [KioskController::class, 'searchBooks'])
        ->middleware(['kiosk.pin', 'throttle:kiosk-book-search'])
        ->name('kiosk.books.search');
    Route::post('/kiosk/loans/return', [KioskController::class, 'storeReturn'])
        ->middleware(['kiosk.pin', 'throttle:kiosk-submit'])
        ->name('kiosk.loans.return');

    Route::post('/kiosk/members', [KioskController::class, 'storeMember'])
        ->middleware(['kiosk.pin', 'throttle:kiosk-submit'])
        ->name('kiosk.members.store');
    Route::get('/kiosk/members/status', [KioskController::class, 'memberRegistrationStatus'])
        ->middleware(['kiosk.pin', 'throttle:kiosk-member-status'])
        ->name('kiosk.members.status');
    Route::get('/kiosk/members/find', [KioskController::class, 'findMember'])
        ->middleware(['kiosk.pin', 'throttle:kiosk-member-lookup'])
        ->name('kiosk.members.find');
    Route::post('/kiosk/members/cancel', [KioskController::class, 'cancelMemberRegistration'])
        ->middleware('kiosk.pin')
        ->name('kiosk.members.cancel');
    Route::post('/kiosk/lock', [KioskController::class, 'lock'])
        ->name('kiosk.lock');

    Route::get('/kiosk/pin', function () {
        return redirect()->route('kiosk.index');
    });

    Route::post('/kiosk/pin', [KioskController::class, 'verifyPin'])
        ->middleware('throttle:kiosk-pin')
        ->name('kiosk.pin.store');
});
