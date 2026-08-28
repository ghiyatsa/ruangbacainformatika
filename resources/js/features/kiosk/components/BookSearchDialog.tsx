import { SearchIcon } from 'lucide-react';
import { useDeferredValue, useEffect, useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Spinner } from '@/components/ui/spinner';
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

    const availableSearchResults = searchResults.filter(
        (book) =>
            !selectedBooks.some((selectedBook) => selectedBook.id === book.id),
    );

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogContent className="max-w-4xl min-w-xl">
                <DialogHeader>
                    <DialogTitle>Cari Buku</DialogTitle>
                    <DialogDescription>
                        {bookSearchMode === 'borrow'
                            ? 'Cari berdasarkan judul, penulis, kategori, atau ISBN.'
                            : 'Pilih buku dari daftar pinjaman aktif anggota ini.'}
                    </DialogDescription>
                </DialogHeader>

                <div className="flex flex-col gap-3">
                    <InputGroup>
                        <InputGroupInput
                            id="book-search"
                            placeholder={
                                bookSearchMode === 'borrow'
                                    ? 'Ketik judul, pengarang, atau topik buku...'
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
                            autoFocus
                            aria-invalid={hasError}
                            className="h-full text-base"
                        />
                        <InputGroupAddon>
                            <SearchIcon />
                        </InputGroupAddon>
                    </InputGroup>

                    {bookSearchMode === 'return' ? (
                        <p className="text-xs text-muted-foreground">
                            Daftar pinjaman aktif ditampilkan otomatis.
                        </p>
                    ) : null}

                    {searchError ? (
                        <p className="text-sm text-destructive">
                            {searchError}
                        </p>
                    ) : null}

                    <ScrollArea className="h-80">
                        <div className="divide-y divide-border/50">
                            {bookSearchMode === 'borrow' &&
                            searchQuery.trim() === '' ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    Mulai ketik untuk mencari buku.
                                </p>
                            ) : isSearching ? (
                                <div className="flex items-center justify-center gap-2 py-8 text-sm text-muted-foreground">
                                    <Spinner />
                                    Mencari buku...
                                </div>
                            ) : availableSearchResults.length > 0 ? (
                                availableSearchResults.map((book) => (
                                    <button
                                        key={book.id}
                                        type="button"
                                        className="flex w-full items-center justify-between gap-4 px-3 py-3 text-left transition-colors hover:bg-accent/40"
                                        onClick={() => onSelectBook(book)}
                                    >
                                        <div className="min-w-0 flex-1 space-y-1">
                                            <p className="line-clamp-1 text-sm font-semibold text-foreground">
                                                {book.title}
                                            </p>
                                            <p className="line-clamp-1 text-xs text-muted-foreground">
                                                {book.authors?.join(', ') ||
                                                    'Penulis belum tersedia'}
                                                {' • '}
                                                {book.isbn
                                                    ? `ISBN ${book.isbn}`
                                                    : book.issn
                                                      ? `ISSN ${book.issn}`
                                                      : 'Tanpa ISBN/ISSN'}
                                            </p>
                                        </div>

                                        <span className="shrink-0 rounded-md bg-muted px-2 py-1 text-xs font-medium text-muted-foreground">
                                            {bookSearchMode === 'borrow'
                                                ? `${book.availableItemsCount} tersedia`
                                                : 'Pinjaman aktif'}
                                        </span>
                                    </button>
                                ))
                            ) : (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    Tidak ada buku yang cocok.
                                </p>
                            )}
                        </div>
                    </ScrollArea>
                </div>
            </DialogContent>
        </Dialog>
    );
}
