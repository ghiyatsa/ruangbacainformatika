import { useForm } from '@inertiajs/react';
import { QrCode, ScanLine } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import KioskLoanDraftController from '@/actions/App/Http/Controllers/KioskLoanDraftController';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import type { QrCameraScannerHandle } from '@/features/kiosk/components/QrCameraScanner';
import { QrCameraScanner } from '@/features/kiosk/components/QrCameraScanner';
import { BookActionForm } from './BookActionForm';

function getQrErrorMessage(
    errors: Record<string, string | undefined>,
): string | null {
    const priorityKeys = [
        'payload',
        'book_ids',
        'book_ids.0',
        'member_identifier',
        'draft',
    ];

    for (const key of priorityKeys) {
        const message = errors[key];

        if (message) {
            return message;
        }
    }

    return (
        Object.values(errors).find(
            (message): message is string =>
                typeof message === 'string' && message !== '',
        ) ?? null
    );
}

export function BorrowForm({ loanMaxBooks }: { loanMaxBooks: number }) {
    const [isQrDialogOpen, setIsQrDialogOpen] = useState(false);
    const [hasDetectedQr, setHasDetectedQr] = useState(false);
    const scannerRef = useRef<QrCameraScannerHandle | null>(null);
    const qrForm = useForm({
        payload: '',
    });
    const qrErrorMessage = getQrErrorMessage(qrForm.errors);

    const handleQrDialogChange = (open: boolean) => {
        setIsQrDialogOpen(open);

        if (!open) {
            scannerRef.current?.stop();
            setHasDetectedQr(false);
            qrForm.reset();
            qrForm.clearErrors();
        }
    };

    useEffect(() => {
        if (!isQrDialogOpen) {
            return;
        }

        const timer = window.setTimeout(() => {
            void scannerRef.current?.start();
        }, 80);

        return () => window.clearTimeout(timer);
    }, [isQrDialogOpen]);

    const restartScanner = () => {
        if (qrForm.processing) {
            return;
        }

        setHasDetectedQr(false);
        qrForm.reset();
        qrForm.clearErrors();
        void scannerRef.current?.start();
    };

    const submitDetectedPayload = (payload: string) => {
        if (qrForm.processing) {
            return;
        }

        setHasDetectedQr(true);
        qrForm.clearErrors();
        qrForm.setData('payload', payload);
        qrForm.post(KioskLoanDraftController.store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                handleQrDialogChange(false);
            },
            onError: (errors) => {
                const message =
                    getQrErrorMessage(errors) ??
                    'QR sudah terbaca, tetapi peminjaman belum berhasil diproses.';

                toast.error(message);
            },
        });
    };

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-border/70 bg-muted/20 p-4">
                <div className="flex items-start gap-3">
                    <div className="rounded-xl bg-primary/10 p-2.5 text-primary">
                        <ScanLine className="size-4" />
                    </div>
                    <div className="space-y-1">
                        <h3 className="text-base font-semibold text-foreground">
                            Scan QR
                        </h3>
                        <p className="text-sm text-muted-foreground">
                            Buka pemindai untuk membaca QR anggota.
                        </p>
                    </div>
                </div>

                <Button
                    type="button"
                    onClick={() => handleQrDialogChange(true)}
                >
                    <QrCode className="size-4" />
                    Scan QR
                </Button>
            </div>

            <BookActionForm
                action={KioskController.borrow.form()}
                submitLabel="Pinjam Buku"
                description={`Maksimal ${loanMaxBooks} buku per anggota.`}
                maxInputs={loanMaxBooks}
                bookSearchUrl={KioskController.searchBooks.url()}
                bookSearchMode="borrow"
                autoFocus={false}
            />

            <Dialog open={isQrDialogOpen} onOpenChange={handleQrDialogChange}>
                <DialogContent className="max-w-2xl" showCloseButton={false}>
                    <DialogHeader>
                        <DialogTitle>Scan QR Anggota</DialogTitle>
                        <DialogDescription>
                            Arahkan QR ke kamera. Proses akan berjalan otomatis.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <QrCameraScanner
                            ref={scannerRef}
                            onDetected={submitDetectedPayload}
                        />

                        {qrForm.processing ? (
                            <Alert>
                                <Spinner />
                                <AlertTitle>QR terbaca</AlertTitle>
                                <AlertDescription>
                                    Sedang memproses peminjaman.
                                </AlertDescription>
                            </Alert>
                        ) : null}

                        {qrErrorMessage ? (
                            <Alert variant="destructive">
                                <AlertTitle>
                                    Peminjaman belum berhasil
                                </AlertTitle>
                                <AlertDescription>
                                    {qrErrorMessage}
                                </AlertDescription>
                            </Alert>
                        ) : null}

                        {hasDetectedQr &&
                        !qrForm.processing &&
                        !qrErrorMessage ? (
                            <Alert>
                                <AlertTitle>QR sudah terbaca</AlertTitle>
                                <AlertDescription>
                                    Jika dialog belum tertutup, silakan scan
                                    ulang.
                                </AlertDescription>
                            </Alert>
                        ) : null}

                        <div className="flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={restartScanner}
                                disabled={qrForm.processing}
                            >
                                Scan Ulang
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => handleQrDialogChange(false)}
                            >
                                Tutup
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    );
}
