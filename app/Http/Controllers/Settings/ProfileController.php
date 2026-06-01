<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileOnboardingRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use App\Services\Auth\AuthenticationRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile');
    }

    public function initiateWhatsAppChange(Request $request): RedirectResponse
    {
        $request->session()->put('allow_whatsapp_change', true);

        return to_route('register.whatsapp');
    }

    public function complete(Request $request): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User) {
            return to_route('settings.profile.edit');
        }

        if ($user->requiresWhatsAppVerification()) {
            return to_route('register.whatsapp');
        }

        if ($user->hasRequiredProfileDetails()) {
            return to_route('settings.profile.edit');
        }

        if (! $this->authenticationRedirector->requiresProfileCompletion($user)) {
            return $user->canAccessAdminPanel()
                ? to_route('filament.admin.pages.dashboard')
                : to_route('home');
        }

        return Inertia::render('auth/register-profile');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $originalWhatsapp = $user->whatsapp;

        $user->fill($request->validated());
        $user->save();

        if ($user->hasRequiredProfileDetails() && ! $user->hasCompletedProfile()) {
            $user->markProfileAsCompleted();
        }

        if ($originalWhatsapp !== $user->whatsapp && $user->requiresWhatsAppVerification()) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => 'Nomor WhatsApp diperbarui. Verifikasi ulang diperlukan.',
            ]);

            return to_route('register.whatsapp');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('settings.profile.edit');
    }

    public function storeOnboarding(ProfileOnboardingRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->authenticationRedirector->requiresProfileCompletion($user)) {
            return $user->canAccessAdminPanel()
                ? to_route('filament.admin.pages.dashboard')
                : to_route('home');
        }

        if ($user->hasRequiredProfileDetails() && ! $user->requiresWhatsAppVerification()) {
            return to_route('settings.profile.edit');
        }

        $user->forceFill([
            'name' => $request->validated('name'),
            'whatsapp' => $request->validated('whatsapp'),
            'address' => $request->validated('address'),
        ]);
        $user->markProfileAsCompleted();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Onboarding selesai.')]);

        return $user->canAccessAdminPanel()
            ? to_route('filament.admin.pages.dashboard')
            : to_route('settings.profile.edit');
    }
}
