import {
    BookOpen,
    FileText,
    GraduationCap,
    History as HistoryIcon,
    X,
} from 'lucide-react';
import type { HistoryItem, QuickResultItem } from './types';

interface SearchResultItemProps {
    item: QuickResultItem | HistoryItem;
    isSelected: boolean;
    onSelect: (item: QuickResultItem | HistoryItem) => void;
    onRemoveHistory?: (id: string, e: React.MouseEvent) => void;
    isHistory?: boolean;
}

export function SearchResultItem({
    item,
    isSelected,
    onSelect,
    onRemoveHistory,
    isHistory = false,
}: SearchResultItemProps) {
    const renderIcon = () => {
        if (isHistory) {
            return (
                <HistoryIcon className="size-4 shrink-0 text-muted-foreground" />
            );
        }

        switch (item.type) {
            case 'book':
                return <BookOpen className="size-4 shrink-0 text-primary" />;
            case 'skripsi':
                return (
                    <GraduationCap className="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                );
            case 'post':
                return (
                    <FileText className="size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                );
            default:
                return <BookOpen className="size-4 shrink-0 text-primary" />;
        }
    };

    return (
        <div
            className={`group flex items-center justify-between rounded-lg px-2.5 py-2 transition-colors ${
                isSelected
                    ? 'bg-accent text-accent-foreground ring-1 ring-border'
                    : 'hover:bg-accent/70'
            }`}
        >
            <button
                type="button"
                onClick={() => onSelect(item)}
                className="flex min-w-0 flex-1 items-center gap-3 text-left"
            >
                {renderIcon()}
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium text-foreground">
                        {item.title}
                    </p>
                    {item.subtitle ? (
                        <p className="truncate text-xs text-muted-foreground">
                            {item.subtitle}
                        </p>
                    ) : null}
                </div>
            </button>

            {isHistory && onRemoveHistory ? (
                <button
                    type="button"
                    onClick={(e) => onRemoveHistory(String(item.id), e)}
                    className="ml-2 rounded p-1 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 hover:bg-background/80 hover:text-foreground"
                    title="Hapus dari riwayat"
                >
                    <X className="size-3.5" />
                </button>
            ) : null}
        </div>
    );
}
