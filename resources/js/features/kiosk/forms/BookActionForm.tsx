import { Form } from '@inertiajs/react';
import { useDeferredValue, useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { FieldDescription, FieldGroup } from '@/components/ui/field';
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
}: {
    action: RouteFormDefinition<'post'>;
    submitLabel: string;
    description: string;
    maxInputs?: number;
    bookSearchUrl?: string;
    bookSearchMode?: KioskBookSearchMode;
}) {
    const [memberIdentifier, setMemberIdentifier] = useState('');
    const [firstIsbn, setFirstIsbn] = useState('');
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
        ? deferredSearchQuery.length >= 2 &&
          (!requiresMemberBeforeSearch || memberIdentifierTrimmed !== '')
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

    return (
        <Form
            {...action}
            resetOnSuccess
            disableWhileProcessing
            className="flex flex-col gap-6"
            onSuccess={() => {
                setMemberIdentifier('');
                setFirstIsbn('');
                setSearchQuery('');
                setSearchResults([]);
                setSelectedBooks([]);
                setSearchError(null);
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
                        <FieldDescription className="max-w-3xl text-sm leading-6">
                            {description}
                        </FieldDescription>

                        <div className="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.95fr)]">
                            <FieldGroup className="grid gap-5 rounded-3xl border border-border/70 bg-card/70 p-6 shadow-sm xl:p-7">
                                <KioskField
                                    label="Email / NIM"
                                    htmlFor="book-member"
                                    error={errors.member_identifier}
                                    required
                                >
                                    <Input
                                        id="book-member"
                                        name="member_identifier"
                                        autoFocus
                                        placeholder="email@mhs.unimal.ac.id atau NIM"
                                        value={memberIdentifier}
                                        onChange={(e) => {
                                            setMemberIdentifier(e.target.value);

                                            if (bookSearchMode === 'return') {
                                                setSearchResults([]);
                                            }
                                        }}
                                        aria-invalid={Boolean(
                                            errors.member_identifier,
                                        )}
                                        className="h-12 text-base"
                                    />
                                </KioskField>

                                <KioskField
                                    label={
                                        usesBookSearch
                                            ? 'Cari Buku'
                                            : 'ISBN / ISSN Buku'
                                    }
                                    htmlFor={
                                        usesBookSearch
                                            ? 'book-search'
                                            : 'book-isbn-0'
                                    }
                                    error={
                                        usesBookSearch
                                            ? hasBookIdsError
                                                ? (errors.book_ids as string) ||
                                                  'Pilih buku yang valid dari hasil pencarian.'
                                                : undefined
                                            : hasIsbnsError
                                              ? (errors.isbns as string) ||
                                                'Periksa kembali input ISBN Anda.'
                                              : undefined
                                    }
                                    required
                                >
                                    {usesBookSearch ? (
                                        <div className="grid gap-4">
                                            <Input
                                                id="book-search"
                                                placeholder={
                                                    bookSearchMode === 'borrow'
                                                        ? 'Cari judul, penulis, ISBN, atau ISSN'
                                                        : 'Cari buku yang sedang dipinjam member ini'
                                                }
                                                value={searchQuery}
                                                onChange={(e) => {
                                                    const nextQuery =
                                                        e.target.value;

                                                    setSearchQuery(nextQuery);

                                                    if (
                                                        nextQuery.trim()
                                                            .length < 2
                                                    ) {
                                                        setSearchResults([]);
                                                        setIsSearching(false);
                                                        setSearchError(null);
                                                    } else {
                                                        setIsSearching(true);
                                                        setSearchError(null);
                                                    }
                                                }}
                                                disabled={
                                                    selectedBooks.length >=
                                                        maxInputs ||
                                                    (requiresMemberBeforeSearch &&
                                                        memberIdentifierTrimmed ===
                                                            '')
                                                }
                                                aria-invalid={Boolean(
                                                    hasBookIdsError,
                                                )}
                                                className="h-13 text-base"
                                            />

                                            {requiresMemberBeforeSearch &&
                                            memberIdentifierTrimmed === '' ? (
                                                <p className="rounded-2xl border border-dashed border-border/70 bg-muted/35 px-4 py-3 text-sm text-muted-foreground">
                                                    Masukkan Email atau NIM
                                                    terlebih dahulu agar daftar
                                                    buku pinjaman aktif bisa
                                                    ditampilkan.
                                                </p>
                                            ) : null}

                                            {searchError ? (
                                                <p className="text-sm text-destructive">
                                                    {searchError}
                                                </p>
                                            ) : null}

                                            {canSearchBooks ? (
                                                <div className="rounded-2xl border border-border/70 bg-background/80">
                                                    <ScrollArea className="max-h-96">
                                                        <div className="grid gap-2 p-3">
                                                            {isSearching ? (
                                                                <div className="flex items-center gap-2 px-3 py-4 text-sm text-muted-foreground">
                                                                    <Spinner />
                                                                    Mencari
                                                                    buku...
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
                                                                            className="rounded-2xl border border-transparent px-4 py-4 text-left transition hover:border-border hover:bg-accent/40"
                                                                            onClick={() => {
                                                                                setSelectedBooks(
                                                                                    (
                                                                                        current,
                                                                                    ) =>
                                                                                        current.length >=
                                                                                        maxInputs
                                                                                            ? current
                                                                                            : [
                                                                                                  ...current,
                                                                                                  book,
                                                                                              ],
                                                                                );
                                                                                setSearchQuery(
                                                                                    '',
                                                                                );
                                                                                setSearchResults(
                                                                                    [],
                                                                                );
                                                                                setSearchError(
                                                                                    null,
                                                                                );
                                                                            }}
                                                                        >
                                                                            <p className="text-base font-semibold text-foreground">
                                                                                {
                                                                                    book.title
                                                                                }
                                                                            </p>
                                                                            <p className="mt-1 text-sm text-muted-foreground">
                                                                                {book.authors?.join(
                                                                                    ', ',
                                                                                ) ||
                                                                                    'Penulis belum tersedia'}
                                                                            </p>
                                                                            <p className="mt-2 text-sm text-muted-foreground">
                                                                                {book.isbn
                                                                                    ? `ISBN ${book.isbn}`
                                                                                    : book.issn
                                                                                      ? `ISSN ${book.issn}`
                                                                                      : 'Tanpa ISBN/ISSN'}
                                                                                {
                                                                                    ' · '
                                                                                }
                                                                                {bookSearchMode ===
                                                                                'borrow'
                                                                                    ? `${book.availableItemsCount} eksemplar tersedia`
                                                                                    : 'Masuk dalam pinjaman aktif member'}
                                                                            </p>
                                                                        </button>
                                                                    ),
                                                                )
                                                            ) : (
                                                                <p className="px-3 py-4 text-sm text-muted-foreground">
                                                                    {bookSearchMode ===
                                                                    'borrow'
                                                                        ? 'Tidak ada buku yang cocok atau masih tersedia.'
                                                                        : 'Tidak ada buku pinjaman aktif yang cocok untuk member ini.'}
                                                                </p>
                                                            )}
                                                        </div>
                                                    </ScrollArea>
                                                </div>
                                            ) : (
                                                <p className="text-sm text-muted-foreground">
                                                    Ketik minimal 2 karakter
                                                    untuk mencari buku.
                                                </p>
                                            )}
                                        </div>
                                    ) : (
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
                                    )}
                                </KioskField>
                            </FieldGroup>

                            <div className="flex h-full flex-col rounded-3xl border border-border/70 bg-linear-to-br from-muted/55 via-card to-card p-6 shadow-sm xl:p-7">
                                <div className="space-y-2 border-b border-border/60 pb-4">
                                    <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                                        Ringkasan
                                    </p>
                                    <h3 className="text-2xl font-semibold tracking-tight text-foreground">
                                        {selectedBooks.length} / {maxInputs}{' '}
                                        buku dipilih
                                    </h3>
                                    <p className="text-sm leading-6 text-muted-foreground">
                                        {bookSearchMode === 'borrow'
                                            ? 'Pilih buku yang tersedia untuk dipinjam, lalu konfirmasi transaksi anggota.'
                                            : 'Pilih buku dalam pinjaman aktif anggota ini, lalu selesaikan proses pengembalian.'}
                                    </p>
                                </div>

                                <div className="mt-5 flex-1 space-y-3">
                                    {selectedBooks.map((book, index) => (
                                        <div
                                            key={book.id}
                                            className="rounded-2xl border border-border/70 bg-background/80 p-4"
                                        >
                                            <input
                                                type="hidden"
                                                name={`book_ids.${index}`}
                                                value={book.id}
                                            />
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="space-y-1.5">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        {book.title}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {book.authors?.join(
                                                            ', ',
                                                        ) ||
                                                            'Penulis belum tersedia'}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
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
                                                    onClick={() =>
                                                        setSelectedBooks(
                                                            (current) =>
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
                                    ))}

                                    {selectedBooks.length === 0 ? (
                                        <div className="rounded-2xl border border-dashed border-border/70 bg-background/60 px-4 py-6 text-sm text-muted-foreground">
                                            Belum ada buku yang dipilih.
                                        </div>
                                    ) : null}
                                </div>

                                <div className="mt-6 space-y-3">
                                    {selectedBooks.length >= maxInputs ? (
                                        <p className="text-sm text-muted-foreground">
                                            Batas maksimal {maxInputs} buku
                                            sudah tercapai.
                                        </p>
                                    ) : null}

                                    <Button
                                        type="submit"
                                        size="lg"
                                        className="h-12 w-full text-base"
                                        disabled={processing || !isComplete}
                                    >
                                        {processing ? <Spinner /> : null}
                                        {submitLabel}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </>
                );
            }}
        </Form>
    );
}
