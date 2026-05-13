import { Form } from '@inertiajs/react';
import { useDeferredValue, useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { FieldDescription, FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { KioskField } from '@/features/kiosk/components/KioskField';
import type { KioskBookSearchResult } from '@/features/kiosk/types';
import type { RouteFormDefinition } from '@/wayfinder';

export function BookActionForm({
    action,
    submitLabel,
    description,
    maxInputs = 3,
    bookSearchUrl,
}: {
    action: RouteFormDefinition<'post'>;
    submitLabel: string;
    description: string;
    maxInputs?: number;
    bookSearchUrl?: string;
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

    const isComplete =
        memberIdentifier.trim() !== '' &&
        (usesBookSearch ? selectedBooks.length > 0 : firstIsbn.trim() !== '');

    useEffect(() => {
        if (!usesBookSearch) {
            return;
        }

        if (deferredSearchQuery.length < 2) {
            return;
        }

        const abortController = new AbortController();
        const searchUrl = new URL(bookSearchUrl!, window.location.origin);
        searchUrl.searchParams.set('q', deferredSearchQuery);

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
    }, [bookSearchUrl, deferredSearchQuery, usesBookSearch]);

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
                        <FieldDescription>{description}</FieldDescription>

                        <FieldGroup className="grid gap-5">
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
                                    onChange={(e) =>
                                        setMemberIdentifier(e.target.value)
                                    }
                                    aria-invalid={Boolean(
                                        errors.member_identifier,
                                    )}
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
                                    <div className="grid gap-3">
                                        <Input
                                            id="book-search"
                                            placeholder="Cari judul, penulis, ISBN, atau ISSN"
                                            value={searchQuery}
                                            onChange={(e) => {
                                                const nextQuery =
                                                    e.target.value;

                                                setSearchQuery(nextQuery);

                                                if (
                                                    nextQuery.trim().length < 2
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
                                                maxInputs
                                            }
                                            aria-invalid={Boolean(
                                                hasBookIdsError,
                                            )}
                                        />

                                        {selectedBooks.map((book, index) => (
                                            <div
                                                key={book.id}
                                                className="flex items-start justify-between gap-3 rounded-xl border border-border/70 bg-card/70 px-4 py-3"
                                            >
                                                <div className="space-y-1">
                                                    <input
                                                        type="hidden"
                                                        name={`book_ids.${index}`}
                                                        value={book.id}
                                                    />
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
                                                        {' · '}
                                                        {
                                                            book.availableItemsCount
                                                        }{' '}
                                                        eksemplar tersedia
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
                                        ))}

                                        {selectedBooks.length >= maxInputs ? (
                                            <p className="text-xs text-muted-foreground">
                                                Batas maksimal {maxInputs} buku
                                                sudah tercapai.
                                            </p>
                                        ) : null}

                                        {searchError ? (
                                            <p className="text-xs text-destructive">
                                                {searchError}
                                            </p>
                                        ) : null}

                                        {deferredSearchQuery.length >= 2 ? (
                                            <div className="grid gap-2 rounded-xl border border-border/70 bg-background/80 p-2">
                                                {isSearching ? (
                                                    <div className="flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground">
                                                        <Spinner />
                                                        Mencari buku...
                                                    </div>
                                                ) : availableSearchResults.length >
                                                  0 ? (
                                                    availableSearchResults.map(
                                                        (book) => (
                                                            <button
                                                                key={book.id}
                                                                type="button"
                                                                className="rounded-lg border border-transparent px-3 py-3 text-left transition hover:border-border hover:bg-accent/40"
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
                                                                }}
                                                            >
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
                                                                    {' · '}
                                                                    {
                                                                        book.availableItemsCount
                                                                    }{' '}
                                                                    eksemplar
                                                                    tersedia
                                                                </p>
                                                            </button>
                                                        ),
                                                    )
                                                ) : (
                                                    <p className="px-3 py-2 text-sm text-muted-foreground">
                                                        Tidak ada buku yang
                                                        cocok atau masih
                                                        tersedia.
                                                    </p>
                                                )}
                                            </div>
                                        ) : (
                                            <p className="text-xs text-muted-foreground">
                                                Ketik minimal 2 karakter untuk
                                                mencari buku.
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
                                                    errors[`isbns.${i + 1}`],
                                                )}
                                            />
                                        ))}
                                    </div>
                                )}
                            </KioskField>
                        </FieldGroup>

                        <Button
                            type="submit"
                            size="lg"
                            disabled={processing || !isComplete}
                        >
                            {processing ? <Spinner /> : null}
                            {submitLabel}
                        </Button>
                    </>
                );
            }}
        </Form>
    );
}
