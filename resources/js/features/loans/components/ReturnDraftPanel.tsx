import { Download, QrCode } from 'lucide-react';
import InputError from '@/components/common/InputError';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
        <div className="border border-border/60 bg-card p-6 shadow-none xl:sticky xl:top-24 xl:h-fit">
            <div className="mb-4">
                <h3 className="text-base font-bold text-foreground">QR Pengembalian</h3>
            </div>
            <div className="space-y-5">
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
                    <div className="border border-border/60 bg-muted/5 p-4">
                        <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                            Di QR
                        </p>
                        <div className="mt-3 space-y-3">
                            {returnDraft.items.map((item) => (
                                <div
                                    key={item.loanItemId}
                                    className="border border-border/60 bg-background p-3"
                                >
                                    <p className="text-sm font-semibold text-foreground">
                                        {item.bookTitle}
                                    </p>
                                    <p className="text-xs text-muted-foreground mt-0.5">
                                        {item.internalCode} &middot; {item.borrowedAt}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                ) : null}

                <div className="border border-border/60 bg-muted/5 px-5 py-6 shadow-none">
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
                            <div className="mx-auto w-max border border-border/60 bg-white p-4 text-primary shadow-none dark:bg-zinc-900 dark:text-white">
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
                        <div className="bg-muted/10 border border-dashed border-border/60 px-5 py-12 text-center text-sm text-muted-foreground">
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
            </div>
        </div>
    );
}
