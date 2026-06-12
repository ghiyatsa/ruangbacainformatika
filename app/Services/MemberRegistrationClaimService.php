<?php

namespace App\Services;

use App\Models\MemberRegistrationClaim;
use App\Models\User;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class MemberRegistrationClaimService
{
    public const TOKEN_PREFIX = 'rb-link-';

    /**
     * @param  array{name: string, email: string, whatsapp: string, address: string}  $attributes
     * @return array{registration: MemberRegistrationClaim, token: string, link_url: string, qr_svg: string}
     */
    public function create(array $attributes): array
    {
        $token = self::TOKEN_PREFIX.Str::lower(Str::random(40));

        $claim = MemberRegistrationClaim::query()->create([
            ...$attributes,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(3),
            'status' => MemberRegistrationClaim::STATUS_PENDING,
        ]);

        $claimUrl = route('account-links.show', ['token' => $token], absolute: true);

        return [
            'registration' => $claim,
            'token' => $token,
            'link_url' => $claimUrl,
            'qr_svg' => $this->generateQrSvg($claimUrl),
        ];
    }

    public function findByToken(string $token): ?MemberRegistrationClaim
    {
        return $this->normalizeClaim(
            MemberRegistrationClaim::query()
                ->where('token_hash', hash('sha256', $token))
                ->first(),
        );
    }

    public function findById(int $id): ?MemberRegistrationClaim
    {
        return $this->normalizeClaim(MemberRegistrationClaim::query()->find($id));
    }

    public function consume(string $token, string $googleId, User $user): MemberRegistrationClaim
    {
        $claim = $this->findByToken($token);

        if (! $claim || $claim->status !== MemberRegistrationClaim::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'link' => 'Permintaan penautan akun tidak ditemukan atau sudah digunakan.',
            ]);
        }

        if ($claim->isExpired()) {
            $claim->markAsExpired();

            throw ValidationException::withMessages([
                'link' => 'Permintaan penautan akun sudah kedaluwarsa. Silakan daftar ulang di kiosk.',
            ]);
        }

        $user->forceFill([
            'google_id' => $googleId,
            'name' => $claim->name,
            'whatsapp' => $claim->whatsapp,
            'address' => $claim->address,
        ])->save();

        $claim->forceFill([
            'status' => $user->requiresWhatsAppVerification()
                ? MemberRegistrationClaim::STATUS_LINKED
                : MemberRegistrationClaim::STATUS_CLAIMED,
            'claimed_at' => $user->requiresWhatsAppVerification() ? null : now(),
            'last_error_message' => null,
            'last_error_at' => null,
            'user_id' => $user->id,
        ])->save();

        return $claim;
    }

    public function recordAttemptError(string $token, string $message): void
    {
        $claim = $this->findByToken($token);

        if (! $claim || $claim->status !== MemberRegistrationClaim::STATUS_PENDING) {
            return;
        }

        $claim->recordAttemptError($message);
    }

    public function clearAttemptError(string $token): void
    {
        $claim = $this->findByToken($token);

        if (! $claim || $claim->status !== MemberRegistrationClaim::STATUS_PENDING) {
            return;
        }

        $claim->clearAttemptError();
    }

    public function markLinkedClaimAsVerified(User $user): void
    {
        $claim = MemberRegistrationClaim::query()
            ->whereBelongsTo($user)
            ->where('status', MemberRegistrationClaim::STATUS_LINKED)
            ->latest('id')
            ->first();

        if (! $claim) {
            return;
        }

        $claim->forceFill([
            'status' => MemberRegistrationClaim::STATUS_CLAIMED,
            'claimed_at' => now(),
            'last_error_message' => null,
            'last_error_at' => null,
        ])->save();
    }

    /**
     * @param  array{id?: int}  $presentedClaim
     */
    public function cancelPresentedClaim(array $presentedClaim): void
    {
        $claimId = $presentedClaim['id'] ?? null;

        if (! is_int($claimId)) {
            return;
        }

        $claim = $this->findById($claimId);

        if ($claim === null || $claim->status !== MemberRegistrationClaim::STATUS_PENDING) {
            return;
        }

        $claim->markAsExpired();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     whatsapp: string,
     *     address: string,
     *     linkUrl: string,
     *     qrSvg: string,
     *     status: string,
     *     expiresAt: string,
     *     claimedAt: string|null,
     *     lastErrorMessage: string|null,
     *     lastErrorAt: string|null,
     *     approvalPending: bool
     * }
     */
    public function present(MemberRegistrationClaim $claim, string $claimUrl, string $qrSvg): array
    {
        return [
            'id' => $claim->id,
            'name' => $claim->name,
            'email' => $claim->email,
            'whatsapp' => $claim->whatsapp,
            'address' => $claim->address,
            'linkUrl' => $claimUrl,
            'qrSvg' => $qrSvg,
            'status' => $claim->status,
            'expiresAt' => $claim->expires_at->toIso8601String(),
            'claimedAt' => $claim->claimed_at?->toIso8601String(),
            'lastErrorMessage' => $claim->last_error_message,
            'lastErrorAt' => $claim->last_error_at?->toIso8601String(),
            'approvalPending' => $claim->user?->requiresManualApproval() ?? false,
        ];
    }

    /**
     * @param  array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     whatsapp: string,
     *     address: string,
     *     linkUrl: string,
     *     qrSvg: string,
     *     status?: string,
     *     expiresAt?: string,
     *     claimedAt?: string|null,
     *     lastErrorMessage?: string|null,
     *     lastErrorAt?: string|null,
     *     approvalPending?: bool
     * }  $presentedClaim
     * @return array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     whatsapp: string,
     *     address: string,
     *     linkUrl: string,
     *     qrSvg: string,
     *     status: string,
     *     expiresAt: string,
     *     claimedAt: string|null,
     *     lastErrorMessage: string|null,
     *     lastErrorAt: string|null,
     *     approvalPending: bool
     * }|null
     */
    public function syncPresentedClaim(array $presentedClaim): ?array
    {
        $claim = $this->findById($presentedClaim['id']);

        if (! $claim) {
            return null;
        }

        $qrSvg = $presentedClaim['qrSvg'];

        if (
            ! str_contains($qrSvg, 'var(--foreground)') ||
            ! str_contains($qrSvg, 'var(--background)') ||
            str_contains($qrSvg, '#111827') ||
            str_contains($qrSvg, '#ffffff')
        ) {
            $qrSvg = $this->generateQrSvg($presentedClaim['linkUrl']);
        }

        if (
            $claim->status === MemberRegistrationClaim::STATUS_LINKED
            && $claim->user instanceof User
            && ! $claim->user->requiresWhatsAppVerification()
        ) {
            $this->markLinkedClaimAsVerified($claim->user);
            $claim = $claim->fresh();
        }

        return [
            ...$presentedClaim,
            'qrSvg' => $qrSvg,
            'status' => $claim->status,
            'expiresAt' => $claim->expires_at->toIso8601String(),
            'claimedAt' => $claim->claimed_at?->toIso8601String(),
            'lastErrorMessage' => $claim->last_error_message,
            'lastErrorAt' => $claim->last_error_at?->toIso8601String(),
            'approvalPending' => $claim->user?->requiresManualApproval() ?? false,
        ];
    }

    protected function generateQrSvg(string $payload): string
    {
        try {
            $svg = (new Writer(
                new ImageRenderer(
                    new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(17, 24, 39))),
                    new SvgImageBackEnd
                )
            ))->writeString($payload);
        } catch (Throwable $exception) {
            throw new RuntimeException('QR penautan akun gagal dibuat.', previous: $exception);
        }

        $svg = trim(substr($svg, strpos($svg, "\n") + 1));

        $svg = (string) preg_replace('/<rect\b([^>]*)fill="#ffffff"([^>]*)><\/rect>/i', '<rect$1fill="var(--background)"$2></rect>', $svg);
        $svg = (string) preg_replace('/<path\b([^>]*)fill="#111827"([^>]*)>/i', '<path$1fill="var(--foreground)"$2>', $svg);
        $svg = (string) preg_replace('/fill="#ffffff"/i', 'fill="var(--background)"', $svg);
        $svg = (string) preg_replace('/fill="#111827"/i', 'fill="var(--foreground)"', $svg);

        return str_replace('<svg ', '<svg color="var(--foreground)" ', $svg);
    }

    protected function normalizeClaim(?MemberRegistrationClaim $claim): ?MemberRegistrationClaim
    {
        if (! $claim) {
            return null;
        }

        if ($claim->status === MemberRegistrationClaim::STATUS_PENDING && $claim->isExpired()) {
            $claim->markAsExpired();
        }

        return $claim->fresh();
    }
}
