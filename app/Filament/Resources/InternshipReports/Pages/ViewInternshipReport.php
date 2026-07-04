<?php

namespace App\Filament\Resources\InternshipReports\Pages;

use App\Filament\Resources\InternshipReports\InternshipReportResource;
use App\Models\InternshipReport;
use App\Services\SimilaritySyncDispatcher;
use App\Services\SimilaritySyncStatusService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewInternshipReport extends ViewRecord
{
    protected static string $resource = InternshipReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retrySync')
                ->label('Sinkronkan Ulang')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('info')
                ->requiresConfirmation()
                ->action(function (): void {
                    app(SimilaritySyncStatusService::class)->markQueued($this->record);
                    app(SimilaritySyncDispatcher::class)->dispatchUpsert($this->record->getKey(), InternshipReport::class);

                    Notification::make()
                        ->success()
                        ->title('Sinkron masuk antrean')
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
