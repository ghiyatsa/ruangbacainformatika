import { useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import KioskReturnDraftController from '@/actions/App/Http/Controllers/KioskReturnDraftController';
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
import { QrCameraScanner } from '@/features/kiosk/components/QrCameraScanner';
import { BookActionForm } from './BookActionForm';
import type { QrCameraScannerHandle } from '@/features/kiosk/components/QrCameraScanner';

function getQrErrorMessage(
    errors: Record<string, string | undefined>,
): string | null {
    const priorityKeys = ['payload', 'loan_item_ids', 'member_identifier'];

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

export function ReturnForm({ loanMaxBooks }: { loanMaxBooks: number }) {
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
        qrForm.post(KioskReturnDraftController.store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                handleQrDialogChange(false);
            },
            onError: (errors) => {
                const message =
                    getQrErrorMessage(errors) ??
                    'QR sudah terbaca, tetapi pengembalian belum berhasil diproses.';

                toast.error(message);
            },
        });
    };

    return (
        <div className="space-y-4">
            <BookActionForm
                action={KioskController.storeReturn()}
                submitLabel="Kembalikan Buku"
                description="Cari pinjaman aktif atau scan QR pengembalian dari perangkat anggota."
                maxInputs={loanMaxBooks}
                bookSearchUrl={KioskController.searchBooks.url()}
                bookSearchMode="return"
                onScanQr={() => handleQrDialogChange(true)}
            />

            <Dialog open={isQrDialogOpen} onOpenChange={handleQrDialogChange}>
                <DialogContent className="max-w-2xl" showCloseButton={false}>
                    <DialogHeader>
                        <DialogTitle>Scan QR Pengembalian</DialogTitle>
                        <DialogDescription>
                            Arahkan QR pengembalian ke kamera. Proses akan
                            berjalan otomatis.
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
                                    Sedang memproses pengembalian.
                                </AlertDescription>
                            </Alert>
                        ) : null}

                        {qrErrorMessage ? (
                            <Alert variant="destructive">
                                <AlertTitle>
                                    Pengembalian belum berhasil
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
