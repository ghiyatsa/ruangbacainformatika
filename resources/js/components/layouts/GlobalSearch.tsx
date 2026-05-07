import { router } from '@inertiajs/react';
import { BookOpen, Search } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import * as React from 'react';
import AnimatedList from '@/components/common/AnimatedList';
import {
    CommandDialog,
    CommandEmpty,
    CommandInput,
    CommandList,
} from '@/components/ui/command';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';

interface SearchResult {
    id: number;
    title: string;
    slug: string;
    coverImageUrl: string;
    authors: string[];
}

export function GlobalSearch() {
    const [open, setOpen] = React.useState(false);
    const [query, setQuery] = React.useState('');
    const [results, setResults] = React.useState<SearchResult[]>([]);
    const [isLoading, setIsLoading] = React.useState(false);

    React.useEffect(() => {
        const down = (e: KeyboardEvent) => {
            if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                setOpen((open) => !open);
            }
        };

        const handleOpen = () => setOpen(true);

        document.addEventListener('keydown', down);
        window.addEventListener('open-global-search', handleOpen);

        return () => {
            document.removeEventListener('keydown', down);
            window.removeEventListener('open-global-search', handleOpen);
        };
    }, []);

    const handleQueryChange = (v: string) => {
        setQuery(v);

        if (!v) {
            setResults([]);
            setIsLoading(false);
        } else {
            setIsLoading(true);
        }
    };

    React.useEffect(() => {
        if (!query) {
            return;
        }

        const timeoutId = setTimeout(async () => {
            try {
                const response = await fetch(
                    `/search?q=${encodeURIComponent(query)}`,
                );
                const data = await response.json();
                setResults(data.data || []);
            } catch (error) {
                console.error('Search failed:', error);
            } finally {
                setIsLoading(false);
            }
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [query]);

    const onSelect = React.useCallback((slug: string) => {
        setOpen(false);
        router.visit(`/books/${slug}`);
    }, []);

    return (
        <>
            <button
                onClick={() => setOpen(true)}
                className="relative flex h-9 w-full items-center justify-start gap-2 rounded-xl border border-accent/50 bg-muted/50 px-3 text-sm text-muted-foreground transition-colors hover:bg-muted md:w-64 lg:w-80"
            >
                <Search className="size-4" />
                <span>Cari buku...</span>
                <kbd className="pointer-events-none absolute top-1/2 right-2 hidden -translate-y-1/2 items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium opacity-100 sm:flex">
                    <span className="text-xs">⌘</span>K
                </kbd>
            </button>
            <CommandDialog
                open={open}
                onOpenChange={setOpen}
                className="top-24 p-2"
            >
                <CommandInput
                    placeholder="Ketik judul buku atau penulis..."
                    value={query}
                    onValueChange={handleQueryChange}
                />
                <AnimatePresence initial={false}>
                    {query.length > 0 && (
                        <motion.div
                            key="search-content"
                            initial={{ height: 0, opacity: 0 }}
                            animate={{ height: 'auto', opacity: 1 }}
                            exit={{ height: 0, opacity: 0 }}
                            transition={{
                                type: 'spring',
                                duration: 0.4,
                                bounce: 0,
                            }}
                            className="overflow-hidden"
                        >
                            <div className="-mx-2 h-px bg-border" />
                            <CommandList>
                                {(isLoading || results.length === 0) && (
                                    <CommandEmpty>
                                        {isLoading ? (
                                            <div className="space-y-2 p-4">
                                                <Skeleton className="h-4 w-full" />
                                                <Skeleton className="h-4 w-3/4" />
                                            </div>
                                        ) : (
                                            'Tidak ada hasil ditemukan.'
                                        )}
                                    </CommandEmpty>
                                )}

                                {results.length > 0 && !isLoading && (
                                    <AnimatedList<SearchResult>
                                        items={results}
                                        onItemSelect={(book) =>
                                            onSelect(book.slug)
                                        }
                                        showGradients
                                        renderItem={(
                                            book,
                                            index,
                                            isSelected,
                                        ) => (
                                            <div
                                                className={cn(
                                                    'flex items-center gap-3 rounded-lg px-3 py-3 transition-colors',
                                                    isSelected
                                                        ? 'bg-accent text-accent-foreground'
                                                        : 'hover:bg-accent/50',
                                                )}
                                            >
                                                <div className="aspect-2/3 w-9 shrink-0 overflow-hidden rounded-sm border bg-muted shadow-sm">
                                                    <img
                                                        src={book.coverImageUrl}
                                                        alt=""
                                                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                                                    />
                                                </div>
                                                <div className="flex flex-1 flex-col gap-0.5">
                                                    <span className="line-clamp-1 font-semibold tracking-tight">
                                                        {book.title}
                                                    </span>
                                                    <span className="line-clamp-1 text-xs text-muted-foreground">
                                                        {book.authors.join(
                                                            ', ',
                                                        )}
                                                    </span>
                                                </div>
                                                <BookOpen
                                                    className={cn(
                                                        'ml-auto size-4 transition-colors',
                                                        isSelected
                                                            ? 'text-accent-foreground'
                                                            : 'text-muted-foreground',
                                                    )}
                                                />
                                            </div>
                                        )}
                                    />
                                )}
                            </CommandList>
                        </motion.div>
                    )}
                </AnimatePresence>
            </CommandDialog>
        </>
    );
}
