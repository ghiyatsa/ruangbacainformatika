import { router } from '@inertiajs/react';
import { AnimatePresence, motion } from 'motion/react';
import * as React from 'react';
import AnimatedList from '@/components/common/AnimatedList';
import { GlobalSearchResultRow } from './GlobalSearchResultRow';
import { GlobalSearchTrigger } from './GlobalSearchTrigger';
import {
    CommandDialog,
    CommandEmpty,
    CommandInput,
    CommandList,
} from '@/components/ui/command';
import { Skeleton } from '@/components/ui/skeleton';
import booksRoute from '@/routes/books';
import internshipReportsRoute from '@/routes/internship-reports';
import skripsiRoute from '@/routes/skripsi';
import thesisRoute from '@/routes/thesis';
import type {
    SearchItemType,
    SearchListItem,
    SearchResponse,
    SearchResult,
} from './types';

const EMPTY_RESULTS: SearchResponse = {
    books: [],
    skripsis: [],
    internshipReports: [],
    theses: [],
};

const SEARCH_ENDPOINT = '/search';

function GlobalSearchResultSkeleton() {
    return (
        <div className="flex items-center gap-3 rounded-lg px-3 py-3">
            <Skeleton className="h-[3.375rem] w-9 shrink-0 rounded-sm" />

            <div className="flex flex-1 flex-col gap-1">
                <div className="flex items-center gap-2">
                    <Skeleton className="h-4 w-2/3" />
                    <Skeleton className="h-4 w-12 rounded-full" />
                </div>
                <Skeleton className="h-3 w-1/2" />
            </div>

            <Skeleton className="size-4 shrink-0 rounded-full" />
        </div>
    );
}

function flattenSearchResults(results: SearchResponse): SearchListItem[] {
    return [
        ...results.books.map((book) => ({
            ...book,
            itemType: 'book' as const,
        })),
        ...results.skripsis.map((skripsi) => ({
            ...skripsi,
            itemType: 'skripsi' as const,
        })),
        ...results.internshipReports.map((report) => ({
            ...report,
            itemType: 'internship_report' as const,
        })),
        ...results.theses.map((thesis) => ({
            ...thesis,
            itemType: 'thesis' as const,
        })),
    ];
}

function visitSearchResult(item: SearchResult, type: SearchItemType): void {
    if (type === 'book') {
        router.visit(booksRoute.show.url(item.slug));

        return;
    }

    if (type === 'skripsi') {
        router.visit(skripsiRoute.show.url(item.studentId ?? ''));

        return;
    }

    if (type === 'thesis') {
        router.visit(thesisRoute.show.url(item.studentId ?? ''));

        return;
    }

    router.visit(internshipReportsRoute.show.url(item.studentId ?? ''));
}

export function GlobalSearch() {
    const [open, setOpen] = React.useState(false);
    const [query, setQuery] = React.useState('');
    const [results, setResults] = React.useState<SearchResponse>(EMPTY_RESULTS);
    const [isLoading, setIsLoading] = React.useState(false);

    const items = React.useMemo(() => flattenSearchResults(results), [results]);
    const hasResults = items.length > 0;

    React.useEffect(() => {
        const down = (event: KeyboardEvent) => {
            if (event.key === 'k' && (event.metaKey || event.ctrlKey)) {
                event.preventDefault();
                setOpen((currentOpen) => !currentOpen);
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

    const handleQueryChange = (value: string) => {
        setQuery(value);

        if (!value) {
            setResults(EMPTY_RESULTS);
            setIsLoading(false);

            return;
        }

        setIsLoading(true);
    };

    React.useEffect(() => {
        if (!query) {
            return;
        }

        const timeoutId = setTimeout(async () => {
            try {
                const response = await fetch(
                    `${SEARCH_ENDPOINT}?q=${encodeURIComponent(query)}`,
                );
                const data = (await response.json()) as SearchResponse;
                setResults(data || EMPTY_RESULTS);
            } catch (error) {
                console.error('Search failed:', error);
                setResults(EMPTY_RESULTS);
            } finally {
                setIsLoading(false);
            }
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [query]);

    const onSelect = React.useCallback(
        (item: SearchResult, type: SearchItemType) => {
            setOpen(false);
            visitSearchResult(item, type);
        },
        [],
    );

    return (
        <>
            <GlobalSearchTrigger onClick={() => setOpen(true)} />

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
                    {query.length > 0 ? (
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
                                {(isLoading || !hasResults) && (
                                    <CommandEmpty>
                                        {isLoading ? (
                                            <div className="space-y-1 p-2">
                                                {Array.from({ length: 4 }).map(
                                                    (_, index) => (
                                                        <GlobalSearchResultSkeleton
                                                            key={index}
                                                        />
                                                    ),
                                                )}
                                            </div>
                                        ) : (
                                            'Tidak ada hasil ditemukan.'
                                        )}
                                    </CommandEmpty>
                                )}

                                {hasResults && !isLoading ? (
                                    <AnimatedList<SearchListItem>
                                        items={items}
                                        onItemSelect={(item) =>
                                            onSelect(item, item.itemType)
                                        }
                                        showGradients
                                        renderItem={(
                                            item,
                                            _index,
                                            isSelected,
                                        ) => (
                                            <GlobalSearchResultRow
                                                item={item}
                                                isSelected={isSelected}
                                            />
                                        )}
                                    />
                                ) : null}
                            </CommandList>
                        </motion.div>
                    ) : null}
                </AnimatePresence>
            </CommandDialog>
        </>
    );
}
