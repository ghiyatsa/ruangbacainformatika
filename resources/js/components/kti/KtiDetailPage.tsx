import { SeoHead } from '@/components/common/SeoHead';
import { BackgroundPattern } from '@/components/layout/BackgroundPattern';
import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

interface KtiDetailPageProps {
    title: string;
    description?: string;
    image?: string;
    keywords?: string | string[];
    hero: ReactNode;
    sidebar: ReactNode;
    secondarySidebar?: ReactNode;
    children: ReactNode;
    footer?: ReactNode;
    showBackground?: boolean;
    deferSecondaryContent?: boolean;
    contentClassName?: string;
}

export function KtiDetailPage({
    title,
    description,
    image,
    keywords,
    hero,
    sidebar,
    secondarySidebar,
    children,
    footer,
    showBackground = true,
    deferSecondaryContent = false,
    contentClassName,
}: KtiDetailPageProps) {
    const hasSecondarySidebar =
        secondarySidebar !== undefined && secondarySidebar !== null;
    const deferredSectionStyle = deferSecondaryContent
        ? {
              contentVisibility: 'auto' as const,
              containIntrinsicSize: '1px 720px',
          }
        : undefined;

    return (
        <>
            <SeoHead
                title={title}
                description={description}
                image={image}
                keywords={keywords}
                type="article"
            />
            {showBackground ? <BackgroundPattern /> : null}

            <div className="relative z-10 flex flex-col">
                {hero}

                <div className={cn('py-10', contentClassName)}>
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid gap-8 md:grid-cols-12 md:gap-10">
                            <aside
                                className="order-3 md:order-1 md:col-span-4 lg:col-span-3"
                                style={deferredSectionStyle}
                            >
                                <div
                                    className={cn(
                                        hasSecondarySidebar &&
                                            'xl:sticky xl:top-24',
                                    )}
                                >
                                    {sidebar}
                                </div>
                            </aside>

                            <div
                                className={cn(
                                    'order-1 md:order-2 md:col-span-8',
                                    hasSecondarySidebar
                                        ? 'lg:col-span-6'
                                        : 'lg:col-span-9',
                                )}
                                style={deferredSectionStyle}
                            >
                                {children}
                            </div>

                            {hasSecondarySidebar ? (
                                <aside
                                    className="order-2 md:order-3 md:col-span-12 lg:col-span-3"
                                    style={deferredSectionStyle}
                                >
                                    <div className="xl:sticky xl:top-24">
                                        {secondarySidebar}
                                    </div>
                                </aside>
                            ) : null}
                        </div>

                        {footer ? <div className="pt-10">{footer}</div> : null}
                    </div>
                </div>
            </div>
        </>
    );
}
