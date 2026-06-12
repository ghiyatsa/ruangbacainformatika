import { useForm } from '@inertiajs/react';
import { QrCode, UserIcon } from 'lucide-react';
import { useDeferredValue, useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import KioskReturnDraftController from '@/actions/App/Http/Controllers/KioskReturnDraftController';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
import { Spinner } from '@/components/ui/spinner';
import { KioskField } from '@/features/kiosk/components/KioskField';
import { QrCameraScanner } from '@/features/kiosk/components/QrCameraScanner';
import type { QrCameraScannerHandle } from '@/features/kiosk/components/QrCameraScanner';
import type { KioskBookSearchResult } from '@/features/kiosk/types';

function getQrErrorMessage(
    errors: Record<string, string | undefined>,
): string | null {
    const priorityKeys = [
        'payload',
        'verification_payload',
        'member_identifier',
        'book_ids',
        'book_ids.0',
        'loan_item_ids',
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

export function ReturnForm() {
    const [memberIdentifier, setMemberIdentifier] = useState('');
    const [borrowedBooks, setBorrowedBooks] = useState<KioskBookSearchResult[]>(
        [],
    );
    const [selectedBookIds, setSelectedBookIds] = useState<number[]>([]);
    const [isLoadingBooks, setIsLoadingBooks] = useState(false);
    const [booksError, setBooksError] = useState<string | null>(null);
    const [isMemberKeyDialogOpen, setIsMemberKeyDialogOpen] = useState(false);
    const [isQrDialogOpen, setIsQrDialogOpen] = useState(false);
    const [hasDetectedMemberKey, setHasDetectedMemberKey] = useState(false);
    const [hasDetectedQr, setHasDetectedQr] = useState(false);
    const memberKeyScannerRef = useRef<QrCameraScannerHandle | null>(null);
    const qrScannerRef = useRef<QrCameraScannerHandle | null>(null);
    const deferredMemberIdentifier = useDeferredValue(memberIdentifier.trim());
    const manualForm = useForm({
        member_identifier: '',
        verification_payload: '',
        book_ids: [] as number[],
    });
    const qrForm = useForm({
        payload: '',
    });
    const manualErrorMessage = getQrErrorMessage(manualForm.errors);
    const qrErrorMessage = getQrErrorMessage(qrForm.errors);

    useEffect(() => {
        if (deferredMemberIdentifier === '') {
            const resetTimer = window.setTimeout(() => {
                setBorrowedBooks([]);
                setSelectedBookIds([]);
                setIsLoadingBooks(false);
                setBooksError(null);
            }, 0);

            return () => window.clearTimeout(resetTimer);
        }

        const abortController = new AbortController();
        const searchUrl = new URL(
            KioskController.searchBooks.url(),
            window.location.origin,
        );
        searchUrl.searchParams.set('mode', 'return');
        searchUrl.searchParams.set('member_identifier', deferredMemberIdentifier);

        const loadingTimer = window.setTimeout(() => {
            setIsLoadingBooks(true);
            setBooksError(null);
        }, 120);

        void fetch(searchUrl.toString(), {
            signal: abortController.signal,
        })
            .then(async (response) => {
                if (!response.ok) {
                    throw new Error('Gagal memuat daftar pinjaman.');
                }

                const payload = (await response.json()) as {
                    books?: KioskBookSearchResult[];
                };
                const books = payload.books ?? [];

                setBorrowedBooks(books);
                setSelectedBookIds((current) =>
                    current.filter((bookId) =>
                        books.some((book) => book.id === bookId),
                    ),
                );
            })
            .catch((error: unknown) => {
                if (
                    error instanceof DOMException &&
                    error.name === 'AbortError'
                ) {
                    return;
                }

                setBorrowedBooks([]);
                setSelectedBookIds([]);
                setBooksError('Daftar pinjaman belum bisa dimuat saat ini.');
            })
            .finally(() => {
                window.clearTimeout(loadingTimer);

                if (!abortController.signal.aborted) {
                    setIsLoadingBooks(false);
                }
            });

        return () => {
            window.clearTimeout(loadingTimer);
            abortController.abort();
        };
    }, [deferredMemberIdentifier]);

    useEffect(() => {
        if (!isMemberKeyDialogOpen) {
            return;
        }

        const timer = window.setTimeout(() => {
            void memberKeyScannerRef.current?.start();
        }, 80);

        return () => window.clearTimeout(timer);
    }, [isMemberKeyDialogOpen]);

    useEffect(() => {
        if (!isQrDialogOpen) {
            return;
        }

        const timer = window.setTimeout(() => {
            void qrScannerRef.current?.start();
        }, 80);

        return () => window.clearTimeout(timer);
    }, [isQrDialogOpen]);

    const toggleBookSelection = (bookId: number, checked: boolean) => {
        setSelectedBookIds((current) => {
            if (checked) {
                return current.includes(bookId) ? current : [...current, bookId];
            }

            return current.filter((selectedBookId) => selectedBookId !== bookId);
        });
    };

    const handleMemberKeyDialogChange = (open: boolean) => {
        setIsMemberKeyDialogOpen(open);

        if (!open) {
            memberKeyScannerRef.current?.stop();
            setHasDetectedMemberKey(false);
            manualForm.reset();
            manualForm.clearErrors();
        }
    };

    const handleQrDialogChange = (open: boolean) => {
        setIsQrDialogOpen(open);

        if (!open) {
            qrScannerRef.current?.stop();
            setHasDetectedQr(false);
            qrForm.reset();
            qrForm.clearErrors();
        }
    };

    const restartMemberKeyScanner = () => {
        if (manualForm.processing) {
            return;
        }

        setHasDetectedMemberKey(false);
        manualForm.reset();
        manualForm.clearErrors();
        void memberKeyScannerRef.current?.start();
    };

    const restartQrScanner = () => {
        if (qrForm.processing) {
            return;
        }

        setHasDetectedQr(false);
        qrForm.reset();
        qrForm.clearErrors();
        void qrScannerRef.current?.start();
    };

    const submitDetectedMemberKey = (payload: string) => {
        if (manualForm.processing || selectedBookIds.length === 0) {
            return;
        }

        setHasDetectedMemberKey(true);
        manualForm.clearErrors();
        manualForm.setData({
            member_identifier: memberIdentifier.trim(),
            verification_payload: payload,
            book_ids: selectedBookIds,
        });
        manualForm.post(KioskController.storeReturn().url, {
            preserveScroll: true,
            onSuccess: () => {
                handleMemberKeyDialogChange(false);
                setMemberIdentifier('');
                setBorrowedBooks([]);
                setSelectedBookIds([]);
            },
            onError: (errors) => {
                const message =
                    getQrErrorMessage(errors) ??
                    'Member key terbaca, tetapi pengembalian belum berhasil diproses.';

                toast.error(message);
            },
        });
    };

    const submitDetectedQr = (payload: string) => {
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
                setMemberIdentifier('');
                setBorrowedBooks([]);
                setSelectedBookIds([]);
            },
            onError: (errors) => {
                const message =
                    getQrErrorMessage(errors) ??
                    'QR sudah terbaca, tetapi pengembalian belum berhasil diproses.';

                toast.error(message);
            },
        });
    };

    const canSubmitManualReturn =
        memberIdentifier.trim() !== '' &&
        selectedBookIds.length > 0 &&
        !manualForm.processing;

    return (
        <div className="space-y-5">
            <div className="grid gap-4">
                <KioskField
                    label="NIM, Email, atau No. HP"
                    htmlFor="return-member"
                    error={manualForm.errors.member_identifier}
                    required
                >
                    <div className="flex flex-col gap-2 sm:flex-row">
                        <InputGroup className="flex-1">
                            <InputGroupInput
                                id="return-member"
                                autoFocus
                                autoComplete="new-password"
                                autoCapitalize="none"
                                autoCorrect="off"
                                spellCheck={false}
                                data-lpignore="true"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                placeholder="NIM, email, atau no. HP"
                                value={memberIdentifier}
                                onChange={(event) => {
                                    setMemberIdentifier(event.target.value);
                                    manualForm.clearErrors('member_identifier');
                                }}
                                aria-invalid={Boolean(
                                    manualForm.errors.member_identifier,
                                )}
                                className="h-full text-base"
                            />
                            <InputGroupAddon>
                                {isLoadingBooks ? <Spinner /> : <UserIcon />}
                            </InputGroupAddon>
                        </InputGroup>
                        <Button
                            type="button"
                            variant="secondary"
                            className="shrink-0 rounded-md px-4 text-sm font-medium"
                            onClick={() => handleQrDialogChange(true)}
                        >
                            <QrCode className="size-4" />
                            Scan QR
                        </Button>
                    </div>
                </KioskField>

                {booksError ? (
                    <Alert variant="destructive">
                        <AlertTitle>Daftar pinjaman belum tersedia</AlertTitle>
                        <AlertDescription>{booksError}</AlertDescription>
                    </Alert>
                ) : null}

                <div className="rounded-lg border border-border/70">
                    <div className="flex items-center justify-between gap-3 border-b border-border/70 px-4 py-3">
                        <p className="text-sm font-semibold text-foreground">
                            Buku yang sedang dipinjam
                        </p>
                        <span className="text-xs font-medium text-muted-foreground">
                            {selectedBookIds.length} dipilih
                        </span>
                    </div>

                    <div className="max-h-[24rem] overflow-y-auto p-3">
                        {memberIdentifier.trim() === '' ? (
                            <p className="px-2 py-6 text-center text-sm text-muted-foreground">
                                Masukkan identitas anggota untuk melihat daftar
                                pinjaman aktif.
                            </p>
                        ) : isLoadingBooks ? (
                            <div className="flex items-center justify-center gap-2 px-2 py-6 text-sm text-muted-foreground">
                                <Spinner />
                                Memuat pinjaman aktif...
                            </div>
                        ) : borrowedBooks.length > 0 ? (
                            <div className="grid gap-2">
                                {borrowedBooks.map((book) => {
                                    const isSelected = selectedBookIds.includes(
                                        book.id,
                                    );

                                    return (
                                        <label
                                            key={book.id}
                                            className={`flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition hover:bg-accent/40 ${
                                                isSelected
                                                    ? 'border-primary/50 bg-primary/5'
                                                    : 'border-border/60'
                                            }`}
                                        >
                                            <Checkbox
                                                checked={isSelected}
                                                onCheckedChange={(checked) =>
                                                    toggleBookSelection(
                                                        book.id,
                                                        checked === true,
                                                    )
                                                }
                                                aria-label={`Pilih ${book.title}`}
                                            />
                                            <img
                                                src={book.coverImageUrl}
                                                alt={book.title}
                                                className="h-16 w-12 shrink-0 rounded-md border border-border/70 object-cover"
                                                loading="lazy"
                                            />
                                            <span className="min-w-0 flex-1">
                                                <span className="line-clamp-1 text-sm font-semibold text-foreground">
                                                    {book.title}
                                                </span>
                                                <span className="mt-1 block line-clamp-1 text-xs text-muted-foreground">
                                                    {book.authors?.join(', ') ||
                                                        'Penulis belum tersedia'}
                                                </span>
                                                <span className="mt-1 block text-xs text-muted-foreground">
                                                    {book.isbn
                                                        ? `ISBN ${book.isbn}`
                                                        : book.issn
                                                          ? `ISSN ${book.issn}`
                                                          : 'Tanpa ISBN/ISSN'}
                                                </span>
                                            </span>
                                        </label>
                                    );
                                })}
                            </div>
                        ) : (
                            <p className="px-2 py-6 text-center text-sm text-muted-foreground">
                                Tidak ada pinjaman aktif untuk identitas ini.
                            </p>
                        )}
                    </div>
                </div>

                {manualErrorMessage ? (
                    <Alert variant="destructive">
                        <AlertTitle>Pengembalian belum berhasil</AlertTitle>
                        <AlertDescription>{manualErrorMessage}</AlertDescription>
                    </Alert>
                ) : null}

                <Button
                    type="button"
                    size="lg"
                    className="h-12 w-full text-base"
                    disabled={!canSubmitManualReturn}
                    onClick={() => handleMemberKeyDialogChange(true)}
                >
                    Kembalikan Buku
                </Button>
            </div>

            <Dialog
                open={isMemberKeyDialogOpen}
                onOpenChange={handleMemberKeyDialogChange}
            >
                <DialogContent className="max-w-2xl" showCloseButton={false}>
                    <DialogHeader>
                        <DialogTitle>Scan Member Key</DialogTitle>
                        <DialogDescription>
                            Anggota membuka member key dari akun mereka di
                            ponsel. Identitas yang diinput harus sesuai dengan
                            member key.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <QrCameraScanner
                            ref={memberKeyScannerRef}
                            onDetected={submitDetectedMemberKey}
                        />

                        {manualForm.processing ? (
                            <Alert>
                                <Spinner />
                                <AlertTitle>Member key terbaca</AlertTitle>
                                <AlertDescription>
                                    Sedang memproses pengembalian.
                                </AlertDescription>
                            </Alert>
                        ) : null}

                        {manualErrorMessage ? (
                            <Alert variant="destructive">
                                <AlertTitle>
                                    Pengembalian belum berhasil
                                </AlertTitle>
                                <AlertDescription>
                                    {manualErrorMessage}
                                </AlertDescription>
                            </Alert>
                        ) : null}

                        {hasDetectedMemberKey &&
                        !manualForm.processing &&
                        !manualErrorMessage ? (
                            <Alert>
                                <AlertTitle>
                                    Member key sudah terbaca
                                </AlertTitle>
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
                                onClick={restartMemberKeyScanner}
                                disabled={manualForm.processing}
                            >
                                Scan Ulang
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    handleMemberKeyDialogChange(false)
                                }
                            >
                                Tutup
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            <Dialog open={isQrDialogOpen} onOpenChange={handleQrDialogChange}>
                <DialogContent className="max-w-2xl" showCloseButton={false}>
                    <DialogHeader>
                        <DialogTitle>Scan QR Pengembalian</DialogTitle>
                        <DialogDescription>
                            Arahkan QR pengembalian dari akun anggota ke kamera.
                            Proses berjalan otomatis.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <QrCameraScanner
                            ref={qrScannerRef}
                            onDetected={submitDetectedQr}
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
                                onClick={restartQrScanner}
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
