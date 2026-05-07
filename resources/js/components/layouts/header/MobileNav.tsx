import { Menu, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { Auth } from '@/types';

interface MobileNavProps {
    mobileOpen: boolean;
    setMobileOpen: (open: boolean | ((prev: boolean) => boolean)) => void;
    isActive: (href: string) => boolean;
    auth: Auth;
    canRegister?: boolean;
}

export function MobileNav({
    mobileOpen,
    setMobileOpen,
}: Pick<MobileNavProps, 'mobileOpen' | 'setMobileOpen'>) {
    return (
        <Button
            variant="ghost"
            size="icon"
            className="relative h-9 w-9 rounded-xl md:hidden"
            onClick={() => setMobileOpen((v) => !v)}
            aria-label={mobileOpen ? 'Tutup menu' : 'Buka menu'}
            aria-expanded={mobileOpen}
        >
            <Menu
                className={[
                    'absolute h-5 w-5 transition-all duration-200',
                    mobileOpen
                        ? 'scale-0 rotate-90 opacity-0'
                        : 'scale-100 rotate-0 opacity-100',
                ].join(' ')}
            />
            <X
                className={[
                    'absolute h-5 w-5 transition-all duration-200',
                    mobileOpen
                        ? 'scale-100 rotate-0 opacity-100'
                        : 'scale-0 -rotate-90 opacity-0',
                ].join(' ')}
            />
        </Button>
    );
}
