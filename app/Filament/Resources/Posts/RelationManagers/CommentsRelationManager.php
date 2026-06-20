<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Komentar';

    protected static ?string $modelLabel = 'Komentar';

    protected static ?string $pluralModelLabel = 'Komentar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->default(fn () => auth()->id())
                    ->label('Penulis'),
                Select::make('parent_id')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'content',
                        modifyQueryUsing: fn ($query) => $query->whereNull('parent_id')
                    )
                    ->placeholder('Pilih komentar induk (opsional)')
                    ->searchable()
                    ->live()
                    ->label('Balasan Untuk'),
                Select::make('reply_to_comment_id')
                    ->relationship(
                        name: 'replyToComment',
                        titleAttribute: 'content',
                        modifyQueryUsing: function ($query, Get $get) {
                            $parentId = $get('parent_id');
                            if ($parentId) {
                                return $query->where(function ($q) use ($parentId) {
                                    $q->where('id', $parentId)
                                        ->orWhere('parent_id', $parentId);
                                });
                            }

                            return $query;
                        }
                    )
                    ->placeholder('Pilih komentar spesifik yang dibalas (opsional)')
                    ->searchable()
                    ->label('Membalas Komentar')
                    ->visible(fn (Get $get) => filled($get('parent_id'))),
                Textarea::make('content')
                    ->required()
                    ->maxLength(1000)
                    ->columnSpanFull()
                    ->label('Isi Komentar'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Penulis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content')
                    ->label('Isi Komentar')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('parent.content')
                    ->label('Komentar Induk')
                    ->limit(30)
                    ->placeholder('-'),
                TextColumn::make('replyToComment.user.name')
                    ->label('Membalas')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
