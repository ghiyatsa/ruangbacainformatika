import { ArrowRight, MoonIcon, SunIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/hooks/use-appearance';
import { register } from '@/routes';
import { AppLogo } from './AppLogo';

interface NavigationProps {
    canRegister: boolean;
}

export default function Navigation({ canRegister }: NavigationProps) {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <header className="sticky top-8 z-50 w-full">
            <div className="mx-auto flex h-16 max-w-7xl items-center justify-between rounded-2xl border border-accent/50 bg-background/10 px-4 backdrop-blur-md">
                <AppLogo />
                <nav className="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        aria-label="Toggle theme"
                        onClick={() =>
                            updateAppearance(
                                appearance === 'dark' ? 'light' : 'dark',
                            )
                        }
                    >
                        {appearance === 'dark' ? <SunIcon /> : <MoonIcon />}
                    </Button>
                    {canRegister && (
                        <Button asChild className="shadow-md shadow-primary/10">
                            <a href={register.url()}>
                                Bergabung
                                <ArrowRight
                                    data-icon="inline-end"
                                    className="ml-1"
                                />
                            </a>
                        </Button>
                    )}
                </nav>
            </div>
        </header>
    );
}
