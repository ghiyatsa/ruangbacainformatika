<?php

namespace App\Filament\Resources\Books\RelationManagers;

use App\Filament\Resources\Books\RelationManagers\Actions\BatchCreateBookItemsAction;
use App\Filament\Resources\Books\RelationManagers\Actions\GenerateBookItemCodeAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Filament::auth()->user()?->can('update', $ownerRecord) ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('internal_code')
                    ->label('Kode Unik / Barcode')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->suffixAction(GenerateBookItemCodeAction::make()),
                Select::make('status')
                    ->label('Status Saat Ini')
                    ->options([
                        'available' => 'Tersedia di Rak',
                        'borrowed' => 'Sedang Dipinjam',
                        'reserved' => 'Dipesan',
                        'maintenance' => 'Dalam Perbaikan',
                    ])
                    ->default('available')
                    ->required(),
                Select::make('condition')
                    ->label('Kondisi Fisik')
                    ->options([
                        'good' => 'Bagus',
                        'damaged' => 'Rusak',
                        'lost' => 'Hilang',
                    ])
                    ->default('good')
                    ->required(),
                TextInput::make('shelf_location')
                    ->label('Lokasi Rak')
                    ->placeholder('Contoh: R-01-A'),
                DatePicker::make('acquired_date')
                    ->label('Tanggal Pengadaan')
                    ->default(now()),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Eksemplar')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('internal_code')
                            ->label('Kode Unik / Barcode')
                            ->weight('bold'),
                        TextEntry::make('status')
                            ->label('Status Saat Ini')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'available' => 'success',
                                'borrowed' => 'info',
                                'reserved' => 'warning',
                                'maintenance' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'available' => 'Tersedia di Rak',
                                'borrowed' => 'Sedang Dipinjam',
                                'reserved' => 'Dipesan',
                                'maintenance' => 'Dalam Perbaikan',
                                default => $state,
                            }),
                        TextEntry::make('condition')
                            ->label('Kondisi Fisik')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'good' => 'success',
                                'damaged' => 'warning',
                                'lost' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'good' => 'Bagus',
                                'damaged' => 'Rusak',
                                'lost' => 'Hilang',
                                default => $state,
                            }),
                        TextEntry::make('shelf_location')
                            ->label('Lokasi Rak')
                            ->placeholder('-'),
                        TextEntry::make('acquired_date')
                            ->label('Tanggal Pengadaan')
                            ->date('d/m/Y')
                            ->placeholder('-'),
                        TextEntry::make('price')
                            ->label('Harga')
                            ->placeholder('-')
                            ->formatStateUsing(fn (?float $state): ?string => $state !== null ? 'Rp '.number_format($state, 0, ',', '.') : null),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('internal_code')
            ->searchPlaceholder('Cari kode atau lokasi rak')
            ->emptyStateHeading('Belum ada eksemplar buku')
            ->emptyStateDescription('Tambahkan eksemplar untuk buku ini.')
            ->columns([
                TextColumn::make('internal_code')
                    ->label('Kode Barcode')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('shelf_location')
                    ->label('Lokasi Rak')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'borrowed' => 'info',
                        'reserved' => 'warning',
                        'maintenance' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'good' => 'success',
                        'damaged' => 'warning',
                        'lost' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia di Rak',
                        'borrowed' => 'Sedang Dipinjam',
                        'reserved' => 'Dipesan',
                        'maintenance' => 'Dalam Perbaikan',
                    ]),
                SelectFilter::make('condition')
                    ->label('Kondisi')
                    ->options([
                        'good' => 'Bagus',
                        'damaged' => 'Rusak',
                        'lost' => 'Hilang',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Eksemplar')
                    ->modalHeading('Tambah Eksemplar')
                    ->modalSubmitActionLabel('Simpan')
                    ->hidden(fn ($livewire) => $livewire->isReadOnly()),
                BatchCreateBookItemsAction::make()
                    ->hidden(fn ($livewire) => $livewire->isReadOnly()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->modalHeading('Detail Eksemplar'),
                EditAction::make()
                    ->label('Ubah')
                    ->modalHeading('Ubah Eksemplar')
                    ->modalSubmitActionLabel('Simpan')
                    ->hidden(fn ($livewire) => $livewire->isReadOnly()),
                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Eksemplar')
                    ->modalDescription('Eksemplar ini akan dihapus dari daftar.')
                    ->modalSubmitActionLabel('Hapus')
                    ->hidden(fn ($livewire) => $livewire->isReadOnly())
                    ->before(function (DeleteAction $action, Model $record): void {
                        if (! method_exists($record, 'deletionBlockedReason') || ! $reason = $record->deletionBlockedReason()) {
                            return;
                        }

                        Notification::make()
                            ->warning()
                            ->title('Eksemplar belum bisa dihapus')
                            ->body($reason)
                            ->send();

                        $action->halt();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Hapus Eksemplar Terpilih')
                        ->modalDescription('Eksemplar terpilih akan dihapus dari daftar.')
                        ->modalSubmitActionLabel('Hapus')
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            $blockedRecord = $records->first(fn (Model $record): bool => method_exists($record, 'deletionBlockedReason') && filled($record->deletionBlockedReason()));

                            if (! $blockedRecord) {
                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('Sebagian eksemplar belum bisa dihapus')
                                ->body($blockedRecord->deletionBlockedReason() ?? 'Masih ada riwayat peminjaman pada eksemplar terpilih.')
                                ->send();

                            $action->halt();
                        }),
                    BulkAction::make('updateShelfLocation')
                        ->label('Ubah Lokasi Rak')
                        ->icon(Heroicon::OutlinedRectangleGroup)
                        ->modalHeading('Ubah Lokasi Rak')
                        ->modalDescription('Lokasi baru akan diterapkan ke eksemplar terpilih.')
                        ->modalSubmitActionLabel('Simpan')
                        ->schema([
                            TextInput::make('shelf_location')
                                ->label('Lokasi Rak Baru')
                                ->placeholder('Contoh: R-01-A')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update([
                                'shelf_location' => $data['shelf_location'],
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Lokasi rak diperbarui')
                                ->body("{$records->count()} eksemplar dipindahkan ke {$data['shelf_location']}.")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ])
                    ->hidden(fn ($livewire) => $livewire->isReadOnly()),
            ]);
    }
}
