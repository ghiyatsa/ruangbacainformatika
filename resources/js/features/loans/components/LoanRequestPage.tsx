import { Form, Head, Link } from '@inertiajs/react';
import { QrCode, ShoppingCart, Trash2 } from 'lucide-react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import LoanRequestController from '@/actions/App/Http/Controllers/LoanRequestController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

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
        hasActiveQr: boolean;
        qrCodeSvg: string | null;
        qrPayload: string | null;
        items: LoanRequestItem[];
    };
    stats: {
        loanMaxBooks: number;
    };
}

export default function LoanRequestPage({ draft, stats }: Props) {
    const isEmpty = draft.items.length === 0;

    return (
        <>
            <Head title="QR Peminjaman" />

            <div className="container mx-auto max-w-5xl py-8 pb-16">
                <div className="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight text-foreground">
                            Keranjang dan QR Peminjaman
                        </h1>
                        <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                            Pilih buku dari katalog, generate QR, cukup scan
                            untuk meminjam buku.
                        </p>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.9fr)]">
                    <Card className="border-border/70">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <ShoppingCart className="size-5 text-primary" />
                                Keranjang Pinjam
                            </CardTitle>
                            <CardDescription>
                                {draft.itemsCount} dari {stats.loanMaxBooks}{' '}
                                buku sudah dipilih untuk dipinjam.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {isEmpty ? (
                                <div className="rounded-2xl border border-dashed border-border/70 bg-muted/20 px-5 py-10 text-center">
                                    <p className="text-sm text-muted-foreground">
                                        Belum ada buku di keranjang peminjaman.
                                    </p>
                                </div>
                            ) : (
                                draft.items.map((item) => (
                                    <div
                                        key={item.id}
                                        className="rounded-2xl border border-border/60 bg-card/70 p-4"
                                    >
                                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                            <div className="min-w-0 space-y-1.5">
                                                <Link
                                                    href={BookController.show(
                                                        item.slug,
                                                    )}
                                                    className="line-clamp-2 text-base font-semibold text-foreground transition-colors hover:text-primary"
                                                >
                                                    {item.title}
                                                </Link>
                                                <p className="text-sm text-muted-foreground">
                                                    {item.authors.join(', ') ||
                                                        'Penulis belum tersedia'}{' '}
                                                    :{' '}
                                                    {item.isbn
                                                        ? `ISBN ${item.isbn}`
                                                        : item.issn
                                                          ? `ISSN ${item.issn}`
                                                          : 'Tanpa ISBN/ISSN'}
                                                </p>
                                            </div>

                                            <div className="flex items-center gap-2">
                                                <Badge variant="secondary">
                                                    {item.availableItemsCount}{' '}
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
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card className="border-border/70">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <QrCode className="size-5 text-primary" />
                                QR
                            </CardTitle>
                            <CardDescription>
                                QR berlaku singkat dan akan otomatis tidak valid
                                setelah dipakai atau saat keranjang berubah.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-5">
                            {draft.qrCodeSvg ? (
                                <div className="rounded-3xl border border-border/70 bg-white p-4 shadow-sm">
                                    <div
                                        className="mx-auto w-full max-w-55"
                                        dangerouslySetInnerHTML={{
                                            __html: draft.qrCodeSvg,
                                        }}
                                    />
                                </div>
                            ) : (
                                <div className="rounded-2xl border border-dashed border-border/70 bg-muted/20 px-5 py-10 text-center text-sm text-muted-foreground">
                                    Generate QR setelah keranjang siap
                                    dipinjamkan.
                                </div>
                            )}

                            {draft.qrPayload ? (
                                <div className="rounded-2xl border border-border/70 bg-muted/20 p-4">
                                    <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                                        Token Scan
                                    </p>
                                    <p className="mt-2 font-mono text-xs break-all text-foreground">
                                        {draft.qrPayload}
                                    </p>
                                </div>
                            ) : null}

                            {draft.expiresAt ? (
                                <p className="text-sm text-muted-foreground">
                                    Berlaku sampai {draft.expiresAt}.
                                </p>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    Belum ada QR aktif untuk keranjang ini.
                                </p>
                            )}

                            <Form {...LoanRequestController.generateQr.form()}>
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        size="lg"
                                        className="w-full"
                                        disabled={processing || isEmpty}
                                    >
                                        <QrCode className="size-4" />
                                        {draft.hasActiveQr
                                            ? 'Generate Ulang QR'
                                            : 'Generate QR Peminjaman'}
                                    </Button>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
