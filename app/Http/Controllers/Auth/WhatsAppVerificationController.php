<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendWhatsAppOtpRequest;
use App\Http\Requests\Auth\VerifyWhatsAppOtpRequest;
use App\Models\User;
use App\Services\Auth\AuthenticationRedirector;
use App\Services\WhatsAppOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class WhatsAppVerificationController extends Controller
{
    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
        protected WhatsAppOtpService $whatsAppOtpService,
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $allowWhatsAppChange = $request->session()->get('allow_whatsapp_change') === true;

        if (! $user->usesCampusEmail()) {
            return to_route('home');
        }

        if (! $this->authenticationRedirector->requiresWhatsAppVerification($user) && ! $allowWhatsAppChange) {
            return $this->authenticationRedirector->requiresProfileCompletion($user)
                ? to_route('register.profile')
                : to_route('home');
        }

        $verification = $this->whatsAppOtpService->status($user);

        if (filled($user->whatsapp) && $user->requiresWhatsAppVerification()) {
            try {
                $verification = $this->whatsAppOtpService->ensureChallenge($user);
            } catch (RuntimeException $exception) {
                report($exception);

                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Kode belum dapat dikirim. Coba lagi.',
                ]);

                $verification = $this->whatsAppOtpService->status($user);
            }
        }

        return Inertia::render('auth/verify-whatsapp', [
            'verification' => $verification,
        ]);
    }

    public function send(SendWhatsAppOtpRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $allowWhatsAppChange = $request->session()->get('allow_whatsapp_change') === true;

        if (! $this->authenticationRedirector->requiresWhatsAppVerification($user) && ! $allowWhatsAppChange) {
            throw ValidationException::withMessages([
                'otp' => 'Verifikasi tidak tersedia untuk akun ini.',
            ]);
        }

        if ($request->filled('whatsapp')) {
            $newWhatsapp = $request->validated('whatsapp');

            if ($user->hasVerifiedWhatsApp() && $user->whatsapp === $newWhatsapp) {
                throw ValidationException::withMessages([
                    'whatsapp' => 'Nomor WhatsApp baru harus berbeda dengan nomor saat ini.',
                ]);
            }

            $user->forceFill([
                'whatsapp' => $newWhatsapp,
            ])->save();
        }

        try {
            $this->whatsAppOtpService->dispatch($user);
        } catch (RuntimeException $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'otp' => 'Kode belum dapat dikirim. Coba lagi beberapa saat lagi.',
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kode berhasil dikirim.',
        ]);

        return back();
    }

    public function verify(VerifyWhatsAppOtpRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $allowWhatsAppChange = $request->session()->get('allow_whatsapp_change') === true;

        if (! $this->authenticationRedirector->requiresWhatsAppVerification($user) && ! $allowWhatsAppChange) {
            throw ValidationException::withMessages([
                'otp' => 'Verifikasi tidak tersedia untuk akun ini.',
            ]);
        }

        $result = $this->whatsAppOtpService->verify(
            $user,
            (string) $request->validated('code'),
        );

        $request->session()->forget('allow_whatsapp_change');

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $result['approvalPending']
                ? 'Verifikasi selesai. Akun menunggu persetujuan admin.'
                : 'Verifikasi selesai.',
        ]);

        $freshUser = $user->fresh();

        if ($freshUser instanceof User && $this->authenticationRedirector->requiresProfileCompletion($freshUser)) {
            return to_route('register.profile');
        }

        return $this->authenticationRedirector->redirectResponse($request, $freshUser);
    }
}
