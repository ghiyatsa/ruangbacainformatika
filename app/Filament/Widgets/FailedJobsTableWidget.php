<?php

namespace App\Filament\Widgets;

use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Support\Facades\Artisan;

class FailedJobsTableWidget extends BaseTableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(FailedJob::query()->latest('failed_at'))
            ->columns([
                TextColumn::make('queue')
                    ->label('Antrean')
                    ->badge()
                    ->color('info')
                    ->icon(Heroicon::OutlinedQueueList)
                    ->iconPosition(IconPosition::Before),

                TextColumn::make('payload')
                    ->label('Job')
                    ->state(fn (FailedJob $record): string => $this->resolveJobDisplayName($record))
                    ->limit(40)
                    ->tooltip(fn (FailedJob $record): string => $this->resolveJobDisplayName($record)),

                TextColumn::make('failed_at')
                    ->label('Gagal Pada')
                    ->since()
                    ->sortable(),

                TextColumn::make('exception')
                    ->label('Pesan Error')
                    ->state(fn (FailedJob $record): string => $this->resolveExceptionMessage($record))
                    ->limit(60)
                    ->color('danger')
                    ->tooltip(fn (FailedJob $record): string => $this->resolveExceptionMessage($record)),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('Ulangi')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Ulangi Job Gagal')
                    ->modalDescription('Job akan dikirim ulang ke antrean.')
                    ->action(function (FailedJob $record): void {
                        Artisan::call('queue:retry', ['id' => [$record->uuid]]);

                        Notification::make()
                            ->title('Job dikirim ulang ke antrean')
                            ->success()
                            ->send();
                    }),

                Action::make('delete')
                    ->label('Hapus')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Job Gagal')
                    ->modalDescription('Catatan kegagalan ini akan dihapus permanen.')
                    ->action(function (FailedJob $record): void {
                        Artisan::call('queue:forget', ['id' => $record->uuid]);

                        Notification::make()
                            ->title('Job gagal dihapus')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                ActionGroup::make([
                    Action::make('retryAll')
                        ->label('Ulangi Semua')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Ulangi Semua Job Gagal')
                        ->modalDescription('Seluruh job gagal akan dikirim ulang ke antrean.')
                        ->action(function (): void {
                            Artisan::call('queue:retry', ['id' => ['all']]);

                            Notification::make()
                                ->title('Seluruh job gagal dikirim ulang')
                                ->success()
                                ->send();
                        }),

                    Action::make('purge')
                        ->label('Bersihkan Semua')
                        ->icon(Heroicon::OutlinedTrash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Bersihkan Semua Job Gagal')
                        ->modalDescription('Seluruh catatan job gagal akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.')
                        ->action(function (): void {
                            Artisan::call('queue:flush');

                            Notification::make()
                                ->title('Seluruh job gagal dibersihkan')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->emptyStateHeading('Tidak ada job gagal')
            ->emptyStateDescription('Semua antrean berjalan lancar.')
            ->paginated([10, 25, 50]);
    }

    protected function resolveJobDisplayName(FailedJob $record): string
    {
        $payload = json_decode((string) $record->payload, true);

        if (is_array($payload) && isset($payload['displayName'])) {
            return strval($payload['displayName']);
        }

        return 'Job tanpa nama';
    }

    protected function resolveExceptionMessage(FailedJob $record): string
    {
        $firstLine = str((string) $record->exception)
            ->before("\n")
            ->trim()
            ->value();

        return $firstLine !== '' ? $firstLine : 'Tanpa detail error';
    }
}
