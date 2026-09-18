<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentSubmission;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Penghapusan akun anggota sesuai permintaan pemiliknya.
 *
 * Prinsip:
 * - Data pribadi dianonimkan sehingga tidak dapat dikaitkan dengan orangnya.
 * - Riwayat peminjaman TIDAK dihapus. Riwayat sirkulasi adalah data
 *   perpustakaan, bukan data pribadi, sehingga tetap utuh demi laporan dan
 *   statistik.
 * - Akun ditandai deleted_at (soft delete) dan baru boleh dihapus permanen
 *   setelah masa tenggang berakhir.
 */
class AccountDeletionService
{
    /** Masa tenggang sebelum akun boleh dihapus permanen. */
    public const GRACE_PERIOD_DAYS = 30;

    /** Kata yang harus diketik pengguna untuk mengonfirmasi. */
    public const CONFIRMATION_PHRASE = 'HAPUS';

    /**
     * Periksa apakah akun boleh dihapus. Mengembalikan alasan penolakan,
     * atau null bila boleh dilanjutkan.
     */
    public function blockingReason(User $user): ?string
    {
        if ($user->canAccessAdminPanel()) {
            return 'Akun pengelola tidak dapat dihapus dari halaman ini.';
        }

        if ($this->hasActiveLoan($user)) {
            return 'Masih ada peminjaman yang belum dikembalikan. Selesaikan pengembalian terlebih dahulu.';
        }

        if ($this->hasPendingSubmission($user)) {
            return 'Masih ada pengajuan yang belum selesai diproses. Tunggu hingga tuntas.';
        }

        return null;
    }

    /** Apakah anggota masih punya pinjaman aktif (termasuk yang telat). */
    public function hasActiveLoan(User $user): bool
    {
        return Loan::query()
            ->whereBelongsTo($user)
            ->where('status', Loan::STATUS_BORROWED)
            ->whereNull('returned_at')
            ->exists();
    }

    /** Apakah ada pengajuan dokumen yang belum tuntas (menunggu atau perlu revisi). */
    public function hasPendingSubmission(User $user): bool
    {
        if (! class_exists(DocumentSubmission::class)) {
            return false;
        }

        return DocumentSubmission::query()
            ->whereBelongsTo($user)
            ->whereIn('status', [
                DocumentSubmission::STATUS_PENDING,
                DocumentSubmission::STATUS_REVISION,
            ])
            ->exists();
    }

    /**
     * Ajukan penghapusan: anonimkan data pribadi lalu tandai akun terhapus.
     *
     * @throws ValidationException bila akun belum boleh dihapus.
     */
    public function request(User $user, ?string $reason = null): void
    {
        if ($alasan = $this->blockingReason($user)) {
            throw ValidationException::withMessages(['deletion' => $alasan]);
        }

        DB::transaction(function () use ($user, $reason): void {
            // Interceptor model dinonaktifkan selama anonimisasi: model ini
            // mengembalikan perubahan email ke nilai lama dan memberi ulang
            // peran member, yang keduanya bertentangan dengan penghapusan.
            $user->withoutModelInterceptors(function () use ($user, $reason): void {
                $user->forceFill([
                    'name' => 'Anggota Dihapus #'.$user->id,
                    'email' => 'deleted_'.$user->id.'_'.Str::lower(Str::random(6)).'@anon.invalid',
                    'google_id' => null,
                    'avatar_url' => null,
                    'whatsapp' => null,
                    'whatsapp_verified_at' => null,
                    'address' => null,
                    'remember_token' => null,
                    'is_approved' => false,
                    'deletion_requested_at' => now(),
                    'deletion_reason' => $reason,
                ])->save();

                // Cabut peran sebelum akun ditandai terhapus.
                $user->syncRoles([]);

                // Tandai terhapus (soft delete) agar tidak muncul di daftar biasa.
                $user->delete();
            });
        });
    }

    /**
     * Batalkan permintaan penghapusan selama masa tenggang.
     *
     * Catatan: data pribadi yang sudah dianonimkan tidak dapat dikembalikan
     * otomatis. Anggota perlu melengkapi kembali datanya setelah pembatalan.
     */
    public function cancel(User $user): void
    {
        if (! $user->trashed()) {
            return;
        }

        $user->restore();
        $user->forceFill([
            'deletion_requested_at' => null,
            'deletion_reason' => null,
            'is_approved' => false,
        ])->save();
    }

    /** Sisa hari masa tenggang, atau null bila tidak sedang mengajukan. */
    public function remainingGraceDays(User $user): ?int
    {
        if ($user->deletion_requested_at === null) {
            return null;
        }

        $deadline = $user->deletion_requested_at->copy()->addDays(self::GRACE_PERIOD_DAYS);

        return max(0, (int) now()->diffInDays($deadline, false));
    }
}
