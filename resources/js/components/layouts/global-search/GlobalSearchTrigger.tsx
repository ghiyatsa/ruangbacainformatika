import { Search } from 'lucide-react';
import { Kbd } from '@/components/ui/kbd';

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
            <Kbd className="ml-auto">⌘ K</Kbd>
        </button>
    );
}
