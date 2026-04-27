<?php

namespace App\Filament\Resources\Publishers\Pages;

use App\Filament\Resources\Publishers\PublisherResource;
use App\Support\Library\LibraryResourceActionFactory;
use Filament\Resources\Pages\EditRecord;

class EditPublisher extends EditRecord
{
    protected static string $resource = PublisherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LibraryResourceActionFactory::deleteAction(
                singularLabel: 'Penerbit',
                fallbackReason: 'Masih ada data terkait yang membuat penerbit ini tidak bisa dihapus saat ini.',
                modalDescription: 'Penerbit hanya bisa dihapus jika sudah tidak dipakai oleh data buku mana pun.',
            ),
        ];
    }
}
