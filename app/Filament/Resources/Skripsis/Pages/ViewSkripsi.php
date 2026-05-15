<?php

namespace App\Filament\Resources\Skripsis\Pages;

use App\Filament\Resources\Skripsis\SkripsiResource;
use App\Services\SimilaritySyncDispatcher;
use App\Services\SimilaritySyncStatusService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewSkripsi extends ViewRecord
{
    protected static string $resource = SkripsiResource::class;

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
                    app(SimilaritySyncDispatcher::class)->dispatchUpsert($this->record->getKey());

                    Notification::make()
                        ->success()
                        ->title('Sync dijadwalkan')
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
