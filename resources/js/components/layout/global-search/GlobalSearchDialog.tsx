/* eslint-disable react-hooks/set-state-in-effect */
import { router } from '@inertiajs/react';
import { Search, Loader2 } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import * as React from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';

const SEARCH_SUGGESTIONS_ENDPOINT = '/search/suggestions';

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
    const [isLoading, setIsLoading] = React.useState(false);
    const [selectedIndex, setSelectedIndex] = React.useState(-1);

        const items = React.useMemo(() => {
        if (query.trim() === '') {
return [];
}

        return [...suggestions, `Cari semua untuk "${query}"`];
    }, [suggestions, query]);

    const handleQueryChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setQuery(value);
        setSelectedIndex(-1);

        if (!value) {
            setSuggestions([]);
            setIsLoading(false);

            return;
        }

        setIsLoading(true);
    };

    React.useEffect(() => {
        if (!query) {
            return;
        }

        const abortController = new AbortController();
        const timeoutId = setTimeout(async () => {
            try {
                const response = await fetch(
                    `${SEARCH_SUGGESTIONS_ENDPOINT}?q=${encodeURIComponent(query)}`,
                    {
                        signal: abortController.signal,
                    },
                );
                const data = (await response.json()) as string[];
                setSuggestions(data || []);
            } catch (error) {
                if (
                    error instanceof DOMException &&
                    error.name === 'AbortError'
                ) {
                    return;
                }

                console.error('Search suggestions failed:', error);
                setSuggestions([]);
            } finally {
                if (!abortController.signal.aborted) {
                    setIsLoading(false);
                }
            }
        }, 200);

        return () => {
            clearTimeout(timeoutId);
            abortController.abort();
        };
    }, [query]);

    const handleSelect = React.useCallback(
        (targetQuery: string) => {
            onOpenChange(false);
            const actualQuery = targetQuery.startsWith('Cari semua untuk "')
                ? query
                : targetQuery;
            router.visit(`/search?q=${encodeURIComponent(actualQuery)}`);
        },
        [onOpenChange, query],
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
                <div className={`flex items-center px-3 ${query.length > 0 ? 'border-b' : ''}`}>
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
                    {query.length > 0 ? (
                        <motion.div
                            key="search-content"
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
                            {isLoading ? (
                                <div className="p-3 space-y-2">
                                    <Skeleton className="h-8 w-3/4 rounded-lg" />
                                    <Skeleton className="h-8 w-5/6 rounded-lg" />
                                    <Skeleton className="h-8 w-2/3 rounded-lg" />
                                </div>
                            ) : suggestions.length === 0 ? (
                                <div className="p-1.5">
                                    <button
                                        onClick={() => handleSelect(query)}
                                        className="flex w-full items-center gap-2 px-1.5 py-2 text-sm text-muted-foreground hover:text-foreground hover:bg-accent rounded-lg text-left min-w-0 cursor-pointer"
                                    >
                                        <Search className="size-4 shrink-0" />
                                        <span className="truncate flex-1 min-w-0">Cari &quot;{query}&quot;</span>
                                    </button>
                                </div>
                            ) : (
                                <>
                                    {/* Scrollable Suggestions List */}
                                    <div className="overflow-y-auto max-h-[15.5rem] no-scrollbar p-1.5">
                                        <div className="space-y-0.5">
                                            {suggestions.map((item, idx) => {
                                                const isSelected = selectedIndex === idx;

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
                                                        <Search className="size-4 shrink-0 text-muted-foreground" />
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
                            )}
                        </motion.div>
                    ) : null}
                </AnimatePresence>
            </DialogContent>
        </Dialog>
    );
}
