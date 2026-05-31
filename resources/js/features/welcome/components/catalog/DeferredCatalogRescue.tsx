import { router } from '@inertiajs/react';
import { AlertCircle, RefreshCw } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface DeferredCatalogRescueProps {
    dataKey: string;
    title: string;
    description: string;
    reloading: boolean;
}

export default function DeferredCatalogRescue({
    dataKey,
    title,
    description,
    reloading,
}: DeferredCatalogRescueProps) {
    return (
        <div className="flex min-h-56 flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-border/70 bg-muted/20 px-4 py-8 text-center sm:px-6">
            <div className="flex size-11 items-center justify-center rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400">
                <AlertCircle className="size-5" />
            </div>
            <div className="space-y-1">
                <h3 className="text-base font-semibold text-foreground">
                    {title}
                </h3>
                <p className="max-w-md text-sm leading-relaxed text-muted-foreground">
                    {description}
                </p>
            </div>
            <Button
                type="button"
                variant="outline"
                className="rounded-xl"
                disabled={reloading}
                onClick={() => router.reload({ only: [dataKey] })}
            >
                <RefreshCw
                    className={reloading ? 'animate-spin' : undefined}
                />
                {reloading ? 'Memuat ulang...' : 'Coba lagi'}
            </Button>
        </div>
    );
}
