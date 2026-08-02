import { Link, usePage } from '@inertiajs/react';
import { ShieldAlert } from 'lucide-react';
import type { Auth } from '@/types/auth';

export function BorrowAccessBanner() {
    const { auth } = usePage<{ auth: Auth }>().props;

    if (!auth.user || !auth.borrowingAccess || auth.borrowingAccess.canBorrow) {
        return null;
    }

    const reason = auth.borrowingAccess.reason;

    if (!reason) {
        return null;
    }

    return (
        <div className="border-b border-amber-500/30 bg-amber-500/10">
            <div className="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-2 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-6 lg:px-8">
                <div className="flex min-w-0 items-start gap-2">
                    <ShieldAlert className="mt-0.5 size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                    <p className="text-amber-800 dark:text-amber-200">
                        <span className="font-semibold">{reason.title}: </span>
                        {reason.message}
                    </p>
                </div>
                {reason.actionUrl ? (
                    <Link
                        href={reason.actionUrl}
                        className="shrink-0 text-sm font-semibold text-amber-700 underline underline-offset-4 hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-100"
                    >
                        Selesaikan sekarang
                    </Link>
                ) : null}
            </div>
        </div>
    );
}
