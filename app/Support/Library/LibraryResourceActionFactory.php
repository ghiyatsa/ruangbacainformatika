<?php

namespace App\Support\Library;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LibraryResourceActionFactory
{
    public static function deleteAction(
        string $singularLabel,
        string $fallbackReason,
        ?string $modalDescription = null,
    ): DeleteAction {
        $label = Str::lower($singularLabel);

        return DeleteAction::make()
            ->label('Hapus')
            ->modalHeading("Konfirmasi penghapusan {$label}")
            ->modalDescription($modalDescription ?? "Sistem akan memverifikasi seluruh keterkaitan data {$label} sebelum penghapusan diproses.")
            ->action(function (DeleteAction $action, Model $record) use ($singularLabel, $fallbackReason): void {
                $reason = self::deletionBlockedReasonFor($record);

                if ($reason !== null) {
                    Notification::make()
                        ->danger()
                        ->title("Penghapusan {$singularLabel} tidak dapat diproses")
                        ->body($reason)
                        ->send();

                    $action->cancel();

                    return;
                }

                try {
                    $record->delete();
                } catch (QueryException) {
                    Notification::make()
                        ->danger()
                        ->title("Penghapusan {$singularLabel} tidak dapat diproses")
                        ->body($fallbackReason)
                        ->send();

                    $action->cancel();
                }
            })
            ->successNotificationTitle("{$singularLabel} telah berhasil dihapus");
    }

    public static function deleteBulkAction(
        string $singularLabel,
        string $pluralLabel,
        string $genericFailureReason,
    ): DeleteBulkAction {
        return DeleteBulkAction::make()
            ->label('Hapus Terpilih')
            ->modalHeading("Konfirmasi penghapusan {$pluralLabel} terpilih")
            ->modalDescription("Setiap {$singularLabel} akan diverifikasi terlebih dahulu agar data yang masih memiliki keterkaitan tidak terhapus secara keliru.")
            ->successNotificationTitle("Data {$pluralLabel} terpilih telah berhasil dihapus")
            ->failureNotificationTitle(function (int $successCount, int $totalCount) use ($pluralLabel): string {
                if ($successCount > 0) {
                    return "{$successCount} dari {$totalCount} data {$pluralLabel} berhasil dihapus.";
                }

                return "Tidak ada data {$pluralLabel} yang dapat dihapus.";
            })
            ->action(function (DeleteBulkAction $action, Collection $records) use ($singularLabel, $pluralLabel, $genericFailureReason): void {
                $failureKey = Str::slug("{$singularLabel}-delete-blocked");

                foreach ($records as $record) {
                    $reason = self::deletionBlockedReasonFor($record);

                    if ($reason !== null) {
                        $action->reportBulkProcessingFailure(
                            $failureKey,
                            fn (int $failureCount, int $totalCount): string => self::formatBulkFailureMessage(
                                singularLabel: $singularLabel,
                                pluralLabel: $pluralLabel,
                                failureCount: $failureCount,
                                totalCount: $totalCount,
                                genericFailureReason: $genericFailureReason,
                            ),
                        );

                        continue;
                    }

                    try {
                        $record->delete();
                    } catch (QueryException) {
                        $action->reportBulkProcessingFailure(
                            $failureKey,
                            fn (int $failureCount, int $totalCount): string => self::formatBulkFailureMessage(
                                singularLabel: $singularLabel,
                                pluralLabel: $pluralLabel,
                                failureCount: $failureCount,
                                totalCount: $totalCount,
                                genericFailureReason: $genericFailureReason,
                            ),
                        );
                    }
                }
            })
            ->deselectRecordsAfterCompletion();
    }

    protected static function deletionBlockedReasonFor(Model $record): ?string
    {
        if (! method_exists($record, 'deletionBlockedReason')) {
            return null;
        }

        /** @var ?string $reason */
        $reason = $record->deletionBlockedReason();

        return $reason;
    }

    protected static function formatBulkFailureMessage(
        string $singularLabel,
        string $pluralLabel,
        int $failureCount,
        int $totalCount,
        string $genericFailureReason,
    ): string {
        if (($failureCount === 1) && ($totalCount === 1)) {
            return ucfirst($singularLabel)." tidak dapat dihapus karena {$genericFailureReason}.";
        }

        if ($failureCount === $totalCount) {
            return "Seluruh {$pluralLabel} tidak dapat dihapus karena {$genericFailureReason}.";
        }

        if ($failureCount === 1) {
            return "Satu {$singularLabel} tidak dapat dihapus karena {$genericFailureReason}.";
        }

        return "{$failureCount} {$pluralLabel} tidak dapat dihapus karena {$genericFailureReason}.";
    }
}
