import { useForm } from '@inertiajs/react';
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
import { QrCameraScanner } from '@/features/kiosk/components/QrCameraScanner';
import { BookActionForm } from './BookActionForm';
import type { QrCameraScannerHandle } from '@/features/kiosk/components/QrCameraScanner';

function getQrErrorMessage(
    errors: Record<string, string | undefined>,
): string | null {
    const priorityKeys = [
        'payload',
        'verification_payload',
        'book_ids',
        'book_ids.0',
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
    const [isLoanQrDialogOpen, setIsLoanQrDialogOpen] = useState(false);
    const [hasDetectedQr, setHasDetectedQr] = useState(false);
    const [hasDetectedLoanQr, setHasDetectedLoanQr] = useState(false);
    const [selectedBookIds, setSelectedBookIds] = useState<number[]>([]);
    const [selectedMemberIdentifier, setSelectedMemberIdentifier] =
        useState('');
    const [formKey, setFormKey] = useState(0);
    const scannerRef = useRef<QrCameraScannerHandle | null>(null);
    const loanQrScannerRef = useRef<QrCameraScannerHandle | null>(null);
    const qrForm = useForm({
        member_identifier: '',
        verification_payload: '',
        book_ids: [] as number[],
    });
    const loanQrForm = useForm({
        payload: '',
    });
    const qrErrorMessage = getQrErrorMessage(qrForm.errors);
    const loanQrErrorMessage = getQrErrorMessage(loanQrForm.errors);

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

    const handleLoanQrDialogChange = (open: boolean) => {
        setIsLoanQrDialogOpen(open);

        if (!open) {
            loanQrScannerRef.current?.stop();
            setHasDetectedLoanQr(false);
            loanQrForm.reset();
            loanQrForm.clearErrors();
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

    useEffect(() => {
        if (!isLoanQrDialogOpen) {
            return;
        }

        const timer = window.setTimeout(() => {
            void loanQrScannerRef.current?.start();
        }, 80);

        return () => window.clearTimeout(timer);
    }, [isLoanQrDialogOpen]);

    const restartScanner = () => {
        if (qrForm.processing) {
            return;
        }

        setHasDetectedQr(false);
        qrForm.reset();
        qrForm.clearErrors();
        void scannerRef.current?.start();
    };

    const restartLoanQrScanner = () => {
        if (loanQrForm.processing) {
            return;
        }

        setHasDetectedLoanQr(false);
        loanQrForm.reset();
        loanQrForm.clearErrors();
        void loanQrScannerRef.current?.start();
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
                    'QR sudah terbaca, tetapi peminjaman belum berhasil diproses.';

                toast.error(message);
            },
        });
    };

    const submitDetectedLoanPayload = (payload: string) => {
        if (loanQrForm.processing) {
            return;
        }

        setHasDetectedLoanQr(true);
        loanQrForm.clearErrors();
        loanQrForm.setData('payload', payload);
        loanQrForm.post(KioskLoanDraftController.store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                handleLoanQrDialogChange(false);
                setFormKey((current) => current + 1);
            },
            onError: (errors) => {
                const message =
                    getQrErrorMessage(errors) ??
                    'QR sudah terbaca, tetapi peminjaman belum berhasil diproses.';

                toast.error(message);
            },
        });
    };

    const startBorrowVerification = (
        memberIdentifier: string,
        bookIds: number[],
    ) => {
        if (
            memberIdentifier.trim() === '' ||
            bookIds.length === 0 ||
            qrForm.processing
        ) {
            return;
        }

        setSelectedMemberIdentifier(memberIdentifier.trim());
        setSelectedBookIds(bookIds);
        setHasDetectedQr(false);
        qrForm.clearErrors();
        setIsQrDialogOpen(true);
    };

    return (
        <div className="space-y-4">
            <BookActionForm
                key={formKey}
                action={KioskController.borrow()}
                submitLabel="Pinjam Buku"
                description={`Cari buku secara manual atau scan QR peminjaman dari perangkat anggota. Maksimal ${loanMaxBooks} buku per anggota.`}
                maxInputs={loanMaxBooks}
                bookSearchUrl={KioskController.searchBooks.url()}
                bookSearchMode="borrow"
                autoFocus
                memberFieldMode="required"
                onScanQr={() => handleLoanQrDialogChange(true)}
                onActionSubmit={({ memberIdentifier, selectedBooks }) =>
                    startBorrowVerification(
                        memberIdentifier,
                        selectedBooks.map((book) => book.id),
                    )
                }
            />

            <Dialog open={isQrDialogOpen} onOpenChange={handleQrDialogChange}>
                <DialogContent className="max-w-2xl" showCloseButton={false}>
                    <DialogHeader>
                        <DialogTitle>Scan Member Key</DialogTitle>
                        <DialogDescription>
                            Anggota membuka member key dari akun mereka di
                            ponsel. Setelah terbaca, peminjaman akan diproses
                            otomatis.
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
                                <AlertTitle>Member key terbaca</AlertTitle>
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
                                <AlertTitle>Member key sudah terbaca</AlertTitle>
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

            <Dialog
                open={isLoanQrDialogOpen}
                onOpenChange={handleLoanQrDialogChange}
            >
                <DialogContent className="max-w-2xl" showCloseButton={false}>
                    <DialogHeader>
                        <DialogTitle>Scan QR Peminjaman</DialogTitle>
                        <DialogDescription>
                            Arahkan QR peminjaman dari akun anggota ke kamera.
                            Proses berjalan otomatis tanpa member key tambahan.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <QrCameraScanner
                            ref={loanQrScannerRef}
                            onDetected={submitDetectedLoanPayload}
                        />

                        {loanQrForm.processing ? (
                            <Alert>
                                <Spinner />
                                <AlertTitle>QR terbaca</AlertTitle>
                                <AlertDescription>
                                    Sedang memproses peminjaman.
                                </AlertDescription>
                            </Alert>
                        ) : null}

                        {loanQrErrorMessage ? (
                            <Alert variant="destructive">
                                <AlertTitle>
                                    Peminjaman belum berhasil
                                </AlertTitle>
                                <AlertDescription>
                                    {loanQrErrorMessage}
                                </AlertDescription>
                            </Alert>
                        ) : null}

                        {hasDetectedLoanQr &&
                        !loanQrForm.processing &&
                        !loanQrErrorMessage ? (
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
                                onClick={restartLoanQrScanner}
                                disabled={loanQrForm.processing}
                            >
                                Scan Ulang
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => handleLoanQrDialogChange(false)}
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
