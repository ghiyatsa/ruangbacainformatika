<?php

use App\Http\Controllers\Api\Kiosk\KioskApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Kiosk
|--------------------------------------------------------------------------
|
| Endpoint JSON untuk klien kiosk non-browser (aplikasi Flutter desktop).
| Logika bisnis tetap di Action/Service yang sama dengan kiosk web.
|
| Autentikasi dua lapis:
|   1. kiosk.network — batasi berdasarkan CIDR jaringan perpustakaan
|   2. kiosk.device  — device token (header X-Kiosk-Device-Token)
|
| Aktivasi perangkat hanya butuh kiosk.network + rate limit kiosk-pin,
| karena tujuannya memang menukar PIN menjadi device token.
|
*/

Route::prefix('kiosk')->group(function (): void {
    // Aktivasi perangkat — tukar PIN menjadi device token.
    Route::post('devices/activate', [KioskApiController::class, 'activateDevice'])
        ->middleware(['kiosk.network', 'throttle:kiosk-pin'])
        ->name('api.kiosk.devices.activate');

    // Endpoint yang memerlukan device token.
    Route::middleware(['kiosk.network', 'kiosk.device'])->group(function (): void {
        Route::get('bootstrap', [KioskApiController::class, 'bootstrap'])
            ->name('api.kiosk.bootstrap');

        Route::post('lock', [KioskApiController::class, 'lock'])
            ->name('api.kiosk.lock');

        Route::post('visits', [KioskApiController::class, 'storeVisit'])
            ->middleware('throttle:kiosk-submit')
            ->name('api.kiosk.visits.store');

        Route::post('visits/member', [KioskApiController::class, 'storeMemberVisit'])
            ->middleware('throttle:kiosk-submit')
            ->name('api.kiosk.visits.member');

        Route::get('books/search', [KioskApiController::class, 'searchBooks'])
            ->middleware('throttle:kiosk-book-search')
            ->name('api.kiosk.books.search');

        Route::post('loans/borrow', [KioskApiController::class, 'borrow'])
            ->middleware('throttle:kiosk-submit')
            ->name('api.kiosk.loans.borrow');

        Route::post('loans/return', [KioskApiController::class, 'storeReturn'])
            ->middleware('throttle:kiosk-submit')
            ->name('api.kiosk.loans.return');

        Route::post('members', [KioskApiController::class, 'storeMember'])
            ->middleware('throttle:kiosk-submit')
            ->name('api.kiosk.members.store');

        Route::get('members/status', [KioskApiController::class, 'memberRegistrationStatus'])
            ->middleware('throttle:kiosk-member-status')
            ->name('api.kiosk.members.status');

        Route::post('members/cancel', [KioskApiController::class, 'cancelMemberRegistration'])
            ->name('api.kiosk.members.cancel');

        Route::get('members/find', [KioskApiController::class, 'findMember'])
            ->middleware('throttle:kiosk-member-lookup')
            ->name('api.kiosk.members.find');
    });
});
