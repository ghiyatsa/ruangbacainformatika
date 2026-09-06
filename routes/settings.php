<?php

use App\Http\Controllers\Settings\MemberKeyController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/profile');
    Route::redirect('settings/profile', '/profile');

    Route::get('profile', [ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::post('profile/change-whatsapp', [ProfileController::class, 'initiateWhatsAppChange'])->name('settings.profile.change-whatsapp');
    Route::post('profile/whatsapp/send', [ProfileController::class, 'sendWhatsAppOtp'])->name('settings.profile.whatsapp.send');
    Route::post('profile/whatsapp/verify', [ProfileController::class, 'verifyWhatsAppOtp'])->name('settings.profile.whatsapp.verify');
    Route::patch('profile', [ProfileController::class, 'update'])->name('settings.profile.update');

    Route::middleware(['profile.completed'])->group(function () {
        Route::redirect('settings/member-key', '/member-qr');
        Route::redirect('settings/member-qr', '/member-qr');
        Route::get('member-qr', [MemberKeyController::class, 'show'])->name('settings.member-key.show');
        Route::post('member-qr', [MemberKeyController::class, 'generate'])->name('settings.member-key.generate');
    });
});
