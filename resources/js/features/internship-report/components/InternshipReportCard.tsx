import { Link } from '@inertiajs/react';
import {
    Calendar,
    ChevronRight,
    ClipboardCheck,
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
import type { InternshipReportData } from '@/features/internship-report/types';
import internshipReportRoute from '@/routes/internship-reports';

interface InternshipReportCardProps {
    report: InternshipReportData;
}

export default function InternshipReportCard({
    report,
}: InternshipReportCardProps) {
    return (
        <Link
            href={internshipReportRoute.show.url(report.studentId)}
            className="group block focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
        >
            <Card className="relative flex h-full flex-col overflow-hidden transition-all duration-300 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 dark:hover:shadow-primary/10">
                <CardHeader className="gap-3">
                    <div className="flex items-center justify-between">
                        <Badge
                            variant="secondary"
                            className="gap-1 bg-primary/10 text-xs text-primary hover:bg-primary/15"
                        >
                            <ClipboardCheck className="size-2.5" />
                            Laporan KP
                        </Badge>
                        {report.year && (
                            <span className="flex items-center gap-1 text-[11px] text-muted-foreground">
                                <Calendar className="size-3" />
                                {report.year}
                            </span>
                        )}
                    </div>

                    <div className="min-h-[3.75rem]">
                        <h3 className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">
                            {report.title}
                        </h3>
                    </div>
                </CardHeader>

                <CardContent className="flex flex-1 flex-col gap-3">
                    <div className="min-h-[1.5rem]">
                        {report.keywords.length > 0 && (
                            <div className="flex flex-wrap gap-1">
                                {report.keywords.slice(0, 3).map((kw, i) => (
                                    <span
                                        key={i}
                                        className="inline-flex items-center gap-0.5 rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                                    >
                                        <Tag className="size-2.5" />
                                        {kw}
                                    </span>
                                ))}
                                {report.keywords.length > 3 && (
                                    <span className="rounded-md bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                        +{report.keywords.length - 3}
                                    </span>
                                )}
                            </div>
                        )}
                    </div>
                </CardContent>

                <CardFooter className="mt-auto flex min-h-[4.5rem] items-center justify-between border-t">
                    <div className="flex flex-col gap-0.5">
                        <span
                            className="flex items-center gap-1 text-xs font-semibold text-foreground"
                            title={report.authorName}
                        >
                            <User className="size-3 text-muted-foreground" />
                            <span className="line-clamp-1">
                                {report.authorName}
                            </span>
                        </span>
                        <span className="flex items-center gap-1 text-[10px] text-muted-foreground">
                            <Hash className="size-2.5" />
                            NIM: {report.studentId}
                        </span>
                    </div>
                    <ChevronRight className="size-4 text-muted-foreground/50 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-primary" />
                </CardFooter>
            </Card>
        </Link>
    );
}
