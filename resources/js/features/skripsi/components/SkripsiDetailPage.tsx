import { Head, Link } from '@inertiajs/react';
import {
    BookMarked,
    Calendar,
    GraduationCap,
    Hash,
    Tag,
    User,
} from 'lucide-react';
import React from 'react';
import { Badge } from '@/components/ui/badge';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import type { SkripsiShowProps } from '@/features/skripsi/types';

interface DetailItemProps {
    icon: React.ReactNode;
    label: string;
    value: string;
}

function DetailItem({ icon, label, value }: DetailItemProps) {
    return (
        <div className="group flex items-start gap-3 rounded-xl p-3 transition-colors hover:bg-muted/50">
            <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                {icon}
            </div>
            <div className="min-w-0">
                <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    {label}
                </p>
                <p className="mt-0.5 truncate text-sm font-semibold text-foreground">
                    {value}
                </p>
            </div>
        </div>
    );
}

export default function SkripsiDetailPage({
    skripsi: { data: skripsi },
}: SkripsiShowProps) {
    return (
        <>
            <Head title={skripsi.studentId} />

            {/* Dot-grid background */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10 flex flex-col">
                {/* Hero Banner */}
                <div className="relative -mt-20 overflow-hidden border-b bg-linear-to-br from-primary/5 via-background to-muted/30 sm:-mt-28">
                    <div className="absolute inset-0 bg-linear-to-b from-background/0 via-background/40 to-background" />

                    <div className="relative mx-auto max-w-7xl px-6 pt-32 pb-12 sm:pt-40 lg:px-8">
                        {/* Breadcrumb */}
                        <Breadcrumb className="mb-8">
                            <BreadcrumbList>
                                <BreadcrumbItem>
                                    <BreadcrumbLink asChild>
                                        <Link href="/">Beranda</Link>
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator />
                                <BreadcrumbItem>
                                    <BreadcrumbLink asChild>
                                        <Link href="/skripsi">Skripsi</Link>
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator />
                                <BreadcrumbItem>
                                    <BreadcrumbPage className="max-w-xs truncate">
                                        {skripsi.studentId}
                                    </BreadcrumbPage>
                                </BreadcrumbItem>
                            </BreadcrumbList>
                        </Breadcrumb>

                        {/* Icon + Title area */}
                        <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                            {/* Icon badge */}
                            <div className="flex size-24 shrink-0 items-center justify-center rounded-3xl border bg-linear-to-br from-primary/20 to-primary/5 shadow-lg shadow-primary/10">
                                <GraduationCap className="size-12 text-primary" />
                            </div>

                            <div className="flex flex-col justify-center">
                                <Badge
                                    variant="secondary"
                                    className="mb-3 w-fit gap-1.5 bg-primary/10 text-primary hover:bg-primary/15"
                                >
                                    <BookMarked className="size-3" />
                                    Skripsi
                                </Badge>

                                <h1 className="mb-3 text-2xl leading-tight font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                    {skripsi.title}
                                </h1>

                                <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                    <span className="flex items-center gap-1.5">
                                        <User className="size-3.5" />
                                        {skripsi.authorName}
                                    </span>
                                    <span className="text-border">•</span>
                                    <span className="flex items-center gap-1.5">
                                        <Hash className="size-3.5" />
                                        NIM: {skripsi.studentId}
                                    </span>
                                    {skripsi.year && (
                                        <>
                                            <span className="text-border">
                                                •
                                            </span>
                                            <span className="flex items-center gap-1.5">
                                                <Calendar className="size-3.5" />
                                                {skripsi.year}
                                            </span>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Main Content */}
                <div className="py-10">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <div className="grid gap-8 md:grid-cols-12 md:gap-10">
                            {/* Sidebar */}
                            <aside className="md:col-span-4 lg:col-span-3">
                                <div className="rounded-2xl border bg-card/80 shadow-sm backdrop-blur-sm">
                                    <div className="p-5">
                                        <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                            Informasi Skripsi
                                        </h2>
                                    </div>
                                    <Separator />
                                    <div className="p-2">
                                        <DetailItem
                                            icon={<User className="size-4" />}
                                            label="Penulis"
                                            value={skripsi.authorName}
                                        />
                                        <DetailItem
                                            icon={<Hash className="size-4" />}
                                            label="NIM"
                                            value={skripsi.studentId}
                                        />
                                        {skripsi.year && (
                                            <DetailItem
                                                icon={
                                                    <Calendar className="size-4" />
                                                }
                                                label="Tahun"
                                                value={String(skripsi.year)}
                                            />
                                        )}
                                    </div>
                                </div>

                                {/* Keywords */}
                                {skripsi.keywords.length > 0 && (
                                    <div className="mt-4 rounded-2xl border bg-card/80 shadow-sm backdrop-blur-sm">
                                        <div className="p-5">
                                            <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                                Kata Kunci
                                            </h2>
                                        </div>
                                        <Separator />
                                        <div className="flex flex-wrap gap-2 p-4">
                                            {skripsi.keywords.map(
                                                (kw, index) => (
                                                    <Badge
                                                        key={index}
                                                        variant="secondary"
                                                        className="gap-1 bg-muted/80"
                                                    >
                                                        <Tag className="size-2.5" />
                                                        {kw}
                                                    </Badge>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}
                            </aside>

                            {/* Abstract */}
                            <div className="md:col-span-8 lg:col-span-9">
                                <section>
                                    <div className="mb-5 flex items-center gap-3">
                                        <div className="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                            <BookMarked className="size-4" />
                                        </div>
                                        <h2 className="text-xl font-bold">
                                            Abstrak
                                        </h2>
                                    </div>

                                    {skripsi.abstract ? (
                                        <div className="space-y-4 text-justify text-base leading-[1.85] text-muted-foreground">
                                            {skripsi.abstract
                                                .split('\n')
                                                .filter(Boolean)
                                                .map((paragraph, i) => (
                                                    <p key={i}>{paragraph}</p>
                                                ))}
                                        </div>
                                    ) : (
                                        <div className="rounded-2xl border border-dashed bg-muted/30 p-10 text-center">
                                            <BookMarked className="mx-auto mb-3 size-10 text-muted-foreground/40" />
                                            <p className="text-sm text-muted-foreground">
                                                Abstrak belum tersedia untuk
                                                skripsi ini.
                                            </p>
                                        </div>
                                    )}
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
