import { Link, useForm } from '@inertiajs/react';
import { Download, QrCode, Trash2 } from 'lucide-react';
import { useEffect, useMemo } from 'react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import LoanRequestController from '@/actions/App/Http/Controllers/LoanRequestController';
import InputError from '@/components/common/InputError';
import { PageLayout } from '@/components/layout/PageLayout';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useCountdown } from '@/hooks/use-countdown';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import { downloadSvgAsPng } from '@/lib/utils';
import type { FormEvent } from 'react';

interface LoanRequestItem {
    id: number;
    bookId: number;
    title: string;
    slug: string;
    authors: string[];
    isbn: string | null;
    issn: string | null;
    availableItemsCount: number;
}

interface Props {
    draft: {
        id: number;
        status: string;
        itemsCount: number;
        expiresAt: string | null;
        expiresAtIso: string | null;
        hasActiveQr: boolean;
        qrCodeSvg: string | null;
        selectedBookIds: number[];
        items: LoanRequestItem[];
    };
    stats: {
        loanMaxBooks: number;
        activeLoansCount: number;
    };
}

export default function LoanRequestPage({ draft, stats }: Props) {
    const isEmpty = draft.items.length === 0;
    const remainingQuota = Math.max(
        stats.loanMaxBooks - stats.activeLoansCount,
        0,
    );
    const defaultSelectedBookIds = useMemo(() => {
        if (draft.selectedBookIds.length > 0) {
            return draft.selectedBookIds;
        }

        return draft.items.slice(0, remainingQuota).map((item) => item.bookId);
    }, [draft.items, draft.selectedBookIds, remainingQuota]);
    const qrForm = useForm<{
        book_ids: number[];
    }>({
        book_ids: defaultSelectedBookIds,
    });
    const { clearErrors, setData } = qrForm;
    const selectedBookIds = qrForm.data.book_ids;
    const selectedBooksCount = selectedBookIds.length;
    const { remainingSeconds, countdownLabel } = useCountdown(
        draft.expiresAtIso,
    );
    const hasActiveQrCountdown =
        draft.hasActiveQr && remainingSeconds !== null && remainingSeconds > 0;

    useEffect(() => {
        setData('book_ids', defaultSelectedBookIds);
        clearErrors();
    }, [clearErrors, draft.id, defaultSelectedBookIds, setData]);

    const toggleBookSelection = (bookId: number, checked: boolean) => {
        const currentBookIds = qrForm.data.book_ids;

        if (checked) {
            if (currentBookIds.includes(bookId)) {
                return;
            }

            if (currentBookIds.length >= remainingQuota) {
                return;
            }

            qrForm.setData('book_ids', [...currentBookIds, bookId]);
        } else {
            qrForm.setData(
                'book_ids',
                currentBookIds.filter(
                    (currentBookId) => currentBookId !== bookId,
                ),
            );
        }

        qrForm.clearErrors('book_ids');
    };

    const submitQrRequest = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        qrForm.post(LoanRequestController.generateQr.url(), {
            preserveScroll: true,
        });
    };

    return (
        <PageLayout
            title="Keranjang Peminjaman"
            metaDescription="Ajukan peminjaman buku melalui scan QR."
            maxWidth="7xl"
            className="pt-0 pb-16"
            showDesktopNoticeInContent={false}
            header={
                <div className="relative -mt-20 overflow-hidden bg-background sm:-mt-28 md:-mt-24">
                    <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-12 sm:px-6 sm:pt-30 lg:px-8">
                        <div className="flex flex-col gap-4">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                                    Keranjang Peminjaman
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            }
        >
            <div className="grid gap-10 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.9fr)]">
                {/* Left Column: Items List */}
                <div className="space-y-6">
                    <div className="border-b pb-3">
                        <h2 className="text-xl font-bold tracking-tight text-foreground">
                            Buku yang Dipilih
                        </h2>
                    </div>

                    <div className="space-y-4">
                        {isEmpty ? (
                            <div className="border border-dashed border-border/60 bg-muted/10 px-5 py-12 text-center">
                                <p className="text-sm text-muted-foreground">
                                    Keranjang masih kosong.
                                </p>
                            </div>
                        ) : (
                            <ScrollArea className="h-[32rem] pr-3">
                                <div className="divide-y divide-border/60">
                                    {draft.items.map((item) => {
                                        const isSelected =
                                            selectedBookIds.includes(
                                                item.bookId,
                                            );
                                        const isSelectionDisabled =
                                            !isSelected &&
                                            selectedBookIds.length >=
                                                remainingQuota;

                                        return (
                                            <div
                                                key={item.id}
                                                className="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0"
                                            >
                                                <div className="flex items-center gap-3 min-w-0">
                                                    <Checkbox
                                                        id={"book-" + item.bookId}
                                                        checked={isSelected}
                                                        disabled={
                                                            isSelectionDisabled ||
                                                            hasActiveQrCountdown
                                                        }
                                                        onCheckedChange={(
                                                            checked,
                                                        ) =>
                                                            toggleBookSelection(
                                                                item.bookId,
                                                                !!checked,
                                                            )
                                                        }
                                                    />
                                                    <div className="min-w-0">
                                                        <Link
                                                            href={BookController.show.url(
                                                                item.slug,
                                                            )}
                                                            instant
                                                            component="books/show"
                                                            pageProps={instantLoadingPageProps()}
                                                            className="text-sm font-bold text-foreground hover:text-primary transition-colors block truncate"
                                                        >
                                                            {item.title}
                                                        </Link>
                                                        <p className="text-xs text-muted-foreground truncate mt-0.5">
                                                            {item.authors.join(
                                                                ', ',
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>

                                                <Link
                                                    href={LoanRequestController.destroyBook.url(
                                                        item.bookId,
                                                    )}
                                                    method="delete"
                                                    as="button"
                                                    type="button"
                                                    preserveScroll
                                                    className="text-muted-foreground hover:text-destructive transition-colors shrink-0 p-1.5 hover:bg-destructive/10 rounded-lg cursor-pointer"
                                                >
                                                    <Trash2 className="size-4" />
                                                    <span className="sr-only">
                                                        Hapus dari keranjang
                                                    </span>
                                                </Link>
                                            </div>
                                        );
                                    })}
                                </div>
                            </ScrollArea>
                        )}
                    </div>
                </div>

                {/* Right Column: QR Code Panel */}
                <div className="space-y-6">
                    <div className="border-b pb-3">
                        <h2 className="text-xl font-bold tracking-tight text-foreground">
                            QR Peminjaman
                        </h2>
                    </div>

                    <div className="space-y-6">
                        <div className="space-y-3.5 text-sm">
                            <div className="flex justify-between border-b pb-2">
                                <span className="text-muted-foreground">
                                    Buku di keranjang:
                                </span>
                                <span className="font-bold text-foreground">
                                    {draft.items.length} buku
                                </span>
                            </div>

                            <div className="flex justify-between border-b pb-2">
                                <span className="text-muted-foreground">
                                    Sisa kuota pinjam:
                                </span>
                                <span className="font-bold text-foreground">
                                    {remainingQuota} buku
                                </span>
                            </div>
                            <div className="flex justify-between border-b pb-2">
                                <span className="text-muted-foreground">
                                    Dipilih untuk QR:
                                </span>
                                <span className="font-bold text-primary">
                                    {selectedBooksCount} buku
                                </span>
                            </div>
                        </div>

                        {qrForm.errors.book_ids && (
                            <InputError
                                message={qrForm.errors.book_ids}
                                className="mt-1"
                            />
                        )}

                        <InputError
                            message={
                                (qrForm.errors as any)[
                                    "book_ids." + selectedBookIds.indexOf(
                                        (qrForm.errors &&
                                            Object.keys(qrForm.errors)
                                                .filter((key) =>
                                                    key.startsWith(
                                                        'book_ids.',
                                                    ),
                                                )
                                                .map((key) =>
                                                    parseInt(
                                                        key.replace(
                                                            'book_ids.',
                                                            '',
                                                        ),
                                                    ),
                                                )
                                                .map(
                                                    (index) =>
                                                        selectedBookIds[index],
                                                )
                                                .shift()) ?? -1,
                                    )
                                ]
                            }
                            className="mt-1"
                        />

                        <InputError
                            message={
                                qrForm.errors &&
                                Object.keys(qrForm.errors)
                                    .filter((key) => key.startsWith('draft.'))
                                    .map(
                                        (key) =>
                                            (qrForm.errors &&
                                                (qrForm.errors[
                                                    key as any
                                                ] as string)) ??
                                            '',
                                    )
                                    .shift()
                            }
                            className="mt-1"
                        />

                        <div className="border border-border/60 bg-muted/5 p-6">
                            {draft.qrCodeSvg && countdownLabel ? (
                                <div className="mb-5 text-center">
                                    <p className="text-[11px] font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Berlaku dalam
                                    </p>
                                    <p
                                        className="mt-2 text-4xl font-semibold tracking-tight text-foreground tabular-nums"
                                        title={
                                            draft.expiresAt
                                                ? "Berlaku sampai " + draft.expiresAt
                                                : undefined
                                        }
                                    >
                                        {countdownLabel}
                                    </p>
                                </div>
                            ) : null}

                            {draft.qrCodeSvg ? (
                                <div className="space-y-4">
                                    <div className="mx-auto w-max border border-border/60 bg-white p-4 text-primary dark:bg-zinc-900 dark:text-white">
                                        <div
                                            className="flex justify-center [&_svg]:mx-auto"
                                            dangerouslySetInnerHTML={{
                                                __html: draft.qrCodeSvg,
                                            }}
                                        />
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        className="mx-auto flex items-center gap-2"
                                        onClick={() =>
                                            downloadSvgAsPng(
                                                draft.qrCodeSvg!,
                                                "qr-peminjaman-" + draft.id + ".png",
                                                'QR PEMINJAMAN',
                                            )
                                        }
                                    >
                                        <Download className="size-4" />
                                        Unduh QR
                                    </Button>
                                </div>
                            ) : (
                                <div className="bg-muted/10 border border-dashed border-border/60 p-12 text-center text-sm text-muted-foreground">
                                    QR siap dibuat.
                                </div>
                            )}
                        </div>

                        <form
                            onSubmit={submitQrRequest}
                            className="space-y-3"
                        >
                            {selectedBookIds.map((bookId) => (
                                <input
                                    key={bookId}
                                    type="hidden"
                                    name="book_ids[]"
                                    value={bookId}
                                />
                            ))}

                            <Button
                                type="submit"
                                size="lg"
                                className="w-full"
                                disabled={
                                    qrForm.processing ||
                                    isEmpty ||
                                    hasActiveQrCountdown ||
                                    remainingQuota < 1 ||
                                    selectedBooksCount === 0
                                }
                            >
                                <QrCode className="size-4" />
                                Buat QR Peminjaman
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </PageLayout>
    );
}
