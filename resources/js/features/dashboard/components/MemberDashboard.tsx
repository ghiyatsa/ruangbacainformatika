import { Link } from '@inertiajs/react';
import {
    CheckCircle2,
    CircleDashed,
    Clock3,
    FileText,
    XCircle,
} from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

export interface MemberDashboardCounts {
    total: number;
    pending: number;
    revision: number;
    approved: number;
    rejected: number;
}

export interface MemberSubmissionItem {
    id: number;
    title: string;
    type: string;
    type_label: string;
    status: string;
    status_label: string;
    status_color: string;
    revision_notes: string | null;
    created_at: string | null;
    reviewed_at: string | null;
}

interface MemberDashboardProps {
    counts: MemberDashboardCounts;
    submissions: MemberSubmissionItem[];
}

const STATUS_ICONS = {
    pending: Clock3,
    revision: CircleDashed,
    approved: CheckCircle2,
    rejected: XCircle,
} as const;

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

export function MemberDashboard({
    counts,
    submissions,
}: MemberDashboardProps) {
    const cards = [
        {
            key: 'pending',
            label: 'Sedang Ditinjau',
            value: counts.pending,
            icon: Clock3,
            tone: 'text-amber-600 dark:text-amber-400',
        },
        {
            key: 'revision',
            label: 'Perlu Revisi',
            value: counts.revision,
            icon: CircleDashed,
            tone: 'text-blue-600 dark:text-blue-400',
        },
        {
            key: 'approved',
            label: 'Diterima',
            value: counts.approved,
            icon: CheckCircle2,
            tone: 'text-green-600 dark:text-green-400',
        },
        {
            key: 'rejected',
            label: 'Ditolak',
            value: counts.rejected,
            icon: XCircle,
            tone: 'text-destructive',
        },
    ];

    return (
        <div className="mx-auto w-full max-w-5xl space-y-6 px-4 py-8">
            <header className="space-y-2">
                <h1 className="text-2xl font-semibold tracking-tight">
                    Status Pengajuan Saya
                </h1>
                <p className="text-sm text-muted-foreground">
                    Pantau berkas yang Anda kirimkan ke perpustakaan.
                </p>
            </header>

            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {cards.map((card) => {
                    const Icon = card.icon;

                    return (
                        <Card key={card.key} className="rounded-2xl">
                            <CardHeader className="flex flex-row items-center justify-between gap-2 pb-2">
                                <CardDescription>{card.label}</CardDescription>
                                <Icon className={`size-5 ${card.tone}`} />
                            </CardHeader>
                            <CardContent>
                                <p className="text-3xl font-semibold">
                                    {card.value}
                                </p>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>

            <Card className="rounded-2xl">
                <CardHeader>
                    <CardTitle>Pengajuan Terbaru</CardTitle>
                    <CardDescription>
                        {counts.total > 0
                            ? `Total ${counts.total} pengajuan`
                            : 'Belum ada pengajuan'}
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                    {submissions.length === 0 ? (
                        <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed border-border/60 px-6 py-10 text-center">
                            <FileText className="size-8 text-muted-foreground" />
                            <div className="space-y-1">
                                <p className="text-sm font-medium">
                                    Belum ada pengajuan
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Berkas yang Anda kirimkan akan muncul di
                                    sini beserta statusnya.
                                </p>
                            </div>
                        </div>
                    ) : (
                        submissions.map((item) => {
                            const Icon =
                                STATUS_ICONS[
                                    item.status as keyof typeof STATUS_ICONS
                                ] ?? FileText;

                            return (
                                <div
                                    key={item.id}
                                    className="flex flex-col gap-3 rounded-xl border border-border/60 p-4 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div className="min-w-0 space-y-1">
                                        <p className="font-medium">
                                            {item.title}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {item.type_label} &middot; dikirim{' '}
                                            {formatDate(item.created_at)}
                                        </p>

                                        {item.revision_notes ? (
                                            <p className="rounded-lg border border-blue-500/30 bg-blue-500/5 p-2 text-sm text-blue-700 dark:text-blue-300">
                                                Catatan peninjau:{' '}
                                                {item.revision_notes}
                                            </p>
                                        ) : null}
                                    </div>

                                    <Badge
                                        variant="outline"
                                        className="w-fit shrink-0 gap-1.5 rounded-lg"
                                    >
                                        <Icon className="size-3.5" />
                                        {item.status_label}
                                    </Badge>
                                </div>
                            );
                        })
                    )}
                </CardContent>
            </Card>

            <p className="text-sm text-muted-foreground">
                Ingin meminjam buku?{' '}
                <Link
                    href="/books"
                    className="font-medium text-foreground underline underline-offset-4"
                >
                    Telusuri katalog
                </Link>
                .
            </p>
        </div>
    );
}
