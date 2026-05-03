<?php

namespace App\Filament\Resources\Loans;

use App\Filament\Resources\Loans\Pages\ListLoans;
use App\Filament\Resources\Loans\Pages\ViewLoan;
use App\Filament\Resources\Loans\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Loans\Schemas\LoanInfolist;
use App\Filament\Resources\Loans\Tables\LoansTable;
use App\Models\Loan;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LoanResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Peminjaman';

    protected static ?string $modelLabel = 'Member Peminjam';

    protected static ?string $pluralModelLabel = 'Member Peminjam';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()
            ->whereHas('loans', fn (Builder $query) => $query->where('status', Loan::STATUS_BORROWED))
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah member dengan peminjaman aktif';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LoanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoansTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('loans')
            ->withCount([
                'loans as active_loans_count' => fn (Builder $query) => $query->where('status', Loan::STATUS_BORROWED),
            ])
            ->with([
                'loans' => fn ($q) => $q->withCount([
                    'items',
                    'items as active_items_count' => fn ($iq) => $iq->whereNull('returned_at'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoans::route('/'),
            'view' => ViewLoan::route('/{record}'),
        ];
    }
}
