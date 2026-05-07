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
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('internal_code')
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
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Eksemplar'),
                BatchCreateBookItemsAction::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Eksemplar'),
                    BulkAction::make('updateShelfLocation')
                        ->label('Ubah Lokasi Rak')
                        ->icon(Heroicon::OutlinedRectangleGroup)
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
                                ->body("Lokasi rak untuk {$records->count()} eksemplar berhasil diubah menjadi {$data['shelf_location']}.")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
