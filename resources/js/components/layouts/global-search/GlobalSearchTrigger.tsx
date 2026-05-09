import { Search } from 'lucide-react';

interface GlobalSearchTriggerProps {
    onClick: () => void;
}

export function GlobalSearchTrigger({ onClick }: GlobalSearchTriggerProps) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="relative flex h-9 w-full items-center justify-start gap-2 rounded-xl border border-accent/50 bg-muted/50 px-3 text-sm text-muted-foreground transition-colors hover:bg-muted md:w-64 lg:w-80"
        >
            <Search className="size-4" />
            <span>Cari buku...</span>
            <kbd className="pointer-events-none absolute top-1/2 right-2 hidden -translate-y-1/2 items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium opacity-100 sm:flex">
                <span className="text-xs">Ctrl</span>K
            </kbd>
        </button>
    );
}
