import { router } from '@inertiajs/react';
import { BookOpen, GraduationCap, Search } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import * as React from 'react';
import AnimatedList from '@/components/common/AnimatedList';
import { Badge } from '@/components/ui/badge';
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
    coverImageUrl?: string;
    authors?: string[];
    authorName?: string;
    studentId?: string;
}

interface SearchResponse {
    books: SearchResult[];
    skripsis: SearchResult[];
}

export function GlobalSearch() {
    const [open, setOpen] = React.useState(false);
    const [query, setQuery] = React.useState('');
    const [results, setResults] = React.useState<SearchResponse>({ books: [], skripsis: [] });
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
            setResults({ books: [], skripsis: [] });
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
                setResults(data || { books: [], skripsis: [] });
            } catch (error) {
                console.error('Search failed:', error);
            } finally {
                setIsLoading(false);
            }
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [query]);

    const onSelect = React.useCallback((item: SearchResult, type: 'book' | 'skripsi') => {
        setOpen(false);

        if (type === 'book') {
            router.visit(`/books/${item.slug}`);
        } else {
            router.visit(`/skripsi/${item.studentId}`);
        }
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
                                {(isLoading || (results.books.length === 0 && results.skripsis.length === 0)) && (
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

                                {(results.books.length > 0 || results.skripsis.length > 0) && !isLoading && (
                                    <AnimatedList<SearchResult & { itemType: 'book' | 'skripsi' }>
                                        items={[
                                            ...results.books.map((b) => ({ ...b, itemType: 'book' as const })),
                                            ...results.skripsis.map((s) => ({ ...s, itemType: 'skripsi' as const })),
                                        ]}
                                        onItemSelect={(item) => onSelect(item, item.itemType)}
                                        showGradients
                                        renderItem={(item, index, isSelected) => (
                                            <div
                                                className={cn(
                                                    'flex items-center gap-3 rounded-lg px-3 py-3 transition-colors',
                                                    isSelected
                                                        ? 'bg-accent text-accent-foreground'
                                                        : 'hover:bg-accent/50',
                                                )}
                                            >
                                                {item.itemType === 'book' ? (
                                                    <>
                                                        <div className="aspect-2/3 w-9 shrink-0 overflow-hidden rounded-sm border bg-muted shadow-sm">
                                                            <img
                                                                src={item.coverImageUrl}
                                                                alt=""
                                                                className="h-full w-full object-cover"
                                                            />
                                                        </div>
                                                        <div className="flex flex-1 flex-col gap-0.5">
                                                            <div className="flex items-center gap-2">
                                                                <span className="line-clamp-1 font-semibold tracking-tight">
                                                                    {item.title}
                                                                </span>
                                                                <Badge variant="outline" className="h-4 px-1 text-[9px] uppercase">Buku</Badge>
                                                            </div>
                                                            <span className="line-clamp-1 text-xs text-muted-foreground">
                                                                {item.authors?.join(', ')}
                                                            </span>
                                                        </div>
                                                        <BookOpen className="ml-auto size-4 text-muted-foreground" />
                                                    </>
                                                ) : (
                                                    <>
                                                        <div className="flex size-9 shrink-0 items-center justify-center rounded-sm border bg-muted shadow-sm">
                                                            <GraduationCap className="size-5 text-muted-foreground" />
                                                        </div>
                                                        <div className="flex flex-1 flex-col gap-0.5">
                                                            <div className="flex items-center gap-2">
                                                                <span className="line-clamp-1 font-semibold tracking-tight">
                                                                    {item.title}
                                                                </span>
                                                                <Badge variant="outline" className="h-4 px-1 text-[9px] uppercase">Skripsi</Badge>
                                                            </div>
                                                            <span className="line-clamp-1 text-xs text-muted-foreground">
                                                                {item.authorName} • {item.studentId}
                                                            </span>
                                                        </div>
                                                        <Search className="ml-auto size-4 text-muted-foreground" />
                                                    </>
                                                )}
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
