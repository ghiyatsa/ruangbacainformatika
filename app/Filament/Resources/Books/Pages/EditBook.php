<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Resources\Books\BookResource;
use App\Support\Library\LibraryResourceActionFactory;
use Filament\Resources\Pages\EditRecord;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LibraryResourceActionFactory::deleteAction(
                singularLabel: 'Buku',
                fallbackReason: 'Masih ada data terkait yang membuat buku ini tidak bisa dihapus saat ini.',
                modalDescription: 'Buku hanya bisa dihapus jika sudah tidak memiliki eksemplar aktif maupun riwayat sirkulasi yang terkait.',
            ),
        ];
    }
}
