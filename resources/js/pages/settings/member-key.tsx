import { Head, useForm } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import MemberKeyController from '@/actions/App/Http/Controllers/Settings/MemberKeyController';
import { Skeleton } from '@/components/ui/skeleton';
import { useCountdown } from '@/hooks/use-countdown';
import { formatCountdown } from '@/lib/format-countdown';
import settings from '@/routes/settings';

interface Props {
    memberKey: {
        hasActiveQr: boolean;
        expiresAt: string | null;
        expiresAtIso: string | null;
        qrCodeSvg: string | null;
    };
}

export default function MemberKeyPage({ memberKey }: Props) {
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

        if (form.processing || autoRegenerateTriggered.current) {
            return;
        }

        autoRegenerateTriggered.current = true;

        form.post(MemberKeyController.generate.url(), {
            preserveScroll: true,
        });
    }, [form.processing, form, remainingSeconds]);

    return (
        <>
            <Head title="Member Key" />

            <div className="border border-border/60 bg-card p-6 shadow-none">
                <div className="flex flex-col items-center justify-center space-y-5 text-center">
                    <div className="flex flex-col items-center justify-center gap-3">
                        <h2 className="text-xl font-bold">Member Key</h2>
                        <p className="text-4xl font-semibold tracking-tight text-foreground tabular-nums">
                            {countdownLabel}
                        </p>
                    </div>
                    {memberKey.qrCodeSvg && !form.processing ? (
                        <div className="space-y-4">
                            <div className="mx-auto w-max border border-border/60 bg-white p-4 text-primary shadow-none dark:bg-zinc-900 dark:text-white">
                                <div
                                    className="flex justify-center [&_svg]:mx-auto [&_svg]:size-72"
                                    dangerouslySetInnerHTML={{
                                        __html: memberKey.qrCodeSvg,
                                    }}
                                />
                            </div>
                        </div>
                    ) : (
                        <div className="mx-auto w-max border border-border/60 bg-white p-4 dark:bg-zinc-900">
                            <Skeleton className="size-72 rounded-none" />
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

MemberKeyPage.layout = {
    breadcrumbs: [
        {
            title: 'Member Key',
            href: settings.memberKey.show(),
        },
    ],
};
