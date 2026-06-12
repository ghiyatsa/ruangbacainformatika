import { Head, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import MemberKeyController from '@/actions/App/Http/Controllers/Settings/MemberKeyController';
import { Card, CardContent, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import settings from '@/routes/settings';

interface Props {
    memberKey: {
        hasActiveQr: boolean;
        expiresAt: string | null;
        expiresAtIso: string | null;
        qrCodeSvg: string | null;
    };
}

function formatCountdown(totalSeconds: number): string {
    if (totalSeconds <= 0) {
        return '00:00';
    }

    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return [minutes, seconds]
        .map((value) => String(value).padStart(2, '0'))
        .join(':');
}

export default function MemberKeyPage({ memberKey }: Props) {
    const form = useForm({
        automatic: true,
    });
    const [currentTimestamp, setCurrentTimestamp] = useState(() => Date.now());
    const autoRegenerateTriggered = useRef(false);
    const expiresAtTimestamp = memberKey.expiresAtIso
        ? new Date(memberKey.expiresAtIso).getTime()
        : null;
    const remainingSeconds =
        expiresAtTimestamp === null
            ? null
            : Math.max(
                  Math.ceil((expiresAtTimestamp - currentTimestamp) / 1000),
                  0,
              );
    const countdownLabel = formatCountdown(remainingSeconds ?? 0);

    useEffect(() => {
        if (expiresAtTimestamp === null) {
            return;
        }

        const interval = window.setInterval(() => {
            setCurrentTimestamp(Date.now());
        }, 1000);

        return () => window.clearInterval(interval);
    }, [expiresAtTimestamp]);

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

            <Card className="border-border/70 shadow-xs">
                <CardContent className="flex flex-col items-center justify-center space-y-5 p-6 text-center">
                    <div className="flex flex-col items-center justify-center gap-3">
                        <CardTitle>Member Key</CardTitle>
                        <p className="text-4xl font-semibold tracking-tight text-foreground tabular-nums">
                            {countdownLabel}
                        </p>
                    </div>
                    {memberKey.qrCodeSvg && !form.processing ? (
                        <div className="space-y-4">
                            <div className="mx-auto w-max bg-white p-4 text-primary shadow-sm dark:bg-card dark:text-white">
                                <div
                                    className="flex justify-center [&_svg]:mx-auto [&_svg]:size-72"
                                    dangerouslySetInnerHTML={{
                                        __html: memberKey.qrCodeSvg,
                                    }}
                                />
                            </div>
                        </div>
                    ) : (
                        <div className="mx-auto w-max bg-white p-4 dark:bg-card">
                            <Skeleton className="size-72 rounded-2xl" />
                        </div>
                    )}
                </CardContent>
            </Card>
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
