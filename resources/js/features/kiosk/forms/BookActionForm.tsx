import { Form } from '@inertiajs/react';
import { useDeferredValue, useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Spinner } from '@/components/ui/spinner';
import { KioskField } from '@/features/kiosk/components/KioskField';
import type {
    KioskBookSearchMode,
    KioskBookSearchResult,
} from '@/features/kiosk/types';
import type { RouteFormDefinition } from '@/wayfinder';

export function BookActionForm({
    action,
    submitLabel,
    description,
    maxInputs = 3,
    bookSearchUrl,
    bookSearchMode = 'borrow',
    autoFocus = true,
}: {
    action: RouteFormDefinition<'post'>;
    submitLabel: string;
    description: string;
    maxInputs?: number;
    bookSearchUrl?: string;
    bookSearchMode?: KioskBookSearchMode;
    autoFocus?: boolean;
}) {
    const [memberIdentifier, setMemberIdentifier] = useState('');
    const [firstIsbn, setFirstIsbn] = useState('');
    const [isSearchDialogOpen, setIsSearchDialogOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<KioskBookSearchResult[]>(
        [],
    );
    const [selectedBooks, setSelectedBooks] = useState<KioskBookSearchResult[]>(
        [],
    );
    const [isSearching, setIsSearching] = useState(false);
    const [searchError, setSearchError] = useState<string | null>(null);

    const deferredSearchQuery = useDeferredValue(searchQuery.trim());
    const usesBookSearch = Boolean(bookSearchUrl);
    const requiresMemberBeforeSearch = bookSearchMode === 'return';
    const memberIdentifierTrimmed = memberIdentifier.trim();
    const canSearchBooks = usesBookSearch
        ? bookSearchMode === 'return'
            ? isSearchDialogOpen && memberIdentifierTrimmed !== ''
            : deferredSearchQuery.length > 0
        : false;

    const isComplete =
        memberIdentifierTrimmed !== '' &&
        (usesBookSearch ? selectedBooks.length > 0 : firstIsbn.trim() !== '');

    useEffect(() => {
        if (!usesBookSearch || !canSearchBooks) {
            return;
        }

        const abortController = new AbortController();
        const searchUrl = new URL(bookSearchUrl!, window.location.origin);
        searchUrl.searchParams.set('q', deferredSearchQuery);
        searchUrl.searchParams.set('mode', bookSearchMode);

        if (requiresMemberBeforeSearch) {
            searchUrl.searchParams.set(
                'member_identifier',
                memberIdentifierTrimmed,
            );
        }

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

        return () => abortController.abort();
    }, [
        bookSearchMode,
        bookSearchUrl,
        canSearchBooks,
        deferredSearchQuery,
        memberIdentifierTrimmed,
        requiresMemberBeforeSearch,
        usesBookSearch,
    ]);

    const availableSearchResults = searchResults.filter(
        (book) =>
            !selectedBooks.some((selectedBook) => selectedBook.id === book.id),
    );

    const resetBookSearch = () => {
        setSearchQuery('');
        setSearchResults([]);
        setIsSearching(false);
        setSearchError(null);
    };

    const handleSearchDialogChange = (open: boolean) => {
        setIsSearchDialogOpen(open);

        if (open) {
            if (bookSearchMode === 'return' && memberIdentifierTrimmed !== '') {
                setIsSearching(true);
                setSearchError(null);
            }
        } else {
            resetBookSearch();
        }
    };

    const addSelectedBook = (book: KioskBookSearchResult) => {
        setSelectedBooks((current) =>
            current.length >= maxInputs ? current : [...current, book],
        );
        handleSearchDialogChange(false);
    };

    return (
        <Form
            {...action}
            resetOnSuccess
            disableWhileProcessing
            className="flex flex-col gap-4"
            onSuccess={() => {
                setMemberIdentifier('');
                setFirstIsbn('');
                setSelectedBooks([]);
                handleSearchDialogChange(false);
            }}
        >
            {({ errors, processing }) => {
                const hasBookIdsError =
                    Boolean(errors.book_ids) ||
                    Object.keys(errors).some((key) =>
                        key.startsWith('book_ids.'),
                    );

                const hasIsbnsError =
                    Boolean(errors.isbns) ||
                    Object.keys(errors).some((key) => key.startsWith('isbns.'));

                return (
                    <>
                        {usesBookSearch ? (
                            <div className="grid gap-4">
                                <FieldGroup className="grid gap-4 rounded-2xl border border-border/70 bg-card p-4">
                                    <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_200px] lg:items-end">
                                        <KioskField
                                            label="Email atau NIM"
                                            htmlFor="book-member"
                                            error={errors.member_identifier}
                                            required
                                            className="min-w-0"
                                        >
                                            <Input
                                                id="book-member"
                                                name="member_identifier"
                                                autoFocus={autoFocus}
                                                placeholder="email@mhs.unimal.ac.id atau NIM"
                                                value={memberIdentifier}
                                                onChange={(e) => {
                                                    setMemberIdentifier(
                                                        e.target.value,
                                                    );

                                                    if (
                                                        bookSearchMode ===
                                                        'return'
                                                    ) {
                                                        setSearchResults([]);
                                                        setSearchError(null);

                                                        if (
                                                            isSearchDialogOpen &&
                                                            e.target.value.trim() !==
                                                                ''
                                                        ) {
                                                            setIsSearching(
                                                                true,
                                                            );
                                                        }
                                                    }
                                                }}
                                                aria-invalid={Boolean(
                                                    errors.member_identifier,
                                                )}
                                                className="h-12 text-base"
                                            />
                                        </KioskField>

                                        <div className="grid gap-2">
                                            <span className="text-sm font-medium text-foreground">
                                                Buku
                                            </span>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                className="h-12 w-full"
                                                disabled={
                                                    selectedBooks.length >=
                                                        maxInputs ||
                                                    (requiresMemberBeforeSearch &&
                                                        memberIdentifierTrimmed ===
                                                            '')
                                                }
                                                onClick={() =>
                                                    handleSearchDialogChange(
                                                        true,
                                                    )
                                                }
                                            >
                                                Cari Buku
                                            </Button>
                                        </div>
                                    </div>

                                    <p className="text-sm text-muted-foreground">
                                        {selectedBooks.length > 0
                                            ? `${selectedBooks.length} buku dipilih`
                                            : description}
                                    </p>

                                    {requiresMemberBeforeSearch &&
                                    memberIdentifierTrimmed === '' ? (
                                        <p className="rounded-2xl border border-dashed border-border/70 bg-muted/35 px-4 py-3 text-sm text-muted-foreground">
                                            Isi Email atau NIM untuk melihat
                                            pinjaman aktif.
                                        </p>
                                    ) : null}

                                    {hasBookIdsError ? (
                                        <p className="text-sm text-destructive">
                                            {(errors.book_ids as string) ||
                                                'Pilih buku yang valid dari hasil pencarian.'}
                                        </p>
                                    ) : null}

                                    <div className="rounded-2xl border border-border/70 bg-muted/20 p-4">
                                        <div className="flex flex-wrap items-start justify-between gap-3 border-b border-border/60 pb-3">
                                            <div className="space-y-1">
                                                <p className="text-sm font-semibold text-foreground">
                                                    Buku dipilih
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {bookSearchMode === 'borrow'
                                                        ? 'Periksa pilihan Anda.'
                                                        : 'Pilih buku yang akan dikembalikan.'}
                                                </p>
                                            </div>
                                            <div className="rounded-full border border-border/70 bg-background px-2.5 py-1 text-xs font-medium text-foreground">
                                                {selectedBooks.length} /{' '}
                                                {maxInputs}
                                            </div>
                                        </div>

                                        <div className="mt-4 space-y-2.5">
                                            {selectedBooks.map(
                                                (book, index) => (
                                                    <div
                                                        key={book.id}
                                                        className="rounded-xl border border-border/70 bg-background px-3 py-2.5"
                                                    >
                                                        <input
                                                            type="hidden"
                                                            name={`book_ids.${index}`}
                                                            value={book.id}
                                                        />
                                                        <div className="flex items-start justify-between gap-2">
                                                            <div className="min-w-0 space-y-1">
                                                                <p className="line-clamp-1 text-sm font-semibold text-foreground">
                                                                    {book.title}
                                                                </p>
                                                                <p className="line-clamp-1 text-xs text-muted-foreground">
                                                                    {book.authors?.join(
                                                                        ', ',
                                                                    ) ||
                                                                        'Penulis belum tersedia'}
                                                                    {' | '}
                                                                    {book.isbn
                                                                        ? `ISBN ${book.isbn}`
                                                                        : book.issn
                                                                          ? `ISSN ${book.issn}`
                                                                          : 'Tanpa ISBN/ISSN'}
                                                                </p>
                                                            </div>
                                                            <Button
                                                                type="button"
                                                                variant="outline"
                                                                size="sm"
                                                                className="shrink-0"
                                                                onClick={() =>
                                                                    setSelectedBooks(
                                                                        (
                                                                            current,
                                                                        ) =>
                                                                            current.filter(
                                                                                (
                                                                                    selectedBook,
                                                                                ) =>
                                                                                    selectedBook.id !==
                                                                                    book.id,
                                                                            ),
                                                                    )
                                                                }
                                                            >
                                                                Hapus
                                                            </Button>
                                                        </div>
                                                    </div>
                                                ),
                                            )}

                                            {selectedBooks.length === 0 ? (
                                                <div className="rounded-xl border border-dashed border-border/70 bg-background/70 px-4 py-5 text-sm text-muted-foreground">
                                                    Belum ada buku dipilih.
                                                </div>
                                            ) : null}
                                        </div>

                                        <div className="mt-6 space-y-3 border-t border-border/60 pt-4">
                                            {selectedBooks.length >=
                                            maxInputs ? (
                                                <p className="text-sm text-muted-foreground">
                                                    Batas {maxInputs} buku telah
                                                    tercapai.
                                                </p>
                                            ) : null}

                                            <Button
                                                type="submit"
                                                size="lg"
                                                className="h-12 w-full text-base"
                                                disabled={
                                                    processing || !isComplete
                                                }
                                            >
                                                {processing ? (
                                                    <Spinner />
                                                ) : null}
                                                {submitLabel}
                                            </Button>
                                        </div>
                                    </div>
                                </FieldGroup>
                            </div>
                        ) : (
                            <div className="grid gap-4">
                                <FieldGroup className="grid gap-2 rounded-2xl border border-border/70 bg-card p-4">
                                    <KioskField
                                        label="Email atau NIM"
                                        htmlFor="book-member"
                                        error={errors.member_identifier}
                                        required
                                    >
                                        <Input
                                            id="book-member"
                                            name="member_identifier"
                                            autoFocus={autoFocus}
                                            placeholder="email@mhs.unimal.ac.id atau NIM"
                                            value={memberIdentifier}
                                            onChange={(e) =>
                                                setMemberIdentifier(
                                                    e.target.value,
                                                )
                                            }
                                            aria-invalid={Boolean(
                                                errors.member_identifier,
                                            )}
                                            className="h-12 text-base"
                                        />
                                    </KioskField>
                                    <KioskField
                                        label="ISBN / ISSN Buku"
                                        htmlFor="book-isbn-0"
                                        error={
                                            hasIsbnsError
                                                ? (errors.isbns as string) ||
                                                  'Periksa kembali input ISBN Anda.'
                                                : undefined
                                        }
                                        required
                                    >
                                        <div className="grid gap-3">
                                            <Input
                                                key={0}
                                                id="book-isbn-0"
                                                name="isbns.0"
                                                placeholder="Scan atau ketik ISBN/ISSN 1"
                                                value={firstIsbn}
                                                onChange={(e) =>
                                                    setFirstIsbn(e.target.value)
                                                }
                                                aria-invalid={Boolean(
                                                    errors['isbns.0'],
                                                )}
                                            />
                                            {Array.from({
                                                length: maxInputs - 1,
                                            }).map((_, i) => (
                                                <Input
                                                    key={i + 1}
                                                    id={`book-isbn-${i + 1}`}
                                                    name={`isbns.${i + 1}`}
                                                    placeholder={`Scan atau ketik ISBN/ISSN ${i + 2} (opsional)`}
                                                    aria-invalid={Boolean(
                                                        errors[
                                                            `isbns.${i + 1}`
                                                        ],
                                                    )}
                                                />
                                            ))}
                                        </div>
                                    </KioskField>
                                    <Button
                                        type="submit"
                                        size="lg"
                                        className="h-12 w-full text-base"
                                        disabled={processing || !isComplete}
                                    >
                                        {processing ? <Spinner /> : null}
                                        {submitLabel}
                                    </Button>
                                </FieldGroup>
                            </div>
                        )}

                        {usesBookSearch ? (
                            <Dialog
                                open={isSearchDialogOpen}
                                onOpenChange={handleSearchDialogChange}
                            >
                                <DialogContent className="max-w-4xl">
                                    <DialogHeader>
                                        <DialogTitle>Cari Buku</DialogTitle>
                                        <DialogDescription>
                                            {bookSearchMode === 'borrow'
                                                ? 'Cari dan pilih buku.'
                                                : 'Cari dari pinjaman aktif member ini.'}
                                        </DialogDescription>
                                    </DialogHeader>

                                    <div className="grid gap-4">
                                        <Input
                                            id="book-search"
                                            placeholder={
                                                bookSearchMode === 'borrow'
                                                    ? 'Cari judul, penulis, ISBN, atau ISSN'
                                                    : 'Filter judul, penulis, ISBN, atau ISSN'
                                            }
                                            value={searchQuery}
                                            onChange={(e) => {
                                                const nextQuery =
                                                    e.target.value;

                                                setSearchQuery(nextQuery);
                                                setSearchError(null);

                                                if (
                                                    bookSearchMode === 'borrow'
                                                ) {
                                                    if (
                                                        nextQuery.trim() === ''
                                                    ) {
                                                        setSearchResults([]);
                                                        setIsSearching(false);
                                                    } else {
                                                        setIsSearching(true);
                                                    }
                                                } else {
                                                    if (
                                                        memberIdentifierTrimmed ===
                                                        ''
                                                    ) {
                                                        setSearchResults([]);
                                                        setIsSearching(false);
                                                    } else {
                                                        setIsSearching(true);
                                                    }
                                                }
                                            }}
                                            autoFocus
                                            aria-invalid={Boolean(
                                                hasBookIdsError,
                                            )}
                                            className="h-12 text-base"
                                        />

                                        {bookSearchMode === 'return' ? (
                                            <p className="text-sm text-muted-foreground">
                                                Daftar pinjaman aktif
                                                ditampilkan otomatis.
                                            </p>
                                        ) : null}

                                        {searchError ? (
                                            <p className="text-sm text-destructive">
                                                {searchError}
                                            </p>
                                        ) : null}

                                        <div className="rounded-2xl border border-border/70 bg-muted/25">
                                            <ScrollArea className="h-80 rounded-2xl bg-muted/25">
                                                <div className="grid gap-2 p-3">
                                                    {bookSearchMode ===
                                                        'borrow' &&
                                                    searchQuery.trim() ===
                                                        '' ? (
                                                        <p className="px-3 py-4 text-sm text-muted-foreground">
                                                            Mulai ketik untuk
                                                            mencari buku.
                                                        </p>
                                                    ) : isSearching ? (
                                                        <div className="flex items-center gap-2 px-3 py-4 text-sm text-muted-foreground">
                                                            <Spinner />
                                                            Mencari buku...
                                                        </div>
                                                    ) : availableSearchResults.length >
                                                      0 ? (
                                                        availableSearchResults.map(
                                                            (book) => (
                                                                <button
                                                                    key={
                                                                        book.id
                                                                    }
                                                                    type="button"
                                                                    className="rounded-xl border border-transparent px-3 py-3 text-left transition hover:border-border hover:bg-accent/40"
                                                                    onClick={() =>
                                                                        addSelectedBook(
                                                                            book,
                                                                        )
                                                                    }
                                                                >
                                                                    <p className="line-clamp-1 text-sm font-semibold text-foreground">
                                                                        {
                                                                            book.title
                                                                        }
                                                                    </p>
                                                                    <p className="mt-1 line-clamp-1 text-xs text-muted-foreground">
                                                                        {book.authors?.join(
                                                                            ', ',
                                                                        ) ||
                                                                            'Penulis belum tersedia'}
                                                                        {' | '}
                                                                        {book.isbn
                                                                            ? `ISBN ${book.isbn}`
                                                                            : book.issn
                                                                              ? `ISSN ${book.issn}`
                                                                              : 'Tanpa ISBN/ISSN'}
                                                                        {' | '}
                                                                        {bookSearchMode ===
                                                                        'borrow'
                                                                            ? `${book.availableItemsCount} tersedia`
                                                                            : 'Pinjaman aktif'}
                                                                    </p>
                                                                </button>
                                                            ),
                                                        )
                                                    ) : (
                                                        <p className="px-3 py-4 text-sm text-muted-foreground">
                                                            {bookSearchMode ===
                                                            'borrow'
                                                                ? 'Tidak ada buku yang sesuai.'
                                                                : 'Tidak ada pinjaman aktif.'}
                                                        </p>
                                                    )}
                                                </div>
                                            </ScrollArea>
                                        </div>
                                    </div>
                                </DialogContent>
                            </Dialog>
                        ) : null}
                    </>
                );
            }}
        </Form>
    );
}
