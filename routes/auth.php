<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\MemberRegistrationClaimController;
use App\Http\Controllers\Auth\WhatsAppVerificationController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::middleware('guest')->group(function () {
    Route::get('/register', fn () => redirect()->route('auth.google'))->name('register');

    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::post('auth/google/popup-session', [GoogleController::class, 'setPopupSession'])->name('auth.google.set-popup');
    Route::post('auth/google/one-tap', [GoogleController::class, 'handleOneTap'])->name('auth.google.one-tap');
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::get('account-link/{token}', [MemberRegistrationClaimController::class, 'show'])->name('account-links.show');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('register/profile', [ProfileController::class, 'complete'])->name('register.profile');
    Route::patch('register/profile', [ProfileController::class, 'storeOnboarding'])->name('register.profile.store');
    Route::get('register/whatsapp', [WhatsAppVerificationController::class, 'show'])->name('register.whatsapp');
    Route::post('register/whatsapp/send', [WhatsAppVerificationController::class, 'send'])->name('register.whatsapp.send');
    Route::post('register/whatsapp/verify', [WhatsAppVerificationController::class, 'verify'])->name('register.whatsapp.verify');

    Route::redirect('/profile', '/settings/profile')->name('settings.profile.show');
});
