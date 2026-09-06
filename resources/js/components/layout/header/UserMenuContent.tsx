import { Link, router, usePage } from '@inertiajs/react';
import {
    History,
    KeyRound,
    Laptop,
    LayoutDashboard,
    LogOut,
    Moon,
    Settings,
    Shield,
    Sun,
    UserCheck,
} from 'lucide-react';
import { UserInfo } from '@/components/common/UserInfo';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/hooks/use-appearance';
import { cleanupMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import loans from '@/routes/loans';
import settings from '@/routes/settings';
import type { Auth, User } from '@/types';

type Props = {
    user: User;
};

export function UserMenuContent({ user }: Props) {
    const pageProps = usePage<{ auth?: Auth }>().props;
    const auth = pageProps.auth;
    const { appearance, updateAppearance } = useAppearance();

    const handleLogout = () => {
        cleanupMobileNavigation();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-2 py-2 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                {auth?.requiresOnboarding && auth.onboardingUrl ? (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full cursor-pointer bg-primary/10 px-2 py-2 font-medium text-primary hover:bg-primary/20"
                            href={auth.onboardingUrl}
                            prefetch
                            onClick={cleanupMobileNavigation}
                        >
                            <UserCheck className="mr-2 h-4 w-4" />
                            Lengkapi Data Anggota
                        </Link>
                    </DropdownMenuItem>
                ) : null}
                {auth?.isMember ? (
                    <DropdownMenuItem asChild>
                        <a
                            className="block w-full cursor-pointer px-2 py-2"
                            href="/dashboard"
                            onClick={cleanupMobileNavigation}
                        >
                            <LayoutDashboard className="mr-2 h-4 w-4" />
                            Dashboard
                        </a>
                    </DropdownMenuItem>
                ) : null}
                {auth?.canAccessAdminPanel ? (
                    <DropdownMenuItem asChild>
                        <a
                            className="block w-full cursor-pointer px-2 py-2"
                            href="/admin"
                            onClick={cleanupMobileNavigation}
                        >
                            <Shield className="mr-2 h-4 w-4" />
                            Admin
                        </a>
                    </DropdownMenuItem>
                ) : null}
                {auth?.isMember ? (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full cursor-pointer px-2 py-2"
                            href={settings.memberKey.show.url()}
                            prefetch
                            onClick={cleanupMobileNavigation}
                        >
                            <KeyRound className="mr-2 h-4 w-4" />
                            Kartu Anggota (QR)
                        </Link>
                    </DropdownMenuItem>
                ) : null}
                {auth?.isMember ? (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full cursor-pointer px-2 py-2"
                            href={loans.history.url()}
                            prefetch
                            onClick={cleanupMobileNavigation}
                        >
                            <History className="mr-2 h-4 w-4" />
                            Riwayat Peminjaman
                        </Link>
                    </DropdownMenuItem>
                ) : null}
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full cursor-pointer px-2 py-2"
                        href={settings.profile.edit.url()}
                        prefetch
                        onClick={cleanupMobileNavigation}
                    >
                        <Settings className="mr-2" />
                        Pengaturan
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuLabel className="px-2 py-1.5">
                    Tema
                </DropdownMenuLabel>
                <DropdownMenuRadioGroup
                    value={appearance}
                    onValueChange={(value) =>
                        updateAppearance(value as 'light' | 'dark' | 'system')
                    }
                >
                    <DropdownMenuRadioItem value="light" className="px-2 py-2">
                        <Sun className="mr-2 h-4 w-4" />
                        Light
                    </DropdownMenuRadioItem>
                    <DropdownMenuRadioItem value="dark" className="px-2 py-2">
                        <Moon className="mr-2 h-4 w-4" />
                        Dark
                    </DropdownMenuRadioItem>
                    <DropdownMenuRadioItem value="system" className="px-2 py-2">
                        <Laptop className="mr-2 h-4 w-4" />
                        System
                    </DropdownMenuRadioItem>
                </DropdownMenuRadioGroup>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link
                    className="block w-full cursor-pointer px-2 py-2"
                    href={logout()}
                    as="button"
                    onClick={handleLogout}
                    data-test="logout-button"
                >
                    <LogOut className="mr-2" />
                    Keluar
                </Link>
            </DropdownMenuItem>
        </>
    );
}
