import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { cn } from '@/lib/utils';

const MODAL_GRADIENTS: Record<
    string,
    { header: string; icon: string; badge: string }
> = {
    visit: {
        header: 'from-blue-500/15 via-cyan-500/5 to-transparent',
        icon: 'bg-blue-500/20 text-blue-600 dark:text-blue-400 ring-blue-500/20',
        badge: 'text-blue-600 dark:text-blue-400',
    },
    member: {
        header: 'from-violet-500/15 via-purple-500/5 to-transparent',
        icon: 'bg-violet-500/20 text-violet-600 dark:text-violet-400 ring-violet-500/20',
        badge: 'text-violet-600 dark:text-violet-400',
    },
    borrow: {
        header: 'from-amber-500/15 via-orange-500/5 to-transparent',
        icon: 'bg-amber-500/20 text-amber-600 dark:text-amber-400 ring-amber-500/20',
        badge: 'text-amber-600 dark:text-amber-400',
    },
    return: {
        header: 'from-emerald-500/15 via-teal-500/5 to-transparent',
        icon: 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 ring-emerald-500/20',
        badge: 'text-emerald-600 dark:text-emerald-400',
    },
};

interface KioskMenuModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    menuKey: string;
    icon: LucideIcon;
    title: string;
    description?: string;
    children: ReactNode;
}

export function KioskMenuModal({
    open,
    onOpenChange,
    menuKey,
    icon: Icon,
    title,
    description,
    children,
}: KioskMenuModalProps) {
    const theme = MODAL_GRADIENTS[menuKey] ?? MODAL_GRADIENTS['visit'];

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                className="max-h-[94dvh] w-[min(94vw,1440px)] max-w-none gap-0 overflow-hidden rounded-[2rem] p-0 sm:!max-w-none"
                showCloseButton={false}
            >
                {/* Gradient header */}
                <div
                    className={cn(
                        'relative overflow-hidden border-b bg-linear-to-br px-8 pt-7 pb-6',
                        theme.header,
                    )}
                >
                    {/* Decorative blob */}
                    <div className="pointer-events-none absolute -top-10 -right-10 size-40 rounded-full bg-current opacity-5 blur-3xl" />

                    <DialogHeader className="relative z-10">
                        <div className="flex items-center gap-3">
                            <div
                                className={cn(
                                    'flex size-10 shrink-0 items-center justify-center rounded-xl ring-1',
                                    theme.icon,
                                )}
                            >
                                <Icon className="size-5" />
                            </div>
                            <div>
                                <DialogTitle className="text-lg leading-tight font-bold">
                                    {title}
                                </DialogTitle>
                                {description && (
                                    <DialogDescription className="mt-0.5 text-sm">
                                        {description}
                                    </DialogDescription>
                                )}
                            </div>
                        </div>
                    </DialogHeader>

                    {/* Close button */}
                    <button
                        type="button"
                        onClick={() => onOpenChange(false)}
                        aria-label="Tutup"
                        className="absolute top-4 right-4 flex size-7 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-foreground/10 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        >
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                {/* Scrollable form body */}
                <ScrollArea className="max-h-[calc(94dvh-6rem)]">
                    <div className="px-8 py-7">{children}</div>
                </ScrollArea>
            </DialogContent>
        </Dialog>
    );
}
