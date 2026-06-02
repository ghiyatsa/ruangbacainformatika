import { Link, router, usePage } from '@inertiajs/react';
import { History, Laptop, LogOut, Moon, Settings, Sun } from 'lucide-react';
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
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import loans from '@/routes/loans';
import settings from '@/routes/settings';
import type { Auth, User } from '@/types';

type Props = {
    user: User;
};

export function UserMenuContent({ user }: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { appearance, updateAppearance } = useAppearance();
    const cleanup = useMobileNavigation();

    const handleLogout = () => {
        cleanup();
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
                {auth.canBorrowBooks ? (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full cursor-pointer px-2 py-2"
                            href={loans.history.url()}
                            prefetch
                            onClick={cleanup}
                        >
                            <History className="mr-2 h-4 w-4" />
                            Riwayat Peminjaman
                        </Link>
                    </DropdownMenuItem>
                ) : null}
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full cursor-pointer px-2 py-2"
                        href={settings.profile.edit()}
                        prefetch
                        onClick={cleanup}
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
