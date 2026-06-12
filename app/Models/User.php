<?php

namespace App\Models;

use App\Support\CampusEmail;
use App\Support\LoanConsequenceService;
use App\Support\WhatsAppPhoneNumber;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'google_id',
    'avatar_url',
    'auth_provider',
    'whatsapp',
    'whatsapp_verified_at',
    'address',
    'profile_completed_at',
    'is_approved',
])]

#[Hidden(['remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'whatsapp_verified_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'is_approved' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $user->whatsapp = app(WhatsAppPhoneNumber::class)->normalize($user->whatsapp);
        });

        static::updating(function (User $user) {
            if ($user->isDirty('email') && $user->getOriginal('email') !== null) {
                $user->email = $user->getOriginal('email');
            }

            if ($user->isDirty('whatsapp') && $user->usesCampusEmail()) {
                $user->whatsapp_verified_at = null;
            }
        });

        static::saved(function (User $user): void {
            $user->assignMemberRoleIfAvailable();
        });
    }

    public function usesGoogleAuth(): bool
    {
        return $this->auth_provider === 'google';
    }

    public function avatarUrl(): ?string
    {
        return filled($this->avatar_url) ? $this->avatar_url : null;
    }

    public function hasAdministrativeRole(): bool
    {
        return $this->hasRole(['super_admin', 'staff']);
    }

    public function shouldReceiveMemberRole(): bool
    {
        return ! $this->hasAdministrativeRole()
            && ! $this->hasRole('member')
            && $this->canReceiveMemberRole();
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
        return $this->hasAdministrativeRole();
    }

    public function canReceiveMemberRole(): bool
    {
        return $this->is_approved
            && $this->hasVerifiedWhatsApp()
            && $this->usesCampusEmail();
    }

    public function canBorrowBooks(): bool
    {
        return $this->hasRole('member') && $this->canReceiveMemberRole();
    }

    public function canViewPublicNotifications(): bool
    {
        return $this->hasRole('member');
    }

    public function hasRequiredProfileDetails(): bool
    {
        return filled($this->whatsapp) && filled($this->address);
    }

    public function usesCampusEmail(): bool
    {
        return app(CampusEmail::class)->isEligibleEmail($this->email);
    }

    public function nim(): string
    {
        return app(CampusEmail::class)->extractIdentityNumber($this->email);
    }

    public function hasVerifiedWhatsApp(): bool
    {
        return $this->whatsapp_verified_at !== null;
    }

    public function requiresWhatsAppVerification(): bool
    {
        return $this->usesCampusEmail()
            && $this->is_approved
            && ! $this->hasVerifiedWhatsApp();
    }

    public function requiresManualApproval(): bool
    {
        return $this->usesCampusEmail()
            && ! app(CampusEmail::class)->shouldAutoApprove($this->email)
            && ! $this->is_approved;
    }

    public function scopePendingMemberApproval(Builder $query): Builder
    {
        return $query
            ->where('is_approved', false)
            ->where('email', 'like', '%@unimal.ac.id')
            ->where('email', 'not like', '%@mhs.unimal.ac.id');
    }

    public function routeNotificationForWhatsApp(): ?string
    {
        return filled($this->whatsapp) ? $this->whatsapp : null;
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

        return $query->where(function (Builder $restrictedQuery): void {
            $restrictedQuery->activeOverdueBorrowers();
            $restrictedQuery->orWhere(fn (Builder $cooldownQuery): Builder => $cooldownQuery->lateReturnCooldown());
        });
    }

    public function scopeActiveOverdueBorrowers(Builder $query): Builder
    {
        $loanConsequenceService = app(LoanConsequenceService::class);

        if (! $loanConsequenceService->lateReturnSuspensionEnabled()) {
            return $query->whereRaw('1 = 0');
        }

        $thresholdDays = $loanConsequenceService->lateReturnSuspendAfterDays();

        return $query->whereHas('loans', function (Builder $loanQuery) use ($thresholdDays): void {
            $loanQuery
                ->where('status', Loan::STATUS_BORROWED)
                ->whereNotNull('due_at')
                ->where('due_at', '<=', now()->subDays($thresholdDays));
        });
    }

    public function scopeLateReturnCooldown(Builder $query): Builder
    {
        $loanConsequenceService = app(LoanConsequenceService::class);

        if (! $loanConsequenceService->lateReturnSuspensionEnabled()) {
            return $query->whereRaw('1 = 0');
        }

        $thresholdDays = $loanConsequenceService->lateReturnSuspendAfterDays();
        $cooldownDays = $loanConsequenceService->lateReturnCooldownDays();

        if ($cooldownDays < 1) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('loans', function (Builder $loanQuery) use ($cooldownDays, $thresholdDays): void {
            $loanQuery
                ->where('status', Loan::STATUS_RETURNED)
                ->whereNotNull('due_at')
                ->whereNotNull('returned_at')
                ->where('returned_at', '>', now()->subDays($cooldownDays));

            $connection = $loanQuery->getConnection();

            if ($connection instanceof Connection && $connection->getDriverName() === 'sqlite') {
                $loanQuery->whereRaw('julianday(returned_at) - julianday(due_at) >= ?', [$thresholdDays]);
            } else {
                $loanQuery->whereRaw('TIMESTAMPDIFF(DAY, due_at, returned_at) >= ?', [$thresholdDays]);
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

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatarUrl();
    }
}
