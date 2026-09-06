<?php

namespace App\Filament\Resources\DocumentSubmissions\Pages;

use App\Filament\Resources\DocumentSubmissions\DocumentSubmissionResource;
use App\Models\DocumentSubmission;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDocumentSubmissions extends ListRecords
{
    protected static string $resource = DocumentSubmissionResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Antrean')
                ->badge(fn (): int => DocumentSubmission::query()->where('status', DocumentSubmission::STATUS_PENDING)->count() ?: DocumentSubmission::query()->count()),

            'internship_reports' => Tab::make('Laporan KP')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', DocumentSubmission::TYPE_INTERNSHIP_REPORT))
                ->badge(fn (): int => DocumentSubmission::query()->where('type', DocumentSubmission::TYPE_INTERNSHIP_REPORT)->where('status', DocumentSubmission::STATUS_PENDING)->count()),

            'skripsi' => Tab::make('Skripsi')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', DocumentSubmission::TYPE_SKRIPSI))
                ->badge(fn (): int => DocumentSubmission::query()->where('type', DocumentSubmission::TYPE_SKRIPSI)->where('status', DocumentSubmission::STATUS_PENDING)->count()),

            'book_donations' => Tab::make('Sumbangan Buku')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', DocumentSubmission::TYPE_BOOK_DONATION))
                ->badge(fn (): int => DocumentSubmission::query()->where('type', DocumentSubmission::TYPE_BOOK_DONATION)->where('status', DocumentSubmission::STATUS_PENDING)->count()),
        ];
    }
}
