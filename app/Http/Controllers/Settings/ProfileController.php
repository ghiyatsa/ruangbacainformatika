<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileOnboardingRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function complete(Request $request): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User || $user->hasRequiredProfileDetails()) {
            return to_route('profile.edit');
        }

        return Inertia::render('auth/register-whatsapp');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->fill($request->validated());
        $user->save();

        if (filled($user->whatsapp) && ! $user->hasCompletedProfile()) {
            $user->markProfileAsCompleted();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    public function storeOnboarding(ProfileOnboardingRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Removal of Google auth requirement

        if ($user->hasRequiredProfileDetails()) {
            return to_route('profile.edit');
        }

        $user->forceFill([
            'whatsapp' => $request->validated('whatsapp'),
        ]);
        $user->markProfileAsCompleted();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Onboarding selesai.')]);

        return to_route('profile.edit');
    }
}
