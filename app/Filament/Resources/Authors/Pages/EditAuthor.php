<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use App\Support\Library\LibraryResourceActionFactory;
use Filament\Resources\Pages\EditRecord;

class EditAuthor extends EditRecord
{
    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LibraryResourceActionFactory::deleteAction(
                singularLabel: 'Penulis',
                fallbackReason: 'Masih ada data terkait yang membuat penulis ini tidak bisa dihapus saat ini.',
                modalDescription: 'Penulis hanya bisa dihapus jika sudah tidak terhubung dengan data buku mana pun.',
            ),
        ];
    }
}
