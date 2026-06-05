import { SeoHead } from '@/components/common/SeoHead';
import { BackgroundPattern } from '@/components/layouts/BackgroundPattern';
import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

interface ResourceDetailPageProps {
    title: string;
    description?: string;
    image?: string;
    keywords?: string | string[];
    hero: ReactNode;
    sidebar: ReactNode;
    children: ReactNode;
    footer?: ReactNode;
    showBackground?: boolean;
    deferSecondaryContent?: boolean;
    contentClassName?: string;
}

export function ResourceDetailPage({
    title,
    description,
    image,
    keywords,
    hero,
    sidebar,
    children,
    footer,
    showBackground = true,
    deferSecondaryContent = false,
    contentClassName,
}: ResourceDetailPageProps) {
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
                                className="order-2 md:order-1 md:col-span-4 lg:col-span-3"
                                style={deferredSectionStyle}
                            >
                                {sidebar}
                            </aside>

                            <div
                                className="order-1 md:order-2 md:col-span-8 lg:col-span-9"
                                style={deferredSectionStyle}
                            >
                                {children}
                            </div>
                        </div>

                        {footer ? <div className="pt-10">{footer}</div> : null}
                    </div>
                </div>
            </div>
        </>
    );
}
