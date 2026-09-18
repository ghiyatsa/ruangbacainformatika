<?php

declare(strict_types=1);

namespace App\Filament\Resources\MemberRegistrationClaims\Pages;

use App\Filament\Resources\MemberRegistrationClaims\MemberRegistrationClaimsResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMemberRegistrationClaims extends ViewRecord
{
    protected static string $resource = MemberRegistrationClaimsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
