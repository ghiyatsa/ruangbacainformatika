<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'profile.completed'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('settings.profile.update');
});

Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {
    Route::get('settings/security', [SecurityController::class, 'edit'])->name('settings.security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('settings.password.update');

    Route::delete('settings/sessions', [SecurityController::class, 'destroy'])->name('settings.sessions.destroy');

    Route::inertia('settings/appearance', 'settings/appearance')->name('settings.appearance.edit');
});
