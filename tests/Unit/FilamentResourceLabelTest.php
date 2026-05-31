<?php

use App\Filament\Resources\CatalogReports\CatalogReportResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;

it('uses polished labels for catalog feedback resources', function () {
    expect(CatalogReportResource::getNavigationLabel())->toBe('Umpan Balik')
        ->and(CatalogReportResource::getModelLabel())->toBe('Umpan balik')
        ->and(CatalogReportResource::getPluralModelLabel())->toBe('Umpan balik')
        ->and(CatalogReportResource::getNavigationBadgeTooltip())->toBe('Umpan balik menunggu tindak lanjut');
});

it('uses polished labels for correspondence resources', function () {
    expect(ContactMessageResource::getNavigationLabel())->toBe('Pesan Kontak')
        ->and(ContactMessageResource::getModelLabel())->toBe('Pesan Kontak')
        ->and(ContactMessageResource::getPluralModelLabel())->toBe('Pesan Kontak')
        ->and(ContactMessageResource::getNavigationBadgeTooltip())->toBe('Pesan kontak baru');
});
