import { SeoHead } from '@/components/common/SeoHead';
import { DeferredGlobalContentNotice } from '@/components/layout/GlobalContentNotice';
import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

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
     * A brief description for search engines and social sharing
     */
    metaDescription?: string;
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
    /**
     * Whether to show the desktop global notice inside the main content container
     * @default true
     */
    showDesktopNoticeInContent?: boolean;
    /**
     * Optional custom image for OG meta tags
     */
    image?: string;
    /**
     * Optional custom content type for OG meta tags
     */
    type?: 'website' | 'article';
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
    metaDescription,
    children,
    maxWidth = '5xl',
    className,
    header,
    showHero = true,
    showDesktopNoticeInContent = true,
    image,
    type,
}: Omit<PageLayoutProps, 'showBackground'>) {
    return (
        <div className="relative flex min-h-[calc(100vh-(--spacing(20)))] flex-col sm:min-h-[calc(100vh-(--spacing(28)))]">
            <SeoHead
                title={title}
                description={metaDescription ?? description}
                image={image}
                type={type}
            />

            <div className="relative z-30 flex flex-1 flex-col">
                {header ? (
                    header
                ) : showHero ? (
                    <section className="relative overflow-hidden border-b bg-background py-12 sm:py-20">

                        <div className="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                            <h1 className="text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl lg:text-6xl">
                                {title}
                            </h1>
                            {description && (
                                <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                    {description}
                                </p>
                            )}
                        </div>
                    </section>
                ) : null}

                <main className={cn('flex-1 py-12', className)}>
                    <div
                        className={cn(
                            'mx-auto px-4 sm:px-6 lg:px-8',
                            maxWidthMap[maxWidth],
                        )}
                    >
                        {showDesktopNoticeInContent ? (
                            <DeferredGlobalContentNotice className="hidden md:block" />
                        ) : null}
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
