<?php

namespace App\Models;

use App\Notifications\Auth\VerifyEmailOtpNotification;
use App\Support\LoanConsequenceService;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'verified_user_agent_hash',
    'password',
    'auth_provider',
    'whatsapp',
    'address',
    'profile_completed_at',
    'is_approved',
])]

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::updating(function (User $user) {
            if ($user->isDirty('email') && $user->getOriginal('email') !== null) {
                $user->email = $user->getOriginal('email');
            }
        });
    }

    public function isMahasiswa(): bool
    {
        return str_ends_with($this->email, '@mhs.unimal.ac.id');
    }

    public function nim(): string
    {
        if (! $this->isMahasiswa()) {
            return '-';
        }

        return substr(Str::before($this->email, '@'), -9);
    }

    public function isDosen(): bool
    {
        return str_ends_with($this->email, '@unimal.ac.id');
    }

    public function usesGoogleAuth(): bool
    {
        return $this->auth_provider === 'google';
    }

    public function hasAdministrativeRole(): bool
    {
        return $this->hasRole(['super_admin', 'staff']);
    }

    public function shouldReceiveMemberRole(): bool
    {
        return ! $this->hasAdministrativeRole() && ! $this->hasRole('member');
    }

    public function assignMemberRoleIfAvailable(): void
    {
        if (! Role::query()->where('name', 'member')->exists()) {
            return;
        }

        if (! $this->shouldReceiveMemberRole()) {
            return;
        }

        $this->assignRole('member');
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->hasVerifiedEmail() && $this->hasAdministrativeRole();
    }

    public static function userAgentFingerprint(?string $userAgent): ?string
    {
        $normalizedUserAgent = Str::of((string) $userAgent)
            ->trim()
            ->squish()
            ->toString();

        if ($normalizedUserAgent === '') {
            return null;
        }

        return hash('sha256', $normalizedUserAgent);
    }

    public function shouldTrustCurrentUserAgent(?string $userAgent): bool
    {
        return $this->hasVerifiedEmail()
            && blank($this->verified_user_agent_hash)
            && filled(static::userAgentFingerprint($userAgent));
    }

    public function requiresEmailVerificationForUserAgent(?string $userAgent): bool
    {
        if (! $this->hasVerifiedEmail()) {
            return false;
        }

        $fingerprint = static::userAgentFingerprint($userAgent);

        if ($fingerprint === null || blank($this->verified_user_agent_hash)) {
            return false;
        }

        return ! hash_equals((string) $this->verified_user_agent_hash, $fingerprint);
    }

    public function trustUserAgent(?string $userAgent): void
    {
        $fingerprint = static::userAgentFingerprint($userAgent);

        if ($fingerprint === null) {
            return;
        }

        $this->forceFill([
            'verified_user_agent_hash' => $fingerprint,
        ])->saveQuietly();
    }

    public function hasRequiredProfileDetails(): bool
    {
        return filled($this->whatsapp) && filled($this->address);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function scopeBorrowingRestricted(Builder $query): Builder
    {
        $loanConsequenceService = app(LoanConsequenceService::class);

        if (! $loanConsequenceService->lateReturnSuspensionEnabled()) {
            return $query->whereRaw('1 = 0');
        }

        $thresholdDays = $loanConsequenceService->lateReturnSuspendAfterDays();
        $cooldownDays = $loanConsequenceService->lateReturnCooldownDays();

        return $query->where(function (Builder $restrictedQuery) use ($cooldownDays, $thresholdDays): void {
            $restrictedQuery->whereHas('loans', function (Builder $loanQuery) use ($thresholdDays): void {
                $loanQuery
                    ->where('status', Loan::STATUS_BORROWED)
                    ->whereNotNull('due_at')
                    ->where('due_at', '<=', now()->subDays($thresholdDays));
            });

            if ($cooldownDays > 0) {
                $restrictedQuery->orWhereHas('loans', function (Builder $loanQuery) use ($cooldownDays, $thresholdDays): void {
                    $loanQuery
                        ->where('status', Loan::STATUS_RETURNED)
                        ->whereNotNull('due_at')
                        ->whereNotNull('returned_at')
                        ->where('returned_at', '>', now()->subDays($cooldownDays));

                    if ($loanQuery->getConnection()->getDriverName() === 'sqlite') {
                        $loanQuery->whereRaw('julianday(returned_at) - julianday(due_at) >= ?', [$thresholdDays]);
                    } else {
                        $loanQuery->whereRaw('TIMESTAMPDIFF(DAY, due_at, returned_at) >= ?', [$thresholdDays]);
                    }
                });
            }
        });
    }

    public function loanDrafts(): HasMany
    {
        return $this->hasMany(LoanDraft::class);
    }

    public function loanItems(): HasManyThrough
    {
        return $this->hasManyThrough(LoanItem::class, Loan::class);
    }

    public function hasCompletedProfile(): bool
    {
        return $this->profile_completed_at !== null;
    }

    public function markProfileAsCompleted(): void
    {
        if ($this->hasCompletedProfile()) {
            return;
        }

        $this->forceFill([
            'profile_completed_at' => now(),
        ])->save();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return true;
        }

        return $this->canAccessAdminPanel();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailOtpNotification);
    }
}
