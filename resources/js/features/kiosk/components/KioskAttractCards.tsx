export interface StatItem {
    label: string;
    value: number;
    bgHover: string;
}

export interface KioskStatsGridProps {
    stats: StatItem[];
}

export function KioskStatsGrid({ stats }: KioskStatsGridProps) {
    return (
        <div className="grid grid-cols-3 gap-2">
            {stats.map((stat) => (
                <div
                    key={stat.label}
                    className={`rounded-xl border border-border/40 bg-muted/20 px-3 py-2 text-center transition-colors ${stat.bgHover}`}
                >
                    <div className="text-xs font-medium text-muted-foreground">
                        {stat.label}
                    </div>
                    <div className="mt-1 font-mono text-base font-bold text-foreground sm:text-lg">
                        {stat.value}
                    </div>
                </div>
            ))}
        </div>
    );
}

export interface KioskTimePanelProps {
    currentTime: Date;
    formattedDate: string;
}

export function KioskTimePanel({
    currentTime,
    formattedDate,
}: KioskTimePanelProps) {
    return (
        <div className="flex flex-col items-center justify-center space-y-1 text-center">
            <div className="font-mono text-4xl font-bold tracking-tight text-foreground sm:text-5xl">
                {currentTime.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                })}
            </div>
            <div className="text-sm font-medium text-muted-foreground capitalize">
                {formattedDate}
            </div>
        </div>
    );
}

export function KioskShortcutsHelp() {
    return (
        <div className="rounded-lg border border-dashed border-border/50 bg-muted/5 px-2.5 py-2 text-center text-xs text-muted-foreground">
            <div className="flex items-center justify-between font-mono text-[11px]">
                <span>Pintasan Menu:</span>
                <kbd className="rounded border border-border/60 bg-muted px-1.5 py-0.5 text-[10px]">
                    1-4 / Alt+1-4
                </kbd>
                <span className="text-border/60">|</span>
                <span>Home / Batal:</span>
                <kbd className="rounded border border-border/60 bg-muted px-1.5 py-0.5 text-[10px]">
                    Esc
                </kbd>
            </div>
        </div>
    );
}
