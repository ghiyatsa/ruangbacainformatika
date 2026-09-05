<?php

namespace App\Filament\Resources\DocumentSubmissions\Tables;

use App\Models\DocumentSubmission;
use App\Models\User;
use App\Services\DocumentDistributionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul, instansi, nama, atau NIM')
            ->emptyStateHeading('Belum ada data distribusi dokumen')
            ->emptyStateDescription('Pengajuan laporan KP, Skripsi, atau Sumbangan Buku akan muncul di sini.')
            ->emptyStateIcon(Heroicon::OutlinedDocumentCheck)
            ->columns([
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->state(fn (DocumentSubmission $record): string => $record->typeLabel())
                    ->color(fn (DocumentSubmission $record): string => $record->typeBadgeColor())
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul / Dokumen')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (DocumentSubmission $record): string => $record->company_name ?? $record->author_names ?? '-'),
                TextColumn::make('user.name')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->description(fn (DocumentSubmission $record): string => 'NIM: '.($record->user?->identityNumber() ?? '-')),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (DocumentSubmission $record): string => $record->statusLabel())
                    ->color(fn (DocumentSubmission $record): string => $record->statusColor())
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('receipt_number')
                    ->label('No. Tanda Terima')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('reviewer.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Dokumen')
                    ->options([
                        DocumentSubmission::TYPE_INTERNSHIP_REPORT => 'Laporan KP',
                        DocumentSubmission::TYPE_SKRIPSI => 'Skripsi',
                        DocumentSubmission::TYPE_BOOK_DONATION => 'Sumbangan Buku',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        DocumentSubmission::STATUS_PENDING => 'Menunggu Verifikasi',
                        DocumentSubmission::STATUS_REVISION => 'Perlu Revisi',
                        DocumentSubmission::STATUS_APPROVED => 'Disetujui',
                        DocumentSubmission::STATUS_REJECTED => 'Ditolak',
                    ]),
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(fn (): array => DocumentSubmission::query()
                        ->whereNotNull('year')
                        ->select('year')
                        ->distinct()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->all()
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),

                    Action::make('downloadDocument')
                        ->label('Unduh Dokumen PDF')
                        ->icon(Heroicon::OutlinedDocumentArrowDown)
                        ->color('info')
                        ->visible(fn (DocumentSubmission $record): bool => ! empty($record->document_file_path))
                        ->url(fn (DocumentSubmission $record): string => route('documents.file', ['submission' => $record->id, 'field' => 'document']))
                        ->openUrlInNewTab(),

                    Action::make('viewEndorsement')
                        ->label('Lihat Pengesahan')
                        ->icon(Heroicon::OutlinedEye)
                        ->color('gray')
                        ->visible(fn (DocumentSubmission $record): bool => $record->type !== DocumentSubmission::TYPE_BOOK_DONATION && ! empty($record->endorsement_file_path))
                        ->url(fn (DocumentSubmission $record): string => route('documents.file', ['submission' => $record->id, 'field' => 'endorsement']))
                        ->openUrlInNewTab(),

                    Action::make('approve')
                        ->label('Setujui Pengajuan')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Persetujuan Dokumen')
                        ->modalDescription('Dokumen akan diverifikasi, otomatis terbit ke katalog resmi, dan nomor tanda terima diterbitkan.')
                        ->visible(fn (DocumentSubmission $record): bool => $record->status !== DocumentSubmission::STATUS_APPROVED)
                        ->action(function (DocumentSubmission $record): void {
                            /** @var User $user */
                            $user = auth()->user();
                            app(DocumentDistributionService::class)->approve($record, $user);

                            Notification::make()
                                ->success()
                                ->title('Pengajuan berhasil disetujui')
                                ->body('Dokumen resmi diterbitkan ke katalog perpustakaan.')
                                ->send();
                        }),

                    Action::make('requestRevision')
                        ->label('Minta Revisi')
                        ->icon(Heroicon::OutlinedExclamationTriangle)
                        ->color('warning')
                        ->visible(fn (DocumentSubmission $record): bool => $record->status !== DocumentSubmission::STATUS_APPROVED)
                        ->schema([
                            Textarea::make('revision_notes')
                                ->label('Catatan Revisi')
                                ->placeholder('Tuliskan poin perbaikan berkas secara spesifik...')
                                ->required()
                                ->rows(4),
                        ])
                        ->action(function (DocumentSubmission $record, array $data): void {
                            /** @var User $user */
                            $user = auth()->user();
                            app(DocumentDistributionService::class)->requestRevision($record, $user, $data['revision_notes']);

                            Notification::make()
                                ->warning()
                                ->title('Permintaan revisi terkirim')
                                ->body('Status pengajuan diperbarui menjadi Perlu Revisi.')
                                ->send();
                        }),
                ])
                    ->label('Aksi'),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->orderByRaw("
                CASE 
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'revision' THEN 2
                    WHEN status = 'rejected' THEN 3
                    WHEN status = 'approved' THEN 4
                    ELSE 5
                END ASC
            ")->orderByDesc('created_at'));
    }
}
