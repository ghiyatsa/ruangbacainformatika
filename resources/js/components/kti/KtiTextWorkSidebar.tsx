import {
    Calendar,
    Hash,
    Tag,
    User,
} from 'lucide-react';
import { KtiDetailItem } from '@/components/kti/KtiDetailItem';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';

export interface TextWorkSidebarRecord {
    authorName: string;
    studentId: string;
    year?: number | null;
    keywords: string[];
}

interface KtiTextWorkSidebarProps {
    record: TextWorkSidebarRecord | null;
    label: string;
}

export function KtiTextWorkSidebar({
    record,
    label,
}: KtiTextWorkSidebarProps) {
    return (
        <div className="space-y-4">
            <div className="rounded-2xl border border-border/60 bg-card">
                <div className="p-5">
                    <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                        Informasi {label}
                    </h2>
                </div>
                <Separator />
                <div className="p-2">
                    {record ? (
                        <>
                            <KtiDetailItem
                                icon={<User className="size-4" />}
                                label="Penulis"
                                value={record.authorName}
                            />
                            <KtiDetailItem
                                icon={<Hash className="size-4" />}
                                label="NIM"
                                value={record.studentId}
                            />
                            {record.year ? (
                                <KtiDetailItem
                                    icon={<Calendar className="size-4" />}
                                    label="Tahun"
                                    value={String(record.year)}
                                />
                            ) : null}
                        </>
                    ) : (
                        <>
                            <KtiDetailItem
                                icon={<User className="size-4" />}
                                label="Penulis"
                                value={
                                    <Skeleton className="h-5 w-32 animate-pulse" />
                                }
                            />
                            <KtiDetailItem
                                icon={<Hash className="size-4" />}
                                label="NIM"
                                value={
                                    <Skeleton className="h-5 w-24 animate-pulse" />
                                }
                            />
                            <KtiDetailItem
                                icon={<Calendar className="size-4" />}
                                label="Tahun"
                                value={
                                    <Skeleton className="h-5 w-16 animate-pulse" />
                                }
                            />
                        </>
                    )}
                </div>
            </div>

            {record ? (
                record.keywords.length > 0 ? (
                    <div className="rounded-2xl border border-border/60 bg-card">
                        <div className="p-5">
                            <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                Kata Kunci
                            </h2>
                        </div>
                        <Separator />
                        <div className="flex flex-wrap gap-2 p-4">
                            {record.keywords.map((keyword) => (
                                <Badge
                                    key={keyword}
                                    variant="secondary"
                                    className="gap-1 bg-muted/80"
                                >
                                    <Tag className="size-2.5" />
                                    {keyword}
                                </Badge>
                            ))}
                        </div>
                    </div>
                ) : null
            ) : (
                <div className="rounded-2xl border border-border/60 bg-card">
                    <div className="p-5">
                        <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                            Kata Kunci
                        </h2>
                    </div>
                    <Separator />
                    <div className="flex flex-wrap gap-2 p-4">
                        <Skeleton className="h-6 w-16 rounded-full animate-pulse" />
                        <Skeleton className="h-6 w-20 rounded-full animate-pulse" />
                        <Skeleton className="h-6 w-14 rounded-full animate-pulse" />
                        <Skeleton className="h-6 w-18 rounded-full animate-pulse" />
                    </div>
                </div>
            )}
        </div>
    );
}
