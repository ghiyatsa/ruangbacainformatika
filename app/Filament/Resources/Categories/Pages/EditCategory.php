<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Support\Library\LibraryResourceActionFactory;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LibraryResourceActionFactory::deleteAction(
                singularLabel: 'Kategori',
                fallbackReason: 'Masih ada data terkait yang membuat kategori ini tidak bisa dihapus saat ini.',
                modalDescription: 'Kategori hanya bisa dihapus jika sudah tidak dipakai oleh data buku mana pun.',
            ),
        ];
    }
}
