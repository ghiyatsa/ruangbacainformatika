import { Link } from '@inertiajs/react';
import { Terminal } from 'lucide-react';
import { home } from '@/routes';

export const AppLogo = () => {
    return (
        <Link href={home.url()} className="flex items-center gap-3">
            <div className="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-lg shadow-primary/20">
                <Terminal className="size-5" />
            </div>
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
