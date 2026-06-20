import { Head, Link, useForm } from '@inertiajs/react';
import { Download, QrCode, Trash2 } from 'lucide-react';
import { useEffect, useMemo } from 'react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import LoanRequestController from '@/actions/App/Http/Controllers/LoanRequestController';
import InputError from '@/components/common/InputError';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
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
        <>
            <Head title="QR Peminjaman" />

            <div className="container mx-auto max-w-5xl px-4 py-8 pb-16 sm:px-6 lg:px-8">
                <div className="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.9fr)]">
                    <Card className="border-border/70">
                        <CardHeader>
                            <CardTitle>Keranjang Peminjaman</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {isEmpty ? (
                                <div className="rounded-2xl border border-dashed border-border/70 bg-muted/20 px-5 py-10 text-center">
                                    <p className="text-sm text-muted-foreground">
                                        Keranjang masih kosong.
                                    </p>
                                </div>
                            ) : (
                                <ScrollArea className="h-[28rem] rounded-2xl">
                                    <div className="space-y-4 p-1.5 pr-3">
                                        {draft.items.map((item) => {
                                            const isSelected =
                                                selectedBookIds.includes(
                                                    item.bookId,
                                                );
                                            const disableUncheckedSelection =
                                                !isSelected &&
                                                selectedBooksCount >=
                                                    remainingQuota;

                                            return (
                                                <div
                                                    key={item.id}
                                                    className="rounded-2xl border border-border/60 bg-card/70 p-4"
                                                >
                                                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                                        <div className="flex min-w-0 items-start gap-3">
                                                            <div className="pt-0.5">
                                                                <Checkbox
                                                                    id={`loan-request-book-${item.id}`}
                                                                    checked={
                                                                        isSelected
                                                                    }
                                                                    disabled={
                                                                        remainingQuota <
                                                                            1 ||
                                                                        disableUncheckedSelection
                                                                    }
                                                                    onCheckedChange={(
                                                                        checked,
                                                                    ) =>
                                                                        toggleBookSelection(
                                                                            item.bookId,
                                                                            checked ===
                                                                                true,
                                                                        )
                                                                    }
                                                                />
                                                            </div>
                                                            <div className="min-w-0 space-y-1.5">
                                                                <Label
                                                                    htmlFor={`loan-request-book-${item.id}`}
                                                                    className="cursor-pointer p-0 text-left text-sm font-medium"
                                                                >
                                                                    Pilih buku
                                                                </Label>
                                                                <Link
                                                                    href={BookController.show(
                                                                        item.slug,
                                                                    )}
                                                                    instant
                                                                    component="books/show"
                                                                    pageProps={instantLoadingPageProps()}
                                                                    className="line-clamp-2 text-base font-semibold text-foreground transition-colors hover:text-primary"
                                                                >
                                                                    {item.title}
                                                                </Link>
                                                                <p className="text-sm text-muted-foreground">
                                                                    {item.authors.join(
                                                                        ', ',
                                                                    ) ||
                                                                        'Penulis belum tersedia'}{' '}
                                                                    :{' '}
                                                                    {item.isbn
                                                                        ? `ISBN ${item.isbn}`
                                                                        : item.issn
                                                                          ? `ISSN ${item.issn}`
                                                                          : 'Tanpa ISBN/ISSN'}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div className="flex items-center gap-2">
                                                            {isSelected ? (
                                                                <Badge>
                                                                    Dipilih
                                                                </Badge>
                                                            ) : null}
                                                            <Badge variant="secondary">
                                                                {
                                                                    item.availableItemsCount
                                                                }{' '}
                                                                tersedia
                                                            </Badge>
                                                            <Link
                                                                href={LoanRequestController.destroyBook(
                                                                    item.bookId,
                                                                )}
                                                                method="delete"
                                                                as="button"
                                                                className="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-border px-3 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                                                            >
                                                                <Trash2 className="size-4" />
                                                                Hapus
                                                            </Link>
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </ScrollArea>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="border-border/70">
                        <CardHeader>
                            <CardTitle>QR</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-5">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                                        Pilihan
                                    </p>
                                    <p className="mt-2 text-sm font-medium text-foreground">
                                        {selectedBooksCount} dari{' '}
                                        {draft.itemsCount} buku dipilih
                                    </p>
                                </div>

                                <Badge variant="secondary">
                                    Kuota {remainingQuota}
                                </Badge>
                            </div>

                            <InputError
                                message={
                                    typeof qrForm.errors.book_ids === 'string'
                                        ? qrForm.errors.book_ids
                                        : undefined
                                }
                            />
                            <InputError
                                message={
                                    typeof (
                                        qrForm.errors as Record<string, unknown>
                                    ).draft === 'string'
                                        ? ((
                                              qrForm.errors as Record<
                                                  string,
                                                  unknown
                                              >
                                          ).draft as string)
                                        : undefined
                                }
                            />

                            <div className="mt-5 rounded-[1.75rem] border border-border/70 bg-card px-5 py-6 shadow-sm">
                                {draft.qrCodeSvg && countdownLabel ? (
                                    <div className="mb-5 text-center">
                                        <p className="text-[11px] font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                            Berlaku dalam
                                        </p>
                                        <p
                                            className="mt-2 text-4xl font-semibold tracking-tight text-foreground tabular-nums"
                                            title={
                                                draft.expiresAt
                                                    ? `Berlaku sampai ${draft.expiresAt}`
                                                    : undefined
                                            }
                                        >
                                            {countdownLabel}
                                        </p>
                                    </div>
                                ) : null}

                                {draft.qrCodeSvg ? (
                                    <div className="space-y-4">
                                        <div className="mx-auto w-max rounded-3xl border border-border/50 bg-white p-4 text-primary shadow-sm dark:bg-card dark:text-white">
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
                                                    `qr-peminjaman-${draft.id}.png`,
                                                    'QR PEMINJAMAN',
                                                )
                                            }
                                        >
                                            <Download className="size-4" />
                                            Unduh QR
                                        </Button>
                                    </div>
                                ) : (
                                    <div className="rounded-2xl bg-muted/20 px-5 py-12 text-center text-sm text-muted-foreground">
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
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
