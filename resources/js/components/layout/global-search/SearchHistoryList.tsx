import { SearchResultItem } from './SearchResultItem';
import type { HistoryItem, QuickResultItem } from './types';

interface SearchHistoryListProps {
    history: HistoryItem[];
    selectedId?: string;
    onSelect: (item: QuickResultItem | HistoryItem) => void;
    onRemoveHistory: (id: string, e: React.MouseEvent) => void;
    onClearAll?: () => void;
}

export function SearchHistoryList({
    history,
    selectedId,
    onSelect,
    onRemoveHistory,
    onClearAll,
}: SearchHistoryListProps) {
    if (history.length === 0) {
        return null;
    }

    return (
        <div className="p-2">
            <div className="flex items-center justify-between px-2 py-1">
                <p className="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                    Terakhir Dilihat / Dicari
                </p>
                {onClearAll ? (
                    <button
                        type="button"
                        onClick={onClearAll}
                        className="text-[11px] text-muted-foreground transition-colors hover:text-foreground"
                    >
                        Hapus Semua
                    </button>
                ) : null}
            </div>
            <div className="space-y-0.5">
                {history.map((item) => (
                    <SearchResultItem
                        key={item.id}
                        item={item}
                        isSelected={selectedId === item.id}
                        onSelect={onSelect}
                        onRemoveHistory={onRemoveHistory}
                        isHistory
                    />
                ))}
            </div>
        </div>
    );
}
