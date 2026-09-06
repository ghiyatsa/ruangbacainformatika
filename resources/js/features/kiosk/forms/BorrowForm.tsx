import { useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { lazy, Suspense } from 'react';
import { toast } from 'sonner';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
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
import { BookActionForm } from './BookActionForm';
import type { QrCameraScannerHandle } from '@/features/kiosk/components/QrCameraScanner';

const QrCameraScanner = lazy(() =>
    import('@/features/kiosk/components/QrCameraScanner').then((module) => ({
        default: module.QrCameraScanner,
    })),
);

function getQrErrorMessage(
    errors: Record<string, string | undefined>,
): string | null {
    const priorityKeys = [
        'payload',
        'verification_payload',
        'book_ids',
        'book_ids.0',
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
    const [selectedBookIds, setSelectedBookIds] = useState<number[]>([]);
    const [selectedMemberIdentifier, setSelectedMemberIdentifier] =
        useState('');
    const [formKey, setFormKey] = useState(0);
    const scannerRef = useRef<QrCameraScannerHandle | null>(null);
    const qrForm = useForm({
        member_identifier: '',
        verification_payload: '',
        book_ids: [] as number[],
    });
    const qrErrorMessage = getQrErrorMessage(qrForm.errors);

    const handleQrDialogChange = (open: boolean) => {
        setIsQrDialogOpen(open);

        if (!open) {
            scannerRef.current?.stop();
            setHasDetectedQr(false);
            setSelectedBookIds([]);
            setSelectedMemberIdentifier('');
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
        if (qrForm.processing || selectedBookIds.length === 0) {
            return;
        }

        setHasDetectedQr(true);
        qrForm.clearErrors();
        qrForm.setData({
            member_identifier: selectedMemberIdentifier,
            verification_payload: payload,
            book_ids: selectedBookIds,
        });
        qrForm.post(KioskController.borrow().url, {
            preserveScroll: true,
            onSuccess: () => {
                handleQrDialogChange(false);
                setFormKey((current) => current + 1);
            },
            onError: (errors) => {
                const message =
                    getQrErrorMessage(errors) ??
                    'Kode QR terbaca, tetapi peminjaman belum dapat diproses. Silakan coba sesaat lagi.';

                toast.error(message);
            },
        });
    };

    const startBorrowVerification = (
        memberIdentifier: string,
        bookIds: number[],
    ) => {
        setSelectedMemberIdentifier(memberIdentifier);
        setSelectedBookIds(bookIds);
        qrForm.setData({
            member_identifier: memberIdentifier,
            verification_payload: '',
            book_ids: bookIds,
        });
        setIsQrDialogOpen(true);
    };

    return (
        <div className="space-y-6">
            <BookActionForm
                key={formKey}
                action={KioskController.borrow()}
                submitLabel="Lanjutkan Peminjaman"
                description="Masukkan identitas anggota dan pilih buku yang ingin dipinjam."
                maxInputs={loanMaxBooks}
                bookSearchUrl={KioskController.searchBooks().url}
                bookSearchMode="borrow"
                onActionSubmit={({ memberIdentifier, selectedBooks }) => {
                    startBorrowVerification(
                        memberIdentifier,
                        selectedBooks.map((b) => b.id),
                    );
                }}
            />

            <Dialog open={isQrDialogOpen} onOpenChange={handleQrDialogChange}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Verifikasi Peminjaman</DialogTitle>
                        <DialogDescription>
                            Arahkan kode QR <strong>Member Key</strong> dari HP
                            Anda ke kamera untuk menyelesaikan peminjaman.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="overflow-hidden rounded-xl border bg-black">
                            <Suspense
                                fallback={
                                    <div className="flex aspect-square w-full items-center justify-center text-sm text-white">
                                        <Spinner className="mr-2 size-4" />
                                        Menyiapkan kamera...
                                    </div>
                                }
                            >
                                <QrCameraScanner
                                    ref={scannerRef}
                                    onDetected={submitDetectedPayload}
                                />
                            </Suspense>
                        </div>

                        {qrErrorMessage ? (
                            <Alert variant="destructive">
                                <AlertTitle>
                                    Verifikasi Belum Berhasil
                                </AlertTitle>
                                <AlertDescription>
                                    {qrErrorMessage}
                                </AlertDescription>
                            </Alert>
                        ) : null}

                        {hasDetectedQr && qrForm.processing ? (
                            <div className="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                                <Spinner className="size-4" />
                                Menyelesaikan peminjaman...
                            </div>
                        ) : (
                            <Button
                                type="button"
                                variant="outline"
                                className="w-full"
                                onClick={restartScanner}
                                disabled={qrForm.processing}
                            >
                                Scan Ulang
                            </Button>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    );
}
