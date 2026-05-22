import { ChevronRight } from 'lucide-react';
import { kioskMenuItems } from '@/features/kiosk/menu';
import { cn } from '@/lib/utils';
import type { KioskMenu } from '@/features/kiosk/types';

export function MenuGrid({
    activeMenu,
    onSelect,
}: {
    activeMenu: KioskMenu | null;
    onSelect: (menu: KioskMenu) => void;
}) {
    return (
        <div className="grid gap-3">
            {kioskMenuItems.map((item) => {
                const Icon = item.icon;
                const isActive = activeMenu === item.key;

                return (
                    <button
                        key={item.key}
                        type="button"
                        onClick={() => onSelect(item.key)}
                        className={cn(
                            'flex items-center justify-between gap-3 rounded-xl border px-4 py-3 text-left transition-colors',
                            isActive
                                ? 'border-primary/30 bg-primary/8 text-foreground'
                                : 'border-border/70 bg-background hover:bg-muted/40',
                        )}
                    >
                        <div className="flex min-w-0 items-center gap-3">
                            <div
                                className={cn(
                                    'flex size-9 shrink-0 items-center justify-center rounded-lg',
                                    isActive
                                        ? 'bg-primary/12 text-primary'
                                        : 'bg-muted text-muted-foreground',
                                )}
                            >
                                <Icon className="size-4" />
                            </div>
                            <div className="min-w-0">
                                <p className="truncate text-sm font-semibold">
                                    {item.label}
                                </p>
                                <p className="truncate text-xs text-muted-foreground">
                                    {item.description}
                                </p>
                            </div>
                        </div>

                        <ChevronRight
                            className={cn(
                                'size-4 shrink-0 text-muted-foreground',
                                isActive && 'text-primary',
                            )}
                        />
                    </button>
                );
            })}
        </div>
    );
}
