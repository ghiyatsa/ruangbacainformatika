<?php

namespace App\Filament\Resources\StaticPages\Tables;

use App\Models\StaticPage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class StaticPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul atau slug halaman')
            ->emptyStateHeading('Belum ada halaman statis')
            ->emptyStateDescription('Halaman publik akan muncul di sini.')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),
                IconColumn::make('is_active')
                    ->label('Publikasi')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('open')
                        ->label('Buka Halaman')
                        ->url(fn (StaticPage $record): string => $record->publicUrl())
                        ->openUrlInNewTab(),
                    EditAction::make()
                        ->label('Ubah'),
                ])->label('Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            /** @var StaticPage|null $systemPage */
                            $systemPage = $records->first(fn (StaticPage $record): bool => $record->isSystemPage());

                            if (! $systemPage) {
                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('Halaman bawaan belum bisa dihapus')
                                ->body('Gunakan menu ubah untuk memperbarui halaman bawaan seperti About, Kebijakan Privasi, atau Syarat Layanan.')
                                ->send();

                            $action->halt();
                        }),
                ]),
            ]);
    }
}
