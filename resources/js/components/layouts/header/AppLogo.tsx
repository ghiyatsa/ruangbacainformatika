import { Link } from '@inertiajs/react';
import { RuangBacaLogo } from '@/components/common/RuangBacaLogo';
import { cn } from '@/lib/utils';
import { home } from '@/routes';

export function AppLogo({ compact = false }: { compact?: boolean }) {
    return (
        <Link href={home.url()} className="flex items-center gap-3">
            <RuangBacaLogo className="size-10" />
            <div className="flex flex-col">
                <span className="text-sm font-bold tracking-wider uppercase">
                    Ruang Baca
                </span>
                <span
                    className={cn(
                        'text-[10px] font-medium text-muted-foreground',
                        compact ? 'hidden xl:block' : 'hidden sm:block',
                    )}
                >
                    Teknik Informatika UNIMAL
                </span>
            </div>
        </Link>
    );
}
