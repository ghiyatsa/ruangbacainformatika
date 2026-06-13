import { cn } from '@/lib/utils';

interface StaticPageContentProps {
    html: string;
    className?: string;
}

export function StaticPageContent({ html, className }: StaticPageContentProps) {
    return (
        <div
            className={cn(
                'mx-auto max-w-4xl rounded-3xl border border-border/60 bg-card p-6 shadow-sm sm:p-8',
                className,
            )}
        >
            <div
                className={cn(
                    'text-sm leading-8 text-muted-foreground sm:text-base',
                    '[&_a]:font-medium [&_a]:text-primary [&_a]:underline [&_a]:underline-offset-4 hover:[&_a]:text-primary/80',
                    '[&_blockquote]:my-6 [&_blockquote]:rounded-r-xl [&_blockquote]:border-l-4 [&_blockquote]:border-primary [&_blockquote]:bg-muted/30 [&_blockquote]:py-3 [&_blockquote]:pr-4 [&_blockquote]:pl-4 [&_blockquote]:text-foreground/90 [&_blockquote]:italic',
                    '[&_h1]:mt-10 [&_h1]:mb-4 [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:tracking-tight [&_h1]:text-foreground sm:[&_h1]:text-3xl',
                    '[&_h1:first-child]:mt-0',
                    '[&_h2]:mt-8 [&_h2]:mb-4 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:tracking-tight [&_h2]:text-foreground sm:[&_h2]:text-2xl',
                    '[&_h2:first-child]:mt-0',
                    '[&_h3]:mt-6 [&_h3]:mb-3 [&_h3]:text-base [&_h3]:font-semibold [&_h3]:tracking-tight [&_h3]:text-foreground sm:[&_h3]:text-lg',
                    '[&_h3:first-child]:mt-0',
                    '[&_h4]:mt-6 [&_h4]:mb-2 [&_h4]:text-sm [&_h4]:font-semibold [&_h4]:text-foreground sm:[&_h4]:text-base',
                    '[&_p]:my-4 [&_p]:leading-8 [&_p:first-child]:mt-0 [&_p:last-child]:mb-0',
                    '[&_strong]:font-semibold [&_strong]:text-foreground',
                    '[&_ul]:my-4 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6',
                    '[&_ol]:my-4 [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6',
                    '[&_li]:my-1.5',
                    '[&_ul_ul]:my-2 [&_ul_ul]:list-[circle] [&_ul_ul]:pl-5',
                    '[&_ol_ol]:my-2 [&_ol_ol]:list-[lower-alpha] [&_ol_ol]:pl-5',
                    '[&_table]:my-6 [&_table]:w-full [&_table]:border-collapse [&_table]:overflow-hidden [&_table]:rounded-xl',
                    '[&_th]:bg-muted/40 [&_th]:px-4 [&_th]:py-3 [&_th]:text-left [&_th]:text-sm [&_th]:font-semibold [&_th]:text-foreground',
                    '[&_td]:px-4 [&_td]:py-3 [&_td]:text-sm [&_td]:text-muted-foreground hover:[&_tr]:bg-muted/20',
                    '[&_pre]:my-6 [&_pre]:overflow-x-auto [&_pre]:rounded-xl [&_pre]:bg-muted/40 [&_pre]:p-4 [&_pre]:font-mono [&_pre]:text-sm [&_pre]:text-foreground',
                    '[&_code]:rounded-md [&_code]:bg-muted/60 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-xs [&_code]:text-foreground [&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-sm',
                    '[&_hr]:my-8 [&_hr]:border-t [&_hr]:border-border/60',
                    '[&_img]:mx-auto [&_img]:my-6 [&_img]:max-w-full [&_img]:rounded-2xl [&_img]:shadow-xs',
                )}
                dangerouslySetInnerHTML={{ __html: html }}
            />
        </div>
    );
}
