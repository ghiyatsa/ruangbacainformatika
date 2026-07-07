import { useForm } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import MemberKeyController from '@/actions/App/Http/Controllers/Settings/MemberKeyController';
import { Skeleton } from '@/components/ui/skeleton';
import { useCountdown } from '@/hooks/use-countdown';
import { formatCountdown } from '@/lib/format-countdown';

interface Props {
    memberKey: {
        hasActiveQr: boolean;
        expiresAt: string | null;
        expiresAtIso: string | null;
        qrCodeSvg: string | null;
    };
}

export function MemberKeySection({ memberKey }: Props) {
    const form = useForm({
        automatic: true,
    });
    const autoRegenerateTriggered = useRef(false);
    const { remainingSeconds } = useCountdown(memberKey.expiresAtIso);
    const countdownLabel = formatCountdown(remainingSeconds ?? 0);

    useEffect(() => {
        if (remainingSeconds === null || remainingSeconds > 0) {
            autoRegenerateTriggered.current = false;

            return;
        }

        if (
            form.processing ||
            autoRegenerateTriggered.current
        ) {
            return;
        }

        autoRegenerateTriggered.current = true;

        form.post(MemberKeyController.generate.url(), {
            preserveScroll: true,
        });
    }, [form.processing, form, remainingSeconds]);

    const isProcessing = form.processing;

    return (
        <section className="rounded-xl border border-border/70 bg-card p-6 shadow-xs">
            <div className="flex flex-col items-center justify-center space-y-4">
                <div className="flex flex-col items-center justify-center gap-1.5 text-center">
                    <p className="text-sm font-medium text-muted-foreground">
                        Masa Berlaku
                    </p>
                    <p className="text-3xl font-bold tracking-tight text-foreground tabular-nums">
                        {countdownLabel}
                    </p>
                </div>

                {memberKey.qrCodeSvg && !isProcessing ? (
                    <div className="group relative">
                        <div className="mx-auto w-max rounded-xl border border-border/60 bg-white p-4 text-primary shadow-xs dark:bg-zinc-950 dark:text-white">
                            <div
                                className="flex justify-center [&_svg]:mx-auto [&_svg]:size-52 md:[&_svg]:size-72"
                                dangerouslySetInnerHTML={{
                                    __html: memberKey.qrCodeSvg,
                                }}
                            />
                        </div>
                    </div>
                ) : (
                    <div className="mx-auto w-max rounded-xl border border-border/60 bg-white p-4 dark:bg-zinc-950">
                        <Skeleton className="size-52 animate-pulse rounded-lg md:size-72" />
                    </div>
                )}
            </div>
        </section>
    );
}
