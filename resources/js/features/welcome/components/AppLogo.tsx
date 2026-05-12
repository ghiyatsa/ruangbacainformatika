import { Link } from '@inertiajs/react';
import { RuangBacaLogo } from '@/components/common/RuangBacaLogo';
import { home } from '@/routes';

export const AppLogo = () => {
    return (
        <Link href={home.url()} className="flex items-center gap-3">
            <RuangBacaLogo className="size-9" />
            <div className="flex flex-col">
                <span className="text-sm font-bold tracking-wider uppercase">
                    Ruang Baca
                </span>
                <span className="hidden text-[10px] font-medium text-muted-foreground sm:block">
                    Teknik Informatika UNIMAL
                </span>
            </div>
        </Link>
    );
};
