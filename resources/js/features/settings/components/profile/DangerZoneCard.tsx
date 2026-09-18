import { router } from '@inertiajs/react';
import { AlertTriangle, Trash2 } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import settings from '@/routes/settings';

export interface AccountDeletionProps {
    blockingReason: string | null;
    confirmationPhrase: string;
    gracePeriodDays: number;
}

interface DangerZoneCardProps {
    accountDeletion: AccountDeletionProps;
}

export function DangerZoneCard({ accountDeletion }: DangerZoneCardProps) {
    const { blockingReason, confirmationPhrase, gracePeriodDays } =
        accountDeletion;
    const [open, setOpen] = useState(false);
    const [confirmation, setConfirmation] = useState('');
    const [reason, setReason] = useState('');
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const canSubmit = confirmation === confirmationPhrase && !processing;

    const reset = () => {
        setConfirmation('');
        setReason('');
        setError(null);
    };

    const submit = () => {
        setProcessing(true);
        setError(null);

        router.delete(settings.profile.destroy.url(), {
            data: { confirmation, reason: reason || null },
            preserveScroll: true,
            onError: (errors) => {
                setError(
                    errors.confirmation ??
                        errors.deletion ??
                        'Penghapusan akun gagal. Periksa kembali isian Anda.',
                );
            },
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <section className="relative overflow-hidden rounded-2xl border border-destructive/30 bg-linear-to-br from-destructive/5 via-card to-card p-6 shadow-xs">
            <div className="pointer-events-none absolute -right-10 -bottom-10 -z-0 h-32 w-32 rounded-full bg-destructive/10 blur-2xl" />

            <div className="relative space-y-4">
                <div className="flex items-start gap-3">
                    <span className="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-destructive/10 text-destructive">
                        <AlertTriangle className="h-5 w-5" />
                    </span>
                    <div className="space-y-1">
                        <h2 className="text-base font-semibold">Hapus Akun</h2>
                        <p className="text-sm text-muted-foreground">
                            Hapus akun beserta data pribadi Anda secara
                            permanen.
                        </p>
                    </div>
                </div>

                {blockingReason ? (
                    <div className="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4">
                        <p className="text-sm font-medium text-amber-700 dark:text-amber-400">
                            Akun belum dapat dihapus
                        </p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {blockingReason}
                        </p>
                    </div>
                ) : (
                    <Button
                        type="button"
                        variant="destructive"
                        className="rounded-xl"
                        onClick={() => {
                            reset();
                            setOpen(true);
                        }}
                    >
                        <Trash2 className="mr-2 h-4 w-4" />
                        Hapus Akun Saya
                    </Button>
                )}
            </div>

            <Dialog
                open={open}
                onOpenChange={(next) => {
                    setOpen(next);

                    if (!next) {
                        reset();
                    }
                }}
            >
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Hapus akun secara permanen?</DialogTitle>
                        <DialogDescription>
                            Tindakan ini tidak dapat dibatalkan. Data pribadi
                            Anda akan dihapus dan Anda akan keluar dari akun
                            ini.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4 py-2">
                        <div className="space-y-2">
                            <Label htmlFor="deletion-confirmation">
                                Ketik{' '}
                                <span className="font-semibold text-destructive">
                                    {confirmationPhrase}
                                </span>{' '}
                                untuk mengonfirmasi
                            </Label>
                            <Input
                                id="deletion-confirmation"
                                value={confirmation}
                                onChange={(e) =>
                                    setConfirmation(e.target.value)
                                }
                                autoComplete="off"
                                placeholder={confirmationPhrase}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="deletion-reason">
                                Alasan (opsional)
                            </Label>
                            <Input
                                id="deletion-reason"
                                value={reason}
                                onChange={(e) => setReason(e.target.value)}
                                placeholder="Misalnya: tidak lagi meminjam buku"
                            />
                        </div>

                        <p className="text-sm text-muted-foreground">
                            Data pribadi Anda (nama, email, nomor WhatsApp, dan
                            alamat) dihapus permanen, dan akun tidak dapat
                            diakses lagi. Riwayat peminjaman tetap tersimpan
                            sebagai data perpustakaan tanpa keterkaitan dengan
                            identitas Anda.
                        </p>

                        <p className="rounded-xl border border-border/60 bg-muted/40 p-3 text-sm text-muted-foreground">
                            Setelah diajukan, akun langsung tidak dapat diakses.
                            Hubungi pengelola dalam {gracePeriodDays} hari bila
                            Anda berubah pikiran.
                        </p>

                        {error ? (
                            <p className="rounded-xl border border-destructive/30 bg-destructive/5 p-3 text-sm text-destructive">
                                {error}
                            </p>
                        ) : null}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            className="rounded-xl"
                            onClick={() => setOpen(false)}
                            disabled={processing}
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            className="rounded-xl"
                            onClick={submit}
                            disabled={!canSubmit}
                        >
                            {processing ? 'Menghapus...' : 'Hapus Akun'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </section>
    );
}
