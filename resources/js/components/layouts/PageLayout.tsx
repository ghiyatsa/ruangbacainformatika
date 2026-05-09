import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { BackgroundPattern } from '@/components/layouts/BackgroundPattern';
import { cn } from '@/lib/utils';

interface PageLayoutProps {
    /**
     * The title of the page (used for <Head /> and default hero)
     */
    title: string;
    /**
     * A brief description shown in the default hero section
     */
    description?: string;
    /**
     * The main content of the page
     */
    children: ReactNode;
    /**
     * Maximum width of the content container
     * @default '5xl'
     */
    maxWidth?:
        | 'sm'
        | 'md'
        | 'lg'
        | 'xl'
        | '2xl'
        | '3xl'
        | '4xl'
        | '5xl'
        | '6xl'
        | '7xl'
        | 'full';
    /**
     * Additional classes for the main content container
     */
    className?: string;
    /**
     * Custom header/hero section. If provided, the default hero will be hidden.
     */
    header?: ReactNode;
    /**
     * Whether to show the dot-grid background pattern
     * @default true
     */
    showBackground?: boolean;
    /**
     * Whether to show the default hero section (only if header is not provided)
     * @default true
     */
    showHero?: boolean;
}

const maxWidthMap = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '3xl': 'max-w-3xl',
    '4xl': 'max-w-4xl',
    '5xl': 'max-w-5xl',
    '6xl': 'max-w-6xl',
    '7xl': 'max-w-7xl',
    full: 'max-w-full',
};

/**
 * A standard layout component for content-heavy pages like About, Contact, etc.
 * Provides a consistent hero section, container, and background effects.
 */
export function PageLayout({
    title,
    description,
    children,
    maxWidth = '5xl',
    className,
    header,
    showBackground = true,
    showHero = true,
}: PageLayoutProps) {
    return (
        <div className="relative flex min-h-[calc(100vh-(--spacing(20)))] flex-col sm:min-h-[calc(100vh-(--spacing(28)))]">
            <Head title={title} />

            {showBackground ? <BackgroundPattern /> : null}

            <div className="relative z-10 flex flex-1 flex-col">
                {header ? (
                    header
                ) : showHero ? (
                    <section className="relative overflow-hidden border-b bg-linear-to-br from-primary/5 via-background to-muted/30 py-12 sm:py-20">
                        {/* Decorative background element */}
                        <div className="absolute top-0 left-1/2 -z-10 h-full w-full -translate-x-1/2 opacity-30">
                            <div className="absolute top-0 left-1/4 h-64 w-64 rounded-full bg-primary/20 blur-3xl" />
                            <div className="absolute right-1/4 bottom-0 h-64 w-64 rounded-full bg-primary/10 blur-3xl" />
                        </div>

                        <div className="mx-auto max-w-7xl px-6 text-center lg:px-8">
                            <h1 className="animate-in text-4xl font-extrabold tracking-tight text-foreground duration-700 fade-in slide-in-from-bottom-4 sm:text-5xl lg:text-6xl">
                                {title}
                            </h1>
                            {description && (
                                <p className="mx-auto mt-6 max-w-2xl animate-in text-lg text-muted-foreground delay-100 duration-700 fade-in slide-in-from-bottom-4">
                                    {description}
                                </p>
                            )}
                        </div>
                    </section>
                ) : null}

                <main className={cn('flex-1 py-12', className)}>
                    <div
                        className={cn(
                            'mx-auto animate-in px-6 delay-200 duration-700 fade-in slide-in-from-bottom-4 lg:px-8',
                            maxWidthMap[maxWidth],
                        )}
                    >
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
