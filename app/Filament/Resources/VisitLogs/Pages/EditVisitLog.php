<?php

namespace App\Filament\Resources\VisitLogs\Pages;

use App\Filament\Resources\VisitLogs\VisitLogResource;
use App\Support\Library\LibraryResourceActionFactory;
use Filament\Resources\Pages\EditRecord;

class EditVisitLog extends EditRecord
{
    protected static string $resource = VisitLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LibraryResourceActionFactory::deleteAction(
                singularLabel: 'Data Kunjungan',
                fallbackReason: 'Terjadi kendala saat memproses penghapusan data kunjungan tersebut.',
                modalDescription: 'Pastikan data kunjungan yang dipilih memang perlu dihapus dari catatan operasional.',
            ),
        ];
    }
}
