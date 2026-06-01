<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;

class ContactMessagesTableWidget extends BaseTableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(ContactMessage::query()->latest())
            ->columns([
                TextColumn::make('name')
                    ->label('Pengirim')
                    ->searchable()
                    ->description(fn (ContactMessage $record): string => $record->subject)
                    ->wrap(),
                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (ContactMessage $record): string => $record->statusLabel())
                    ->badge()
                    ->color(fn (ContactMessage $record): string => $record->statusColor()),
                TextColumn::make('preview')
                    ->label('Pesan')
                    ->state(fn (ContactMessage $record): string => $record->preview)
                    ->limit(70)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Masuk')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (ContactMessage $record): string => ContactMessageResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateIcon(Heroicon::OutlinedEnvelope)
            ->emptyStateHeading('Belum ada pesan masuk')
            ->emptyStateDescription('Pesan dari halaman kontak akan tampil di sini.')
            ->paginated([5, 10]);
    }
}
