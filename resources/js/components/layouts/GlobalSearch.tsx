import { router } from '@inertiajs/react';
import { BookOpen, Search } from 'lucide-react';
import * as React from 'react';
import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { Skeleton } from '@/components/ui/skeleton';

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

    React.useEffect(() => {
        const timeoutId = setTimeout(async () => {
            if (!query) {
                setResults([]);
                setIsLoading(false);

                return;
            }

            setIsLoading(true);

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
                    onValueChange={setQuery}
                />
                <CommandList>
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
                    {results.length > 0 && (
                        <CommandGroup heading="Buku">
                            {results.map((book) => (
                                <CommandItem
                                    key={book.id}
                                    value={book.title}
                                    onSelect={() => onSelect(book.slug)}
                                    className="flex items-center gap-3 py-3"
                                >
                                    <div className="size-10 shrink-0 overflow-hidden rounded-md border">
                                        <img
                                            src={book.coverImageUrl}
                                            alt=""
                                            className="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div className="flex flex-1 flex-col">
                                        <span className="line-clamp-1 font-medium">
                                            {book.title}
                                        </span>
                                        <span className="line-clamp-1 text-xs text-muted-foreground">
                                            {book.authors.join(', ')}
                                        </span>
                                    </div>
                                    <BookOpen className="ml-auto size-4 text-muted-foreground" />
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    )}
                </CommandList>
            </CommandDialog>
        </>
    );
}
