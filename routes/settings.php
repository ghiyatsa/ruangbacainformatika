<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'profile.completed'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::post('settings/profile/change-whatsapp', [ProfileController::class, 'initiateWhatsAppChange'])->name('settings.profile.change-whatsapp');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('settings.profile.update');
    Route::get('settings/security', [SecurityController::class, 'edit'])->name('settings.security.edit');
});
