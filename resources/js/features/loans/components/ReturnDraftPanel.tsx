import { Download, QrCode } from 'lucide-react';
import InputError from '@/components/common/InputError';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { downloadSvgAsPng } from '@/lib/utils';
import type { ReturnDraftPayload } from '@/features/loans/types';

interface ReturnDraftPanelProps {
    returnDraft: ReturnDraftPayload;
    activeLoanCount: number;
    selectedItemsCount: number;
    countdownLabel: string | null;
    hasActiveQrCountdown: boolean;
    isProcessing: boolean;
    errors: Record<string, unknown>;
    onSubmit: (event: React.FormEvent<HTMLFormElement>) => void;
}

function getFormError(
    errors: Record<string, unknown>,
    field: string,
): string | undefined {
    return typeof errors[field] === 'string'
        ? (errors[field] as string)
        : undefined;
}

export function ReturnDraftPanel({
    returnDraft,
    activeLoanCount,
    selectedItemsCount,
    countdownLabel,
    hasActiveQrCountdown,
    isProcessing,
    errors,
    onSubmit,
}: ReturnDraftPanelProps) {
    return (
        <Card className="border-border/70 xl:sticky xl:top-24 xl:h-fit">
            <CardHeader>
                <CardTitle>QR Pengembalian</CardTitle>
            </CardHeader>
            <CardContent className="space-y-5">
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                            Pilihan
                        </p>
                        <p className="mt-2 text-sm font-medium text-foreground">
                            {selectedItemsCount} buku dipilih
                        </p>
                    </div>

                    <Badge variant="secondary">
                        {activeLoanCount} aktif
                    </Badge>
                </div>

                <InputError message={getFormError(errors, 'loan_item_ids')} />
                <InputError message={getFormError(errors, 'draft')} />

                {returnDraft.items.length > 0 ? (
                    <div className="rounded-2xl border border-border/60 bg-muted/15 p-4">
                        <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                            Di QR
                        </p>
                        <div className="mt-3 space-y-3">
                            {returnDraft.items.map((item) => (
                                <div
                                    key={item.loanItemId}
                                    className="rounded-xl border border-border/50 bg-background/80 p-3"
                                >
                                    <p className="text-sm font-semibold text-foreground">
                                        {item.bookTitle}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {item.internalCode} · {item.borrowedAt}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                ) : null}

                <div className="rounded-[1.75rem] border border-border/70 bg-card px-5 py-6 shadow-sm">
                    {returnDraft.qrCodeSvg && countdownLabel ? (
                        <div className="mb-5 text-center">
                            <p className="text-[11px] font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                Berlaku dalam
                            </p>
                            <p
                                className="mt-2 text-4xl font-semibold tracking-tight text-foreground tabular-nums"
                                title={
                                    returnDraft.expiresAt
                                        ? `Berlaku sampai ${returnDraft.expiresAt}`
                                        : undefined
                                }
                            >
                                {countdownLabel}
                            </p>
                        </div>
                    ) : null}

                    {returnDraft.qrCodeSvg ? (
                        <div className="space-y-4">
                            <div className="mx-auto w-max rounded-3xl border border-border/50 bg-white p-4 text-primary shadow-sm dark:bg-card dark:text-white">
                                <div
                                    className="flex justify-center [&_svg]:mx-auto"
                                    dangerouslySetInnerHTML={{
                                        __html: returnDraft.qrCodeSvg,
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
                                        returnDraft.qrCodeSvg!,
                                        `qr-pengembalian-${returnDraft.id ?? 'draft'}.png`,
                                        'QR PENGEMBALIAN',
                                    )
                                }
                            >
                                <Download className="size-4" />
                                Unduh QR
                            </Button>
                        </div>
                    ) : (
                        <div className="rounded-2xl bg-muted/20 px-5 py-12 text-center text-sm text-muted-foreground">
                            QR belum dibuat.
                        </div>
                    )}
                </div>

                <form
                    onSubmit={onSubmit}
                    className="space-y-3"
                >
                    <Button
                        type="submit"
                        size="lg"
                        className="w-full"
                        disabled={
                            isProcessing ||
                            activeLoanCount < 1 ||
                            selectedItemsCount === 0 ||
                            hasActiveQrCountdown
                        }
                    >
                        <QrCode className="size-4" />
                        Buat QR
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}
