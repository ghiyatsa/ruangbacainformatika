import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

export default function BookCardSkeleton() {
    return (
        <Card className="overflow-hidden">
            <CardContent className="p-0">
                <div className="relative aspect-3/4 overflow-hidden bg-muted">
                    <Skeleton className="h-full w-full rounded-none" />
                    <div className="absolute top-2 right-2 flex flex-col gap-1">
                        <Skeleton className="h-4 w-16" />
                        <Skeleton className="h-4 w-12" />
                    </div>
                </div>
                <div className="p-4">
                    <div className="mb-2 flex flex-wrap gap-1">
                        <Skeleton className="h-3 w-10" />
                        <Skeleton className="h-3 w-12" />
                    </div>
                    <Skeleton className="h-4 w-3/4 mb-1" />
                    <Skeleton className="h-3 w-1/2 mb-3" />
                    <div className="mt-3 flex items-center justify-between border-t pt-3">
                        <Skeleton className="h-3 w-12" />
                        <Skeleton className="h-3 w-8" />
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
