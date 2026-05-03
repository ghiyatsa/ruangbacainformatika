import { usePage } from '@inertiajs/react';
import { MoonIcon, SunIcon, UserCircle } from 'lucide-react';
import { GlobalSearch } from '@/components/layouts/GlobalSearch';
import { UserMenuContent } from '@/components/layouts/UserMenuContent';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { AppLogo } from '@/components/welcome/AppLogo';
import { useAppearance } from '@/hooks/use-appearance';
import { login, register } from '@/routes';
import type { Auth } from '@/types';

export function AppHeader() {
    const { auth, canRegister = true } = usePage<{
        auth: Auth;
        canRegister?: boolean;
    }>().props;
    const { appearance, updateAppearance } = useAppearance();

    return (
        <header className="sticky top-3 z-50 w-full px-3 sm:top-6 sm:px-6">
            <div className="mx-auto flex h-14 max-w-7xl items-center justify-between rounded-2xl border border-accent/50 bg-background/50 px-3 backdrop-blur-md sm:h-16 sm:px-4 dark:bg-background/20">
                <AppLogo />

                <div className="hidden flex-1 justify-center px-6 md:flex">
                    <GlobalSearch />
                </div>

                <div className="flex items-center gap-2">
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-9 w-9 rounded-xl"
                        onClick={() =>
                            updateAppearance(
                                appearance === 'dark' ? 'light' : 'dark',
                            )
                        }
                    >
                        {appearance === 'dark' ? (
                            <SunIcon className="h-5 w-5" />
                        ) : (
                            <MoonIcon className="h-5 w-5" />
                        )}
                        <span className="sr-only">Toggle theme</span>
                    </Button>

                    {auth.user ? (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    className="relative h-9 w-9 rounded-full p-0"
                                >
                                    <UserCircle className="h-6 w-6" />
                                    <span className="sr-only">User menu</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                className="w-56"
                                align="end"
                                forceMount
                            >
                                <UserMenuContent user={auth.user} />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    ) : (
                        <div className="flex items-center gap-2">
                            <Button
                                variant="ghost"
                                asChild
                                className="hidden sm:inline-flex"
                            >
                                <a href={login.url()}>Masuk</a>
                            </Button>
                            {canRegister && (
                                <Button
                                    asChild
                                    className="rounded-xl shadow-md shadow-primary/10"
                                >
                                    <a href={register.url()}>Bergabung</a>
                                </Button>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}
