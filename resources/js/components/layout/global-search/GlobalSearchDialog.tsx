/* eslint-disable react-hooks/set-state-in-effect */
import { router, useHttp } from '@inertiajs/react';
import { Loader2, Search } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import * as React from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    clearAllSearchHistory,
    loadSearchHistory,
    removeSearchHistoryItem,
    saveSearchHistoryItem,
} from './history-storage';
import { SearchFooter } from './SearchFooter';
import { SearchHistoryList } from './SearchHistoryList';
import { SearchResultsList } from './SearchResultsList';
import { SearchResultsSkeleton } from './SearchResultsSkeleton';
import type {
    HistoryItem,
    QuickResultItem,
    SuggestionApiResponse,
} from './types';

const SEARCH_ENDPOINT = '/search/suggestions';

interface GlobalSearchDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function GlobalSearchDialog({
    open,
    onOpenChange,
}: GlobalSearchDialogProps) {
    const [query, setQuery] = React.useState('');
    const [quickResults, setQuickResults] = React.useState<
        SuggestionApiResponse['quickResults']
    >({});
    const [history, setHistory] = React.useState<HistoryItem[]>([]);
    const [isDebouncing, setIsDebouncing] = React.useState(false);
    const [selectedIndex, setSelectedIndex] = React.useState<number>(0);

    const http = useHttp();
    const httpRef = React.useRef(http);

    React.useEffect(() => {
        httpRef.current = http;
    }, [http]);

    const isLoading = http.processing || isDebouncing;

    // Muat riwayat terakhir saat dialog dibuka
    React.useEffect(() => {
        if (open) {
            setHistory(loadSearchHistory());
        }
    }, [open]);

    // Handle Query input & debounce fetch
    const handleQueryChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setQuery(value);

        if (!value.trim()) {
            setQuickResults({});
            setIsDebouncing(false);
        } else {
            setIsDebouncing(true);
        }
    };

    React.useEffect(() => {
        if (!query.trim()) {
            return;
        }

        const timeoutId = setTimeout(() => {
            setIsDebouncing(false);
            httpRef.current.get(
                `${SEARCH_ENDPOINT}?q=${encodeURIComponent(query.trim())}`,
                {
                    onSuccess: (data: unknown) => {
                        const payload = (data || {}) as SuggestionApiResponse;
                        setQuickResults(payload.quickResults || {});
                    },
                    onError: () => {
                        setQuickResults({});
                    },
                },
            );
        }, 160);

        return () => {
            clearTimeout(timeoutId);
            httpRef.current.cancel();
        };
    }, [query]);

    const books = React.useMemo(
        () => quickResults?.books || [],
        [quickResults?.books],
    );
    const skripsi = React.useMemo(
        () => quickResults?.skripsi || [],
        [quickResults?.skripsi],
    );
    const posts = React.useMemo(
        () => quickResults?.posts || [],
        [quickResults?.posts],
    );

    const isSearching = query.trim().length > 0;
    const hasLiveResults =
        books.length > 0 || skripsi.length > 0 || posts.length > 0;

    // Item aktif yang dapat dinavigasi panah atas/bawah
    const activeItems = React.useMemo<
        Array<QuickResultItem | HistoryItem>
    >(() => {
        if (isSearching) {
            return [...books, ...skripsi, ...posts];
        }

        return history;
    }, [isSearching, books, skripsi, posts, history]);

    React.useEffect(() => {
        setSelectedIndex(0);
    }, [query, quickResults, history]);

    // Navigasi ke item yang dipilih dan catat ke riwayat
    const handleSelectItem = React.useCallback(
        (item: QuickResultItem | HistoryItem) => {
            onOpenChange(false);
            const updated = saveSearchHistoryItem({
                id: `${item.type}-${item.id}`,
                title: item.title,
                subtitle: item.subtitle,
                url: item.url,
                type: item.type,
            });

            setHistory(updated);
            router.visit(item.url);
        },
        [onOpenChange],
    );

    const handleRemoveHistory = React.useCallback(
        (id: string, e: React.MouseEvent) => {
            e.stopPropagation();
            e.preventDefault();
            const updated = removeSearchHistoryItem(id);

            setHistory(updated);
        },
        [],
    );

    const handleClearAllHistory = React.useCallback(() => {
        clearAllSearchHistory();
        setHistory([]);
    }, []);

    // Navigasi Keyboard: ↑, ↓, ↵ Enter, Esc
    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();

            if (activeItems.length > 0) {
                setSelectedIndex((prev) =>
                    prev < activeItems.length - 1 ? prev + 1 : 0,
                );
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();

            if (activeItems.length > 0) {
                setSelectedIndex((prev) =>
                    prev > 0 ? prev - 1 : activeItems.length - 1,
                );
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();

            if (activeItems.length > 0 && activeItems[selectedIndex]) {
                handleSelectItem(activeItems[selectedIndex]);
            }
        } else if (e.key === 'Escape') {
            onOpenChange(false);
        }
    };

    // Reset state saat dialog ditutup
    React.useEffect(() => {
        if (!open) {
            setQuery('');
            setQuickResults({});
            setIsDebouncing(false);
            setSelectedIndex(0);
        }
    }, [open]);

    const selectedItem = activeItems[selectedIndex];
    const selectedId = selectedItem
        ? isSearching
            ? `${selectedItem.type}-${selectedItem.id}`
            : (selectedItem as HistoryItem).id
        : undefined;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogHeader className="sr-only">
                <DialogTitle>Pencarian Koleksi</DialogTitle>
                <DialogDescription>
                    Pencarian instan koleksi buku, skripsi, dan artikel.
                </DialogDescription>
            </DialogHeader>
            <DialogContent
                className="top-[15%] w-full translate-y-0 gap-0 overflow-hidden rounded-2xl border bg-popover p-0 shadow-2xl sm:top-[18%] sm:max-w-2xl"
                overlayClassName="bg-black/60 backdrop-blur-xs"
                showCloseButton={false}
            >
                <div
                    className={`flex items-center px-4 ${query.length > 0 || history.length > 0 ? 'border-b' : ''}`}
                >
                    <Search className="mr-3 size-4 shrink-0 text-muted-foreground" />
                    <input
                        className="h-13 w-full border-none bg-transparent px-0 text-sm outline-none placeholder:text-muted-foreground focus:ring-0 focus:outline-none"
                        placeholder="Ketik judul buku, nama pengarang, topik skripsi, artikel..."
                        value={query}
                        onChange={handleQueryChange}
                        onKeyDown={handleKeyDown}
                        autoFocus
                    />
                    {isLoading && (
                        <Loader2 className="ml-2 size-4 animate-spin text-muted-foreground" />
                    )}
                </div>

                <AnimatePresence initial={false}>
                    {isSearching || history.length > 0 ? (
                        <motion.div
                            key="global-search-container"
                            initial={{ height: 0, opacity: 0 }}
                            animate={{ height: 'auto', opacity: 1 }}
                            exit={{ height: 0, opacity: 0 }}
                            transition={{ duration: 0.2 }}
                            className="flex max-h-[28rem] w-full flex-col overflow-hidden"
                        >
                            <div className="no-scrollbar flex-1 overflow-y-auto p-2">
                                {isSearching ? (
                                    isLoading && !hasLiveResults ? (
                                        <SearchResultsSkeleton />
                                    ) : !isLoading && !hasLiveResults ? (
                                        <div className="py-12 text-center text-sm text-muted-foreground">
                                            <Search className="mx-auto mb-2 size-8 opacity-30" />
                                            Tidak ditemukan hasil untuk &ldquo;
                                            <span className="font-medium text-foreground">
                                                {query}
                                            </span>
                                            &rdquo;
                                        </div>
                                    ) : (
                                        <SearchResultsList
                                            books={books}
                                            skripsi={skripsi}
                                            posts={posts}
                                            selectedId={selectedId}
                                            onSelect={handleSelectItem}
                                        />
                                    )
                                ) : (
                                    <SearchHistoryList
                                        history={history}
                                        selectedId={selectedId}
                                        onSelect={handleSelectItem}
                                        onRemoveHistory={handleRemoveHistory}
                                        onClearAll={handleClearAllHistory}
                                    />
                                )}
                            </div>

                            <SearchFooter />
                        </motion.div>
                    ) : null}
                </AnimatePresence>
            </DialogContent>
        </Dialog>
    );
}
