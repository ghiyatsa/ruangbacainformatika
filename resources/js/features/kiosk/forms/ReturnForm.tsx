import { useForm } from '@inertiajs/react';
import { UserIcon } from 'lucide-react';
import { useDeferredValue, useEffect, useRef, useState } from 'react';
import { lazy, Suspense } from 'react';
import { toast } from 'sonner';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
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
import type { QrCameraScannerHandle } from '@/features/kiosk/components/QrCameraScanner';
import type { KioskBookSearchResult } from '@/features/kiosk/types';

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
    const [hasDetectedMemberKey, setHasDetectedMemberKey] = useState(false);
    const memberKeyScannerRef = useRef<QrCameraScannerHandle | null>(null);
    const deferredMemberIdentifier = useDeferredValue(memberIdentifier.trim());
    const manualForm = useForm({
        member_identifier: '',
        verification_payload: '',
        book_ids: [] as number[],
    });
    const manualErrorMessage = getQrErrorMessage(manualForm.errors);

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
            KioskController.searchBooks().url,
            window.location.origin,
        );
        searchUrl.searchParams.set('mode', 'return');
        searchUrl.searchParams.set(
            'member_identifier',
            deferredMemberIdentifier,
        );

        const fetchBorrowedBooks = async () => {
            setIsLoadingBooks(true);
            setBooksError(null);

            try {
                const response = await fetch(searchUrl.toString(), {
                    signal: abortController.signal,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat daftar buku.');
                }

                const data = (await response.json()) as {
                    books?: KioskBookSearchResult[];
                };

                const books = Array.isArray(data.books) ? data.books : [];
                setBorrowedBooks(books);
                setSelectedBookIds(books.map((book) => book.id));
            } catch (error) {
                if (
                    typeof error === 'object' &&
                    error !== null &&
                    'name' in error &&
                    error.name === 'AbortError'
                ) {
                    return;
                }

                setBorrowedBooks([]);
                setSelectedBookIds([]);
                setBooksError(
                    'Tidak dapat memuat buku pinjaman. Pastikan identitas terdaftar.',
                );
            } finally {
                setIsLoadingBooks(false);
            }
        };

        void fetchBorrowedBooks();

        return () => {
            abortController.abort();
        };
    }, [deferredMemberIdentifier]);

    const handleMemberKeyDialogChange = (open: boolean) => {
        setIsMemberKeyDialogOpen(open);

        if (!open) {
            memberKeyScannerRef.current?.stop();
            setHasDetectedMemberKey(false);
            manualForm.setData('verification_payload', '');
            manualForm.clearErrors();
        }
    };

    useEffect(() => {
        if (!isMemberKeyDialogOpen) {
            return;
        }

        const timer = window.setTimeout(() => {
            void memberKeyScannerRef.current?.start();
        }, 80);

        return () => window.clearTimeout(timer);
    }, [isMemberKeyDialogOpen]);

    const toggleBookSelection = (bookId: number, checked: boolean) => {
        setSelectedBookIds((current) => {
            if (checked) {
                return current.includes(bookId)
                    ? current
                    : [...current, bookId];
            }

            return current.filter((id) => id !== bookId);
        });
    };

    const restartMemberKeyScanner = () => {
        if (manualForm.processing) {
            return;
        }

        setHasDetectedMemberKey(false);
        manualForm.setData('verification_payload', '');
        manualForm.clearErrors();
        void memberKeyScannerRef.current?.start();
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
                    'Member key terbaca, namun pengembalian belum berhasil.';

                toast.error(message);
            },
        });
    };

    const openMemberKeyDialog = () => {
        if (memberIdentifier.trim() === '') {
            toast.error('Masukkan NIM atau email anggota terlebih dahulu.');

            return;
        }

        if (selectedBookIds.length === 0) {
            toast.error('Pilih minimal satu buku untuk dikembalikan.');

            return;
        }

        manualForm.setData({
            member_identifier: memberIdentifier.trim(),
            verification_payload: '',
            book_ids: selectedBookIds,
        });

        setIsMemberKeyDialogOpen(true);
    };

    return (
        <div className="space-y-6">
            <div className="space-y-4">
                <KioskField
                    label="Identitas Anggota"
                    htmlFor="return_member_identifier"
                    required
                >
                    <InputGroup>
                        <InputGroupAddon>
                            <UserIcon className="size-4" />
                        </InputGroupAddon>
                        <InputGroupInput
                            id="return_member_identifier"
                            autoFocus
                            value={memberIdentifier}
                            onChange={(event) =>
                                setMemberIdentifier(event.target.value)
                            }
                            placeholder="Contoh: 210170001 atau nama@unimal.ac.id"
                            autoComplete="off"
                        />
                    </InputGroup>
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
                                                width={48}
                                                height={64}
                                                className="h-16 w-12 shrink-0 rounded-md border border-border/70 object-cover"
                                                loading="lazy"
                                            />
                                            <span className="min-w-0 flex-1">
                                                <span className="line-clamp-1 text-sm font-semibold text-foreground">
                                                    {book.title}
                                                </span>
                                                <span className="mt-1 line-clamp-1 block text-xs text-muted-foreground">
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
                        <AlertDescription>
                            {manualErrorMessage}
                        </AlertDescription>
                    </Alert>
                ) : null}

                <Button
                    type="button"
                    size="lg"
                    className="w-full"
                    disabled={
                        memberIdentifier.trim() === '' ||
                        selectedBookIds.length === 0 ||
                        manualForm.processing
                    }
                    onClick={openMemberKeyDialog}
                >
                    {manualForm.processing ? (
                        <>
                            <Spinner />
                            Memproses...
                        </>
                    ) : (
                        `Kembalikan ${selectedBookIds.length} Buku`
                    )}
                </Button>
            </div>

            <Dialog
                open={isMemberKeyDialogOpen}
                onOpenChange={handleMemberKeyDialogChange}
            >
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Verifikasi Pengembalian</DialogTitle>
                        <DialogDescription>
                            Arahkan kode QR <strong>Member Key</strong> dari HP
                            Anda ke kamera untuk menyelesaikan pengembalian.
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
                                    ref={memberKeyScannerRef}
                                    onDetected={submitDetectedMemberKey}
                                />
                            </Suspense>
                        </div>

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

                        {hasDetectedMemberKey && manualForm.processing ? (
                            <div className="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                                <Spinner className="size-4" />
                                Menyelesaikan pengembalian...
                            </div>
                        ) : (
                            <Button
                                type="button"
                                variant="outline"
                                className="w-full"
                                onClick={restartMemberKeyScanner}
                                disabled={manualForm.processing}
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
