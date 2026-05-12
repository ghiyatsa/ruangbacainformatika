import { Link } from '@inertiajs/react';
import {
    BookMarked,
    Calendar,
    ChevronRight,
    Hash,
    Tag,
    User,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from '@/components/ui/card';
import type { SkripsiData } from '@/features/skripsi/types';
import skripsiRoute from '@/routes/skripsi';

interface SkripsiCardProps {
    skripsi: SkripsiData;
}

export default function SkripsiCard({ skripsi }: SkripsiCardProps) {
    return (
        <Link
            href={skripsiRoute.show.url(skripsi.studentId)}
            className="group block h-full focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
        >
            <Card className="relative flex h-full flex-col overflow-hidden transition-all duration-300 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 dark:hover:shadow-primary/10">
                <CardHeader className="gap-3">
                    {/* Badge + Year row */}
                    <div className="flex items-center justify-between">
                        <Badge
                            variant="secondary"
                            className="gap-1 bg-primary/10 text-xs text-primary hover:bg-primary/15"
                        >
                            <BookMarked className="size-2.5" />
                            Skripsi
                        </Badge>
                        {skripsi.year && (
                            <span className="flex items-center gap-1 text-[11px] text-muted-foreground">
                                <Calendar className="size-3" />
                                {skripsi.year}
                            </span>
                        )}
                    </div>

                    {/* Title */}
                    <h3 className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">
                        {skripsi.title}
                    </h3>
                </CardHeader>

                <CardContent className="flex flex-1 flex-col gap-3">
                    {/* Keywords */}
                    {skripsi.keywords.length > 0 && (
                        <div className="flex flex-wrap gap-1">
                            {skripsi.keywords.slice(0, 3).map((kw, i) => (
                                <span
                                    key={i}
                                    className="inline-flex items-center gap-0.5 rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                                    data-skeleton-ignore
                                >
                                    <Tag className="size-2.5" />
                                    {kw}
                                </span>
                            ))}
                            {skripsi.keywords.length > 3 && (
                                <span className="rounded-md bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                    +{skripsi.keywords.length - 3}
                                </span>
                            )}
                        </div>
                    )}
                </CardContent>

                <CardFooter className="mt-auto flex items-center justify-between border-t">
                    <div className="flex flex-col gap-0.5">
                        <span className="flex items-center gap-1 text-xs font-semibold text-foreground">
                            <User className="size-3 text-muted-foreground" />
                            {skripsi.authorName}
                        </span>
                        <span className="flex items-center gap-1 text-[10px] text-muted-foreground">
                            <Hash className="size-2.5" />
                            NIM: {skripsi.studentId}
                        </span>
                    </div>
                    <ChevronRight className="size-4 text-muted-foreground/50 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-primary" />
                </CardFooter>
            </Card>
        </Link>
    );
}
