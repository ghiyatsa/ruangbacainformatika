<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\LoanResource;
use App\Support\Library\LibraryResourceActionFactory;
use Filament\Resources\Pages\ViewRecord;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LibraryResourceActionFactory::deleteAction(
                singularLabel: 'Transaksi Peminjaman',
                fallbackReason: 'Transaksi peminjaman tersebut tidak dapat dihapus karena masih memiliki keterkaitan data.',
                modalDescription: 'Transaksi peminjaman hanya dapat dihapus apabila seluruh item telah dikembalikan dan transaksi tidak lagi aktif.',
            ),
        ];
    }
}
