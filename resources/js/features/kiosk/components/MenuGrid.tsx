import { ChevronRight } from 'lucide-react';
import { kioskMenuItems } from '@/features/kiosk/menu';
import type { KioskMenu } from '@/features/kiosk/types';
import { cn } from '@/lib/utils';

const MENU_GRADIENTS = [
    {
        card: 'from-blue-500/10 to-cyan-500/5 border-blue-500/20 hover:border-blue-500/40',
        icon: 'bg-blue-500/15 text-blue-600 dark:text-blue-400',
    },
    {
        card: 'from-violet-500/10 to-purple-500/5 border-violet-500/20 hover:border-violet-500/40',
        icon: 'bg-violet-500/15 text-violet-600 dark:text-violet-400',
    },
    {
        card: 'from-amber-500/10 to-orange-500/5 border-amber-500/20 hover:border-amber-500/40',
        icon: 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
    },
    {
        card: 'from-emerald-500/10 to-teal-500/5 border-emerald-500/20 hover:border-emerald-500/40',
        icon: 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
    },
];

export function MenuGrid({
    onSelect,
}: {
    onSelect: (menu: KioskMenu) => void;
}) {
    return (
        <div className="grid gap-4 sm:grid-cols-2">
            {kioskMenuItems.map((item, i) => {
                const Icon = item.icon;
                const gradient = MENU_GRADIENTS[i % MENU_GRADIENTS.length];

                return (
                    <button
                        key={item.key}
                        type="button"
                        onClick={() => onSelect(item.key)}
                        className={cn(
                            'group flex cursor-pointer flex-col items-start gap-4 rounded-xl border bg-linear-to-br p-5 text-left',
                            'transition-all duration-200 hover:scale-[1.02] hover:shadow-md',
                            gradient.card,
                        )}
                    >
                        <div
                            className={cn(
                                'flex size-11 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-110',
                                gradient.icon,
                            )}
                        >
                            <Icon className="size-5" />
                        </div>

                        <div className="flex w-full items-end justify-between gap-2">
                            <div className="flex flex-col gap-1">
                                <span className="font-semibold leading-tight text-foreground">
                                    {item.label}
                                </span>
                                <span className="text-sm text-muted-foreground">
                                    {item.description}
                                </span>
                            </div>

                            <ChevronRight
                                className="size-4 shrink-0 translate-x-0 text-muted-foreground/50 opacity-0 transition-all duration-200 group-hover:translate-x-0.5 group-hover:opacity-100"
                            />
                        </div>
                    </button>
                );
            })}
        </div>
    );
}
