import { cn } from '@/lib/utils';

interface StaticPageContentProps {
    html: string;
    className?: string;
}

export function StaticPageContent({
    html,
    className,
}: StaticPageContentProps) {
    return (
        <div
            className={cn(
                'mx-auto max-w-4xl rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm backdrop-blur-xs sm:p-8',
                className,
            )}
        >
            <div
                className={cn(
                    'text-sm leading-7 text-muted-foreground sm:text-base',
                    '[&_a]:font-medium [&_a]:text-primary [&_a]:underline-offset-4 hover:[&_a]:underline',
                    '[&_blockquote]:border-l-2 [&_blockquote]:border-primary/30 [&_blockquote]:pl-4 [&_blockquote]:italic',
                    '[&_h2]:mt-8 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:tracking-tight [&_h2]:text-foreground sm:[&_h2]:text-2xl',
                    '[&_h2:first-child]:mt-0',
                    '[&_h3]:mt-6 [&_h3]:text-base [&_h3]:font-semibold [&_h3]:text-foreground sm:[&_h3]:text-lg',
                    '[&_ol]:my-4 [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-5',
                    '[&_p]:my-4 [&_p:first-child]:mt-0 [&_p:last-child]:mb-0',
                    '[&_strong]:font-semibold [&_strong]:text-foreground',
                    '[&_ul]:my-4 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-5',
                )}
                dangerouslySetInnerHTML={{ __html: html }}
            />
        </div>
    );
}
