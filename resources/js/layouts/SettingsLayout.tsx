import { Link } from '@inertiajs/react';
import { ShieldCheck, User } from 'lucide-react';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import settings from '@/routes/settings';
import type { PropsWithChildren } from 'react';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profil',
        href: settings.profile.edit(),
        icon: User,
    },
    {
        title: 'Keamanan',
        href: settings.security.edit(),
        icon: ShieldCheck,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <div className="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
            {/* Page header */}
            <div className="mx-auto max-w-5xl">
                <div className="mb-8">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Pengaturan
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Kelola profil dan preferensi akun Anda
                    </p>
                </div>

                <div className="flex flex-col gap-8 lg:flex-row lg:gap-12">
                    {/* Sidebar */}
                    <aside className="w-full lg:w-52 lg:shrink-0">
                        <nav
                            className="flex flex-row gap-1 lg:flex-col"
                            aria-label="Navigasi pengaturan"
                        >
                            {sidebarNavItems.map((item, index) => {
                                const isActive = isCurrentOrParentUrl(
                                    item.href,
                                );

                                return (
                                    <Link
                                        key={`${toUrl(item.href)}-${index}`}
                                        href={item.href}
                                        className={cn(
                                            'group flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150',
                                            isActive
                                                ? 'bg-primary text-primary-foreground shadow-sm'
                                                : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                        )}
                                    >
                                        {item.icon && (
                                            <item.icon
                                                className={cn(
                                                    'h-4 w-4 shrink-0',
                                                    isActive
                                                        ? 'opacity-100'
                                                        : 'opacity-60 group-hover:opacity-100',
                                                )}
                                            />
                                        )}
                                        {item.title}
                                    </Link>
                                );
                            })}
                        </nav>
                    </aside>

                    <Separator className="lg:hidden" />

                    {/* Content */}
                    <div className="min-w-0 flex-1">
                        <div className="max-w-2xl space-y-10">{children}</div>
                    </div>
                </div>
            </div>
        </div>
    );
}
