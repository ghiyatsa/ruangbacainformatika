/* eslint-disable react-hooks/set-state-in-effect */
import { router, useHttp } from '@inertiajs/react';
import { Search, Loader2, History, X } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import * as React from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const SEARCH_SUGGESTIONS_ENDPOINT = '/search/suggestions';
const STORAGE_KEY = 'global_search_history';
const MAX_HISTORY = 5;

interface GlobalSearchDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

/**
 * Highlights matches of query query inside text with HTML <strong> tag.
 */
function getHighlightedText(text: string, highlight: string) {
    if (!highlight.trim()) {
        return <span>{text}</span>;
    }
    
    const escapedHighlight = highlight.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&');
    const regex = new RegExp(`(${escapedHighlight})`, 'gi');
    const parts = text.split(regex);
    
    return (
        <span>
            {parts.map((part, index) =>
                regex.test(part) ? (
                    <strong key={index} className="font-extrabold text-foreground">
                        {part}
                    </strong>
                ) : (
                    <span key={index} className="text-muted-foreground">
                        {part}
                    </span>
                )
            )}
        </span>
    );
}

export function GlobalSearchDialog({
    open,
    onOpenChange,
}: GlobalSearchDialogProps) {
    const [query, setQuery] = React.useState('');
    const [suggestions, setSuggestions] = React.useState<string[]>([]);
    const [history, setHistory] = React.useState<string[]>([]);
    const [selectedIndex, setSelectedIndex] = React.useState(-1);
    const [isDebouncing, setIsDebouncing] = React.useState(false);

    const http = useHttp();
    const isLoading = http.processing || isDebouncing;

    React.useEffect(() => {
        if (typeof window !== 'undefined') {
            try {
                const stored = localStorage.getItem(STORAGE_KEY);

                if (stored) {
                    setHistory(JSON.parse(stored));
                }
            } catch (e) {
                console.error('Failed to load search history', e);
            }
        }
    }, [open]);

    const saveToHistory = React.useCallback((searchQuery: string) => {
        const trimmed = searchQuery.trim();

        if (!trimmed) {
            return;
        }

        setHistory((prev) => {
            const filtered = prev.filter((item) => item !== trimmed);
            const updated = [trimmed, ...filtered].slice(0, MAX_HISTORY);

            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
            } catch (e) {
                console.error('Failed to save search history', e);
            }

            return updated;
        });
    }, []);

    const deleteHistoryItem = React.useCallback((itemToDelete: string, e: React.MouseEvent) => {
        e.stopPropagation();
        e.preventDefault();
        setHistory((prev) => {
            const updated = prev.filter((item) => item !== itemToDelete);

            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
            } catch (err) {
                console.error('Failed to delete search history item', err);
            }

            return updated;
        });
    }, []);

    const items = React.useMemo(() => {
        if (query.trim() === '') {
            return history;
        }

        return [...suggestions, `Cari semua untuk "${query}"`];
    }, [suggestions, query, history]);

    const handleQueryChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setQuery(value);
        setSelectedIndex(-1);

        if (!value) {
            setSuggestions([]);
            setIsDebouncing(false);
        } else {
            setIsDebouncing(true);
        }
    };

    React.useEffect(() => {
        if (!query) {
            return;
        }

        const timeoutId = setTimeout(() => {
            setIsDebouncing(false);
            http.get(
                `${SEARCH_SUGGESTIONS_ENDPOINT}?q=${encodeURIComponent(query)}`,
                {
                    onSuccess: (data: unknown) => {
                        setSuggestions((data as string[]) || []);
                    },
                    onError: () => {
                        setSuggestions([]);
                    },
                }
            );
        }, 200);

        return () => {
            clearTimeout(timeoutId);
            http.cancel();
        };
    }, [query, http]);

    const handleSelect = React.useCallback(
        (targetQuery: string) => {
            onOpenChange(false);
            const actualQuery = targetQuery.startsWith('Cari semua untuk "') || targetQuery.startsWith('Cari "')
            ? query
            : targetQuery;
            saveToHistory(actualQuery);
            router.visit(`/search?q=${encodeURIComponent(actualQuery)}`);
        },
        [onOpenChange, query, saveToHistory],
    );

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setSelectedIndex((prev) => (prev + 1 < items.length ? prev + 1 : prev));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setSelectedIndex((prev) => (prev - 1 >= 0 ? prev - 1 : prev));
        } else if (e.key === 'Enter') {
            e.preventDefault();

            if (selectedIndex >= 0 && selectedIndex < items.length) {
                handleSelect(items[selectedIndex]);
            } else if (query.trim() !== '') {
                handleSelect(query);
            }
        } else if (e.key === 'Escape') {
            onOpenChange(false);
        }
    };

    React.useEffect(() => {
        if (!open) {
            setQuery('');
            setSuggestions([]);
            setSelectedIndex(-1);
            setIsDebouncing(false);
        }
    }, [open]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogHeader className="sr-only">
                <DialogTitle>Pencarian Global</DialogTitle>
                <DialogDescription>Masukkan kata kunci pencarian</DialogDescription>
            </DialogHeader>
            <DialogContent
                className="top-[15%]! sm:top-[20%]! translate-y-0! overflow-hidden rounded-xl! p-0! gap-0! w-full sm:max-w-2xl! border bg-popover shadow-lg"
                showCloseButton={false}
            >
                <div className={`flex items-center px-3 ${query.length > 0 ? 'border-b' : history.length > 0 ? 'border-b' : ''}`}>
                    <Search className="mr-2 h-4 w-4 shrink-0 opacity-50" />
                    <input
                        className="h-12 w-full bg-transparent text-sm outline-none border-none focus-visible:ring-0 focus-visible:ring-offset-0 focus:outline-none focus:ring-0 px-0 placeholder:text-muted-foreground"
                        placeholder="Cari buku, artikel, atau karya ilmiah..."
                        value={query}
                        onChange={handleQueryChange}
                        onKeyDown={handleKeyDown}
                        autoFocus
                    />
                    {isLoading && <Loader2 className="h-4 w-4 animate-spin opacity-50 ml-2" />}
                </div>

                <AnimatePresence initial={false}>
                    {query.length > 0 || history.length > 0 ? (
                        <motion.div
                            key="global-search-container"
                            initial={{ height: 0, opacity: 0 }}
                            animate={{ height: 'auto', opacity: 1 }}
                            exit={{ height: 0, opacity: 0 }}
                            transition={{
                                type: 'spring',
                                duration: 0.3,
                                bounce: 0,
                            }}
                            className="w-full overflow-hidden flex flex-col max-h-72"
                        >
                            {query.length > 0 ? (
                                isLoading ? (
                                    <div className="p-1.5 space-y-0.5">
                                        <div className="flex items-center gap-2 px-1.5 py-2">
                                            <Search className="size-4 shrink-0 text-muted-foreground/30 animate-pulse" />
                                            <div className="h-5 w-full rounded bg-muted animate-pulse" />
                                        </div>
                                        <div className="flex items-center gap-2 px-1.5 py-2">
                                            <Search className="size-4 shrink-0 text-muted-foreground/30 animate-pulse" />
                                            <div className="h-5 w-full rounded bg-muted animate-pulse" />
                                        </div>
                                        <div className="flex items-center gap-2 px-1.5 py-2">
                                            <Search className="size-4 shrink-0 text-muted-foreground/30 animate-pulse" />
                                            <div className="h-5 w-full rounded bg-muted animate-pulse" />
                                        </div>
                                    </div>
                                ) : suggestions.length === 0 ? (
                                    <div className="p-1.5">
                                        <button
                                            onClick={() => handleSelect(`Cari semua untuk "${query}"`)}
                                            className="flex w-full items-center gap-2 px-1.5 py-2 text-sm text-primary hover:bg-accent rounded-lg text-left min-w-0 cursor-pointer font-medium"
                                        >
                                            <Search className="size-4 shrink-0 text-primary" />
                                            <span className="truncate flex-1 min-w-0">Cari semua untuk &quot;{query}&quot;</span>
                                        </button>
                                    </div>
                                ) : (
                                    <>
                                        {/* Scrollable Suggestions List */}
                                        <div className="overflow-y-auto max-h-[15.5rem] no-scrollbar p-1.5">
                                            <div className="space-y-0.5">
                                                {suggestions.map((item, idx) => {
                                                    const isSelected = selectedIndex === idx;
                                                    const isInHistory = history.includes(item);

                                                    return (
                                                        <button
                                                            key={item}
                                                            onClick={() => handleSelect(item)}
                                                            className={`flex w-full items-center gap-2 px-1.5 py-2 text-sm rounded-lg text-left cursor-pointer transition-colors min-w-0 ${
                                                                isSelected
                                                                    ? 'bg-accent text-accent-foreground'
                                                                    : 'hover:bg-accent/50'
                                                            }`}
                                                        >
                                                            {isInHistory ? (
                                                                <History className="size-4 shrink-0 text-muted-foreground" />
                                                            ) : (
                                                                <Search className="size-4 shrink-0 text-muted-foreground" />
                                                            )}
                                                            <span className="truncate flex-1 min-w-0">
                                                                {getHighlightedText(item, query)}
                                                            </span>
                                                        </button>
                                                    );
                                                })}
                                            </div>
                                        </div>

                                        {/* Sticky Bottom "Cari semua..." button */}
                                        <div className="sticky bottom-0 bg-popover p-1.5 border-t border-dashed border-border mt-auto">
                                            {(() => {
                                                const searchAllIndex = suggestions.length;
                                                const isSelected = selectedIndex === searchAllIndex;
                                                const label = `Cari semua untuk "${query}"`;

                                                return (
                                                    <button
                                                        onClick={() => handleSelect(label)}
                                                        className={`flex w-full items-center gap-2 px-1.5 py-2 text-sm rounded-lg text-left cursor-pointer transition-colors min-w-0 ${
                                                            isSelected
                                                                ? 'bg-accent text-accent-foreground'
                                                                : 'hover:bg-accent/50'
                                                        }`}
                                                    >
                                                        <Search className="size-4 shrink-0 text-primary" />
                                                        <span className="truncate flex-1 min-w-0 text-primary font-medium block">
                                                            {label}
                                                        </span>
                                                    </button>
                                                );
                                            })()}
                                        </div>
                                    </>
                                )
                            ) : (
                                <div className="p-1.5">
                                    <div className="space-y-0.5 max-h-56 overflow-y-auto no-scrollbar">
                                        {history.map((item, idx) => {
                                            const isSelected = selectedIndex === idx;

                                            return (
                                                <button
                                                    key={item}
                                                    onClick={() => handleSelect(item)}
                                                    className={`flex w-full items-center justify-between gap-2 px-1.5 py-2 text-sm rounded-lg text-left cursor-pointer transition-colors min-w-0 ${
                                                        isSelected
                                                            ? 'bg-accent text-accent-foreground'
                                                            : 'hover:bg-accent/50'
                                                    }`}
                                                >
                                                    <div className="flex items-center gap-2 min-w-0 flex-1">
                                                        <History className="size-4 shrink-0 text-muted-foreground" />
                                                        <span className="truncate text-muted-foreground">{item}</span>
                                                    </div>
                                                    <span
                                                        onClick={(e) => deleteHistoryItem(item, e)}
                                                        className="p-1 rounded-md hover:bg-foreground/10 text-muted-foreground hover:text-foreground cursor-pointer shrink-0 transition-colors"
                                                    >
                                                        <X className="size-3" />
                                                    </span>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </motion.div>
                    ) : null}
                </AnimatePresence>
            </DialogContent>
        </Dialog>
    );
}
