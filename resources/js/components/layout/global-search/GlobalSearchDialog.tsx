/* eslint-disable react-hooks/set-state-in-effect */
import { router, useHttp } from '@inertiajs/react';
import {
    BookOpen,
    FileText,
    GraduationCap,
    History,
    Loader2,
    Search,
    X,
} from 'lucide-react';
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

interface QuickResultItem {
    type: 'book' | 'skripsi' | 'post';
    id: number;
    title: string;
    subtitle: string;
    url: string;
}

interface SuggestionApiResponse {
    suggestions?: string[];
    quickResults?: {
        books?: QuickResultItem[];
        skripsi?: QuickResultItem[];
        posts?: QuickResultItem[];
    };
}

interface GlobalSearchDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

function getHighlightedText(text: string, highlight: string) {
    const query = highlight.trim().toLowerCase();

    if (!query) {
        return <span>{text}</span>;
    }

    const source = text.toLowerCase();
    const parts: React.ReactNode[] = [];
    let queryIndex = 0;
    let run: string[] = [];
    let runIsMatch = false;

    const flush = () => {
        if (run.length === 0) {
            return;
        }

        const chunk = run.join('');
        const key = parts.length;

        if (runIsMatch) {
            parts.push(
                <strong key={key} className="font-bold text-foreground">
                    {chunk}
                </strong>,
            );
        } else {
            parts.push(
                <span key={key} className="text-muted-foreground">
                    {chunk}
                </span>,
            );
        }

        run = [];
    };

    for (let i = 0; i < text.length; i++) {
        const isMatch =
            queryIndex < query.length && source[i] === query[queryIndex];

        if (isMatch) {
            queryIndex++;
        }

        if (isMatch !== runIsMatch) {
            flush();
            runIsMatch = isMatch;
        }

        run.push(text[i]);
    }

    flush();

    return <span>{parts}</span>;
}

export function GlobalSearchDialog({
    open,
    onOpenChange,
}: GlobalSearchDialogProps) {
    const [query, setQuery] = React.useState('');
    const [suggestions, setSuggestions] = React.useState<string[]>([]);
    const [quickResults, setQuickResults] = React.useState<
        SuggestionApiResponse['quickResults']
    >({});
    const [history, setHistory] = React.useState<string[]>([]);
    const [isDebouncing, setIsDebouncing] = React.useState(false);
    const historyRef = React.useRef<string[]>([]);

    const http = useHttp();
    const httpRef = React.useRef(http);
    React.useEffect(() => {
        httpRef.current = http;
    }, [http]);

    const isLoading = http.processing || isDebouncing;

    React.useEffect(() => {
        if (typeof window !== 'undefined') {
            try {
                const stored = localStorage.getItem(STORAGE_KEY);
                const parsed = stored ? (JSON.parse(stored) as string[]) : [];

                if (Array.isArray(parsed)) {
                    historyRef.current = parsed;
                    setHistory(parsed);
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

        const updated = [
            trimmed,
            ...historyRef.current.filter((item) => item !== trimmed),
        ].slice(0, MAX_HISTORY);

        historyRef.current = updated;

        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
        } catch (e) {
            console.error('Failed to save search history', e);
        }

        setHistory(updated);
    }, []);

    const deleteHistoryItem = React.useCallback(
        (itemToDelete: string, e: React.MouseEvent) => {
            e.stopPropagation();
            e.preventDefault();

            const updated = historyRef.current.filter(
                (item) => item !== itemToDelete,
            );

            historyRef.current = updated;

            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
            } catch (err) {
                console.error('Failed to delete search history item', err);
            }

            setHistory(updated);
        },
        [],
    );

    const handleQueryChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setQuery(value);

        if (!value) {
            setSuggestions([]);
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
                `${SEARCH_SUGGESTIONS_ENDPOINT}?q=${encodeURIComponent(query.trim())}`,
                {
                    onSuccess: (data: unknown) => {
                        const payload = (data || {}) as SuggestionApiResponse;
                        setSuggestions(payload.suggestions || []);
                        setQuickResults(payload.quickResults || {});
                    },
                    onError: () => {
                        setSuggestions([]);
                        setQuickResults({});
                    },
                },
            );
        }, 180);

        return () => {
            clearTimeout(timeoutId);
            httpRef.current.cancel();
        };
    }, [query]);

    const executeDirectSearch = React.useCallback(
        (targetQuery: string) => {
            onOpenChange(false);
            const actualQuery = targetQuery.trim();
            saveToHistory(actualQuery);
            router.visit(`/search?q=${encodeURIComponent(actualQuery)}`, {
                headers: { 'X-Search-Clicked': '1' },
            });
        },
        [onOpenChange, saveToHistory],
    );

    const navigateToUrl = React.useCallback(
        (url: string, historyLabel: string) => {
            onOpenChange(false);
            saveToHistory(historyLabel);
            router.visit(url);
        },
        [onOpenChange, saveToHistory],
    );

    const allQuickItems = React.useMemo(() => {
        const books = quickResults?.books || [];
        const skripsi = quickResults?.skripsi || [];
        const posts = quickResults?.posts || [];

        return [...books, ...skripsi, ...posts];
    }, [quickResults]);

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter') {
            e.preventDefault();

            if (query.trim() !== '') {
                executeDirectSearch(query);
            }
        } else if (e.key === 'Escape') {
            onOpenChange(false);
        }
    };

    React.useEffect(() => {
        if (!open) {
            setQuery('');
            setSuggestions([]);
            setQuickResults({});
            setIsDebouncing(false);
        }
    }, [open]);

    const hasQuickResults = allQuickItems.length > 0;
    const hasSuggestions = suggestions.length > 0;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogHeader className="sr-only">
                <DialogTitle>Pencarian Koleksi</DialogTitle>
                <DialogDescription>
                    Ketik kata kunci untuk mencari buku, skripsi, atau artikel.
                </DialogDescription>
            </DialogHeader>
            <DialogContent
                className="top-[15%]! w-full translate-y-0! gap-0! overflow-hidden rounded-2xl! border bg-popover p-0! shadow-2xl sm:top-[18%]! sm:max-w-2xl!"
                overlayClassName="bg-black/60 backdrop-blur-xs"
                showCloseButton={false}
            >
                <div
                    className={`flex items-center px-4 ${query.length > 0 || history.length > 0 ? 'border-b' : ''}`}
                >
                    <Search className="mr-3 size-4 shrink-0 text-muted-foreground" />
                    <input
                        className="h-13 w-full border-none bg-transparent px-0 text-sm outline-none placeholder:text-muted-foreground focus:ring-0 focus:outline-none"
                        placeholder="Ketik judul buku, nama dosen, topik skripsi, atau artikel..."
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
                    {query.length > 0 || history.length > 0 ? (
                        <motion.div
                            key="global-search-container"
                            initial={{ height: 0, opacity: 0 }}
                            animate={{ height: 'auto', opacity: 1 }}
                            exit={{ height: 0, opacity: 0 }}
                            transition={{ duration: 0.2 }}
                            className="flex max-h-[26rem] w-full flex-col overflow-hidden"
                        >
                            {query.length > 0 ? (
                                <div className="no-scrollbar overflow-y-auto p-2">
                                    {/* 1. Quick Direct Results */}
                                    {hasQuickResults ? (
                                        <div className="space-y-3 p-1">
                                            {quickResults?.books &&
                                            quickResults.books.length > 0 ? (
                                                <div>
                                                    <p className="px-2 py-1 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                                        Buku
                                                    </p>
                                                    <div className="space-y-0.5">
                                                        {quickResults.books.map(
                                                            (book) => (
                                                                <button
                                                                    key={`book-${book.id}`}
                                                                    type="button"
                                                                    onClick={() =>
                                                                        navigateToUrl(
                                                                            book.url,
                                                                            book.title,
                                                                        )
                                                                    }
                                                                    className="flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-left transition-colors hover:bg-accent"
                                                                >
                                                                    <BookOpen className="size-4 shrink-0 text-primary" />
                                                                    <div className="min-w-0 flex-1">
                                                                        <p className="truncate text-sm font-medium text-foreground">
                                                                            {
                                                                                book.title
                                                                            }
                                                                        </p>
                                                                        <p className="truncate text-xs text-muted-foreground">
                                                                            {
                                                                                book.subtitle
                                                                            }
                                                                        </p>
                                                                    </div>
                                                                </button>
                                                            ),
                                                        )}
                                                    </div>
                                                </div>
                                            ) : null}

                                            {quickResults?.skripsi &&
                                            quickResults.skripsi.length > 0 ? (
                                                <div>
                                                    <p className="px-2 py-1 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                                        Karya Ilmiah
                                                    </p>
                                                    <div className="space-y-0.5">
                                                        {quickResults.skripsi.map(
                                                            (item) => (
                                                                <button
                                                                    key={`skripsi-${item.id}`}
                                                                    type="button"
                                                                    onClick={() =>
                                                                        navigateToUrl(
                                                                            item.url,
                                                                            item.title,
                                                                        )
                                                                    }
                                                                    className="flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-left transition-colors hover:bg-accent"
                                                                >
                                                                    <GraduationCap className="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                                                    <div className="min-w-0 flex-1">
                                                                        <p className="truncate text-sm font-medium text-foreground">
                                                                            {
                                                                                item.title
                                                                            }
                                                                        </p>
                                                                        <p className="truncate text-xs text-muted-foreground">
                                                                            {
                                                                                item.subtitle
                                                                            }
                                                                        </p>
                                                                    </div>
                                                                </button>
                                                            ),
                                                        )}
                                                    </div>
                                                </div>
                                            ) : null}

                                            {quickResults?.posts &&
                                            quickResults.posts.length > 0 ? (
                                                <div>
                                                    <p className="px-2 py-1 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                                        Artikel
                                                    </p>
                                                    <div className="space-y-0.5">
                                                        {quickResults.posts.map(
                                                            (post) => (
                                                                <button
                                                                    key={`post-${post.id}`}
                                                                    type="button"
                                                                    onClick={() =>
                                                                        navigateToUrl(
                                                                            post.url,
                                                                            post.title,
                                                                        )
                                                                    }
                                                                    className="flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-left transition-colors hover:bg-accent"
                                                                >
                                                                    <FileText className="size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                                                                    <div className="min-w-0 flex-1">
                                                                        <p className="truncate text-sm font-medium text-foreground">
                                                                            {
                                                                                post.title
                                                                            }
                                                                        </p>
                                                                    </div>
                                                                </button>
                                                            ),
                                                        )}
                                                    </div>
                                                </div>
                                            ) : null}
                                        </div>
                                    ) : null}

                                    {/* 2. Text Suggestions */}
                                    {hasSuggestions ? (
                                        <div className="mt-2 border-t border-border/50 pt-2">
                                            <p className="px-2 py-1 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                                Pencarian Terkait
                                            </p>
                                            <div className="space-y-0.5">
                                                {suggestions.map((item) => (
                                                    <button
                                                        key={item}
                                                        type="button"
                                                        onClick={() =>
                                                            executeDirectSearch(
                                                                item,
                                                            )
                                                        }
                                                        className="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left text-sm text-foreground transition-colors hover:bg-accent"
                                                    >
                                                        <Search className="size-3.5 shrink-0 text-muted-foreground" />
                                                        <span className="min-w-0 flex-1 truncate">
                                                            {getHighlightedText(
                                                                item,
                                                                query,
                                                            )}
                                                        </span>
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    ) : null}

                                    {/* 3. Action Footer */}
                                    <div className="mt-2 border-t border-dashed border-border p-1 pt-2">
                                        <button
                                            type="button"
                                            onClick={() =>
                                                executeDirectSearch(query)
                                            }
                                            className="flex w-full items-center justify-between gap-2 rounded-lg bg-primary/10 px-3 py-2 text-left text-sm font-semibold text-primary transition-colors hover:bg-primary/20"
                                        >
                                            <span className="flex items-center gap-2 truncate">
                                                <Search className="size-4 shrink-0" />
                                                Lihat semua hasil untuk &ldquo;
                                                {query}&rdquo;
                                            </span>
                                            <kbd className="hidden shrink-0 rounded bg-background px-1.5 py-0.5 text-xs text-muted-foreground sm:inline-block">
                                                ↵ Enter
                                            </kbd>
                                        </button>
                                    </div>
                                </div>
                            ) : (
                                /* History Screen */
                                <div className="p-2">
                                    <p className="px-2 py-1 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                        Riwayat Pencarian
                                    </p>
                                    <div className="space-y-0.5">
                                        {history.map((item) => (
                                            <div
                                                key={item}
                                                className="group flex items-center justify-between rounded-lg px-2.5 py-1.5 transition-colors hover:bg-accent"
                                            >
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        executeDirectSearch(
                                                            item,
                                                        )
                                                    }
                                                    className="flex min-w-0 flex-1 items-center gap-2 text-left text-sm text-foreground"
                                                >
                                                    <History className="size-3.5 text-muted-foreground" />
                                                    <span className="truncate">
                                                        {item}
                                                    </span>
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={(e) =>
                                                        deleteHistoryItem(
                                                            item,
                                                            e,
                                                        )
                                                    }
                                                    className="opacity-0 transition-opacity group-hover:opacity-100"
                                                >
                                                    <X className="size-3.5 text-muted-foreground hover:text-foreground" />
                                                </button>
                                            </div>
                                        ))}
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
