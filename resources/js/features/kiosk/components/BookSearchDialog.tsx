import { BookOpen, Loader2, Search } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useDeferredValue, useEffect, useMemo, useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { SearchFooter } from '@/components/layout/global-search/SearchFooter';
import { SearchResultsSkeleton } from '@/components/layout/global-search/SearchResultsSkeleton';
import type {
    KioskBookSearchMode,
    KioskBookSearchResult,
} from '@/features/kiosk/types';

interface BookSearchDialogProps {
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
    bookSearchUrl: string;
    bookSearchMode: KioskBookSearchMode;
    memberIdentifier: string;
    onSelectBook: (book: KioskBookSearchResult) => void;
    selectedBooks: KioskBookSearchResult[];
    maxInputs: number;
    hasError?: boolean;
}

export function BookSearchDialog({
    isOpen,
    onOpenChange,
    bookSearchUrl,
    bookSearchMode,
    memberIdentifier,
    onSelectBook,
    selectedBooks,
    hasError = false,
}: BookSearchDialogProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<KioskBookSearchResult[]>(
        [],
    );
    const [isSearching, setIsSearching] = useState(false);
    const [searchError, setSearchError] = useState<string | null>(null);
    const [selectedIndex, setSelectedIndex] = useState<number>(0);

    const deferredSearchQuery = useDeferredValue(searchQuery.trim());
    const memberIdentifierTrimmed = memberIdentifier.trim();
    const requiresMemberBeforeSearch = bookSearchMode === 'return';

    const canSearchBooks =
        bookSearchMode === 'return'
            ? isOpen && memberIdentifierTrimmed !== ''
            : deferredSearchQuery.length > 0;

    const handleOpenChange = (open: boolean) => {
        onOpenChange(open);

        if (!open) {
            setSearchQuery('');
            setSearchResults([]);
            setIsSearching(false);
            setSearchError(null);
            setSelectedIndex(0);
        }
    };

    // Fetch search results
    useEffect(() => {
        if (!canSearchBooks) {
            const resetSearchTimeout = window.setTimeout(() => {
                setSearchResults([]);
                setIsSearching(false);
                setSearchError(null);
            }, 0);

            return () => window.clearTimeout(resetSearchTimeout);
        }

        const abortController = new AbortController();
        const searchUrl = new URL(bookSearchUrl, window.location.origin);
        searchUrl.searchParams.set('q', deferredSearchQuery);
        searchUrl.searchParams.set('mode', bookSearchMode);

        if (requiresMemberBeforeSearch) {
            searchUrl.searchParams.set(
                'member_identifier',
                memberIdentifierTrimmed,
            );
        }

        const startSearchingTimeout = window.setTimeout(() => {
            setIsSearching(true);
            setSearchError(null);
        }, 0);

        void fetch(searchUrl.toString(), {
            signal: abortController.signal,
        })
            .then(async (response) => {
                if (!response.ok) {
                    throw new Error('Gagal memuat hasil pencarian buku.');
                }

                const payload = (await response.json()) as {
                    books?: KioskBookSearchResult[];
                };

                setSearchResults(payload.books ?? []);
            })
            .catch((error: unknown) => {
                if (
                    error instanceof DOMException &&
                    error.name === 'AbortError'
                ) {
                    return;
                }

                setSearchResults([]);
                setSearchError('Pencarian buku belum bisa digunakan saat ini.');
            })
            .finally(() => {
                if (!abortController.signal.aborted) {
                    setIsSearching(false);
                }
            });

        return () => {
            window.clearTimeout(startSearchingTimeout);
            abortController.abort();
        };
    }, [
        bookSearchMode,
        bookSearchUrl,
        canSearchBooks,
        deferredSearchQuery,
        memberIdentifierTrimmed,
        requiresMemberBeforeSearch,
    ]);

    const availableSearchResults = useMemo(
        () =>
            searchResults.filter(
                (book) =>
                    !selectedBooks.some(
                        (selectedBook) => selectedBook.id === book.id,
                    ),
            ),
        [searchResults, selectedBooks],
    );

    useEffect(() => {
        setSelectedIndex(0);
    }, [availableSearchResults]);

    const handleSelectBook = (book: KioskBookSearchResult) => {
        onSelectBook(book);
        handleOpenChange(false);
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (availableSearchResults.length > 0) {
                setSelectedIndex((prev) =>
                    prev < availableSearchResults.length - 1 ? prev + 1 : 0,
                );
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (availableSearchResults.length > 0) {
                setSelectedIndex((prev) =>
                    prev > 0 ? prev - 1 : availableSearchResults.length - 1,
                );
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (
                availableSearchResults.length > 0 &&
                availableSearchResults[selectedIndex]
            ) {
                handleSelectBook(availableSearchResults[selectedIndex]);
            }
        } else if (e.key === 'Escape') {
            handleOpenChange(false);
        }
    };

    const isInputActive =
        bookSearchMode === 'return' || searchQuery.trim().length > 0;
    const hasLiveResults = availableSearchResults.length > 0;

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogHeader className="sr-only">
                <DialogTitle>Cari Buku</DialogTitle>
                <DialogDescription>
                    {bookSearchMode === 'borrow'
                        ? 'Cari berdasarkan judul, penulis, kategori, atau ISBN.'
                        : 'Pilih buku dari daftar pinjaman aktif anggota ini.'}
                </DialogDescription>
            </DialogHeader>
            <DialogContent
                className="top-[15%] w-full translate-y-0 gap-0 overflow-hidden rounded-2xl border bg-popover p-0 shadow-2xl sm:top-[18%] sm:max-w-2xl"
                overlayClassName="bg-black/60 backdrop-blur-xs"
                showCloseButton={false}
            >
                <div
                    className={`flex items-center px-4 ${isInputActive ? 'border-b' : ''}`}
                >
                    <Search className="mr-3 size-4 shrink-0 text-muted-foreground" />
                    <input
                        id="book-search"
                        className="h-13 w-full border-none bg-transparent px-0 text-sm outline-none placeholder:text-muted-foreground focus:ring-0 focus:outline-none"
                        placeholder={
                            bookSearchMode === 'borrow'
                                ? 'Ketik judul, pengarang, topik buku, ISBN...'
                                : 'Filter buku pinjaman aktif...'
                        }
                        autoComplete="off"
                        autoCorrect="off"
                        spellCheck={false}
                        value={searchQuery}
                        onChange={(e) => {
                            const nextQuery = e.target.value;
                            setSearchQuery(nextQuery);
                            setSearchError(null);

                            if (bookSearchMode === 'borrow') {
                                if (nextQuery.trim() === '') {
                                    setSearchResults([]);
                                    setIsSearching(false);
                                } else {
                                    setIsSearching(true);
                                }
                            } else {
                                if (memberIdentifierTrimmed === '') {
                                    setSearchResults([]);
                                    setIsSearching(false);
                                } else {
                                    setIsSearching(true);
                                }
                            }
                        }}
                        onKeyDown={handleKeyDown}
                        autoFocus
                        aria-invalid={hasError}
                    />
                    {isSearching ? (
                        <Loader2 className="ml-2 size-4 animate-spin text-muted-foreground" />
                    ) : null}
                </div>

                <AnimatePresence initial={false}>
                    {isInputActive ? (
                        <motion.div
                            key="kiosk-book-search-container"
                            initial={{ height: 0, opacity: 0 }}
                            animate={{ height: 'auto', opacity: 1 }}
                            exit={{ height: 0, opacity: 0 }}
                            transition={{ duration: 0.2 }}
                            className="flex max-h-[28rem] w-full flex-col overflow-hidden"
                        >
                            <div className="no-scrollbar flex-1 overflow-y-auto p-2">
                                {searchError ? (
                                    <p className="px-3 py-6 text-center text-sm text-destructive">
                                        {searchError}
                                    </p>
                                ) : isSearching && !hasLiveResults ? (
                                    <SearchResultsSkeleton />
                                ) : !isSearching && !hasLiveResults ? (
                                    <div className="py-12 text-center text-sm text-muted-foreground">
                                        <Search className="mx-auto mb-2 size-8 opacity-30" />
                                        {searchQuery.trim() ? (
                                            <>
                                                Tidak ditemukan hasil untuk &ldquo;
                                                <span className="font-medium text-foreground">
                                                    {searchQuery}
                                                </span>
                                                &rdquo;
                                            </>
                                        ) : (
                                            'Tidak ada buku yang dapat dipilih.'
                                        )}
                                    </div>
                                ) : (
                                    <div className="space-y-4 p-1">
                                        <div>
                                            <p className="px-2 py-1 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                                {bookSearchMode === 'borrow'
                                                    ? 'Katalog Buku'
                                                    : 'Pinjaman Aktif'}
                                            </p>
                                            <div className="space-y-0.5">
                                                {availableSearchResults.map(
                                                    (book, index) => {
                                                        const isSelected =
                                                            selectedIndex === index;
                                                        const authorText =
                                                            book.authors?.join(
                                                                ', ',
                                                            ) ||
                                                            'Penulis tidak tersedia';
                                                        const metaText =
                                                            book.isbn
                                                                ? `ISBN ${book.isbn}`
                                                                : book.issn
                                                                  ? `ISSN ${book.issn}`
                                                                  : null;
                                                        const subtitle = metaText
                                                            ? `${authorText} • ${metaText}`
                                                            : authorText;

                                                        return (
                                                            <div
                                                                key={book.id}
                                                                className={`group flex items-center justify-between rounded-lg px-2.5 py-2 transition-colors ${
                                                                    isSelected
                                                                        ? 'bg-accent text-accent-foreground ring-1 ring-border'
                                                                        : 'hover:bg-accent/70'
                                                                }`}
                                                            >
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        handleSelectBook(
                                                                            book,
                                                                        )
                                                                    }
                                                                    className="flex min-w-0 flex-1 items-center gap-3 text-left"
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
                                                                                subtitle
                                                                            }
                                                                        </p>
                                                                    </div>
                                                                </button>

                                                                <span className="shrink-0 rounded-md bg-muted px-2 py-0.5 text-[11px] font-medium text-muted-foreground">
                                                                    {bookSearchMode ===
                                                                    'borrow'
                                                                        ? `${book.availableItemsCount} tersedia`
                                                                        : 'Pinjaman'}
                                                                </span>
                                                            </div>
                                                        );
                                                    },
                                                )}
                                            </div>
                                        </div>
                                    </div>
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
