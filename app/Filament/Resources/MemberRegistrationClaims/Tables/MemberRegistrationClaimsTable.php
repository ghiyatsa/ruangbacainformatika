<?php

namespace App\Filament\Resources\MemberRegistrationClaims\Tables;

use App\Models\MemberRegistrationClaim;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberRegistrationClaimsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->description(fn (MemberRegistrationClaim $record): string => $record->email)
                    ->wrap(),
                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => ucfirst($state),
                    )
                    ->color(fn (MemberRegistrationClaim $record): string => match ($record->status) {
                        MemberRegistrationClaim::STATUS_PENDING => 'warning',
                        MemberRegistrationClaim::STATUS_LINKED => 'info',
                        MemberRegistrationClaim::STATUS_CLAIMED => 'success',
                        MemberRegistrationClaim::STATUS_EXPIRED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Akun Terkait')
                    ->placeholder('-'),
                TextColumn::make('expires_at')
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y H:i')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        MemberRegistrationClaim::STATUS_PENDING => 'Pending',
                        MemberRegistrationClaim::STATUS_LINKED => 'Linked',
                        MemberRegistrationClaim::STATUS_CLAIMED => 'Claimed',
                        MemberRegistrationClaim::STATUS_EXPIRED => 'Expired',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada klaim registrasi')
            ->emptyStateDescription('Klaim registrasi dari kiosk akan muncul di sini.');
    }
}
