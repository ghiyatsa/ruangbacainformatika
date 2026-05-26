import { Form } from '@inertiajs/react';
import { UserIcon, BarcodeIcon, SearchIcon, QrCode } from 'lucide-react';
import { useDeferredValue, useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { FieldGroup, FieldDescription } from '@/components/ui/field';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
import { Spinner } from '@/components/ui/spinner';
import { BookSearchDialog } from '@/features/kiosk/components/BookSearchDialog';
import { KioskField } from '@/features/kiosk/components/KioskField';
import type {
    KioskBookSearchMode,
    KioskBookSearchResult,
} from '@/features/kiosk/types';

export function BookActionForm({
    action,
    submitLabel,
    maxInputs = 3,
    bookSearchUrl,
    bookSearchMode = 'borrow',
    autoFocus = true,
    onScanQr,
}: {
    action: {
        url: string;
        method: 'get' | 'post' | 'put' | 'patch' | 'delete';
    };
    submitLabel: string;
    description: string;
    maxInputs?: number;
    bookSearchUrl?: string;
    bookSearchMode?: KioskBookSearchMode;
    autoFocus?: boolean;
    onScanQr?: () => void;
}) {
    const [memberIdentifier, setMemberIdentifier] = useState('');
    const [firstIsbn, setFirstIsbn] = useState('');
    const [isSearchDialogOpen, setIsSearchDialogOpen] = useState(false);
    const [selectedBooks, setSelectedBooks] = useState<KioskBookSearchResult[]>(
        [],
    );
    const [memberData, setMemberData] = useState<{
        id: number;
        name: string;
        email: string;
        whatsapp: string;
    } | null>(null);
    const [isSearchingMember, setIsSearchingMember] = useState(false);

    const deferredMemberIdentifier = useDeferredValue(memberIdentifier.trim());

    useEffect(() => {
        if (deferredMemberIdentifier === '') {
            return;
        }

        const abortController = new AbortController();
        const searchUrl = new URL(
            '/kiosk/members/find',
            window.location.origin,
        );
        searchUrl.searchParams.set('identifier', deferredMemberIdentifier);

        void fetch(searchUrl.toString(), {
            signal: abortController.signal,
        })
            .then(async (response) => {
                if (!response.ok) {
                    throw new Error('Gagal memuat data anggota.');
                }

                const payload = (await response.json()) as {
                    member?: {
                        id: number;
                        name: string;
                        email: string;
                        whatsapp: string;
                    } | null;
                };

                setMemberData(payload.member ?? null);
            })
            .catch((error: unknown) => {
                if (
                    error instanceof DOMException &&
                    error.name === 'AbortError'
                ) {
                    return;
                }

                setMemberData(null);
            })
            .finally(() => {
                if (!abortController.signal.aborted) {
                    setIsSearchingMember(false);
                }
            });

        return () => abortController.abort();
    }, [deferredMemberIdentifier]);

    const usesBookSearch = Boolean(bookSearchUrl);
    const requiresMemberBeforeSearch = bookSearchMode === 'return';
    const memberIdentifierTrimmed = memberIdentifier.trim();

    const isComplete =
        memberIdentifierTrimmed !== '' &&
        (usesBookSearch ? selectedBooks.length > 0 : firstIsbn.trim() !== '');

    const handleSearchDialogChange = (open: boolean) => {
        setIsSearchDialogOpen(open);
    };

    const addSelectedBook = (book: KioskBookSearchResult) => {
        setSelectedBooks((current) =>
            current.length >= maxInputs ? current : [...current, book],
        );
        setIsSearchDialogOpen(false);
    };

    return (
        <Form
            action={action.url}
            method={action.method}
            resetOnSuccess
            disableWhileProcessing
            autoComplete="off"
            className="flex flex-col gap-4"
            onSuccess={() => {
                setMemberIdentifier('');
                setFirstIsbn('');
                setSelectedBooks([]);
                setIsSearchDialogOpen(false);
                setMemberData(null);
                setIsSearchingMember(false);
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
                        <input
                            type="text"
                            name="username"
                            autoComplete="username"
                            tabIndex={-1}
                            className="hidden"
                            aria-hidden="true"
                        />
                        <input
                            type="password"
                            name="password"
                            autoComplete="current-password"
                            tabIndex={-1}
                            className="hidden"
                            aria-hidden="true"
                        />

                        <div className="grid gap-4">
                            <FieldGroup className="grid gap-4 border-none bg-transparent p-0 shadow-none">
                                <div className="grid gap-4">
                                    <KioskField
                                        label="NIM, Email, atau No. HP"
                                        htmlFor="book-member"
                                        error={errors.member_identifier}
                                        required
                                    >
                                        <div className="flex gap-2">
                                            <InputGroup className="flex-1">
                                                <InputGroupInput
                                                    id="book-member"
                                                    name="member_identifier"
                                                    autoFocus={autoFocus}
                                                    autoComplete="new-password"
                                                    autoCapitalize="none"
                                                    autoCorrect="off"
                                                    spellCheck={false}
                                                    data-lpignore="true"
                                                    data-1p-ignore="true"
                                                    data-bwignore="true"
                                                    placeholder="NIM, email, atau no. HP"
                                                    value={memberIdentifier}
                                                    onChange={(e) => {
                                                        const val =
                                                            e.target.value;
                                                        setMemberIdentifier(
                                                            val,
                                                        );

                                                        if (val.trim() === '') {
                                                            setMemberData(null);
                                                            setIsSearchingMember(
                                                                false,
                                                            );
                                                        } else {
                                                            setIsSearchingMember(
                                                                true,
                                                            );
                                                        }
                                                    }}
                                                    aria-invalid={Boolean(
                                                        errors.member_identifier,
                                                    )}
                                                    className="h-full text-base"
                                                />
                                                <InputGroupAddon>
                                                    {isSearchingMember ? (
                                                        <Spinner />
                                                    ) : (
                                                        <UserIcon />
                                                    )}
                                                </InputGroupAddon>
                                            </InputGroup>
                                            {usesBookSearch && (
                                                <Button
                                                    type="button"
                                                    variant="default"
                                                    className="shrink-0 rounded-md px-4 text-sm font-medium"
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
                                                    <SearchIcon />
                                                    Cari Buku
                                                </Button>
                                            )}
                                            {onScanQr && (
                                                <Button
                                                    type="button"
                                                    variant="secondary"
                                                    className="shrink-0 rounded-md px-4 text-sm font-medium"
                                                    onClick={onScanQr}
                                                >
                                                    <QrCode className="size-4" />
                                                    Scan QR
                                                </Button>
                                            )}
                                        </div>
                                        {memberData && (
                                            <FieldDescription className="mt-1">
                                                {memberData.name} (
                                                {memberData.email})
                                            </FieldDescription>
                                        )}
                                    </KioskField>
                                </div>

                                {usesBookSearch ? (
                                    <>
                                        {hasBookIdsError ? (
                                            <p className="text-sm text-destructive">
                                                {(errors.book_ids as string) ||
                                                    'Pilih buku yang valid dari hasil pencarian.'}
                                            </p>
                                        ) : null}

                                        <div className="mt-2 space-y-4">
                                            <div className="flex items-center justify-between border-b border-border/40 pb-2">
                                                <p className="text-sm font-semibold text-foreground">
                                                    Buku dipilih
                                                </p>
                                                <span className="text-xs font-medium text-muted-foreground">
                                                    {selectedBooks.length} /{' '}
                                                    {maxInputs}
                                                </span>
                                            </div>

                                            <div className="mt-2 space-y-1">
                                                {selectedBooks.map(
                                                    (book, index) => (
                                                        <div
                                                            key={book.id}
                                                            className="flex items-center justify-between gap-4 border-b border-border/40 py-3 last:border-0"
                                                        >
                                                            <input
                                                                type="hidden"
                                                                name={`book_ids.${index}`}
                                                                value={book.id}
                                                            />
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
                                                    ),
                                                )}

                                                {selectedBooks.length === 0 ? (
                                                    <p className="py-6 text-center text-sm text-muted-foreground">
                                                        Belum ada buku dipilih.
                                                    </p>
                                                ) : null}
                                            </div>

                                            <div className="mt-4">
                                                <Button
                                                    type="submit"
                                                    size="lg"
                                                    className="h-12 w-full text-base"
                                                    disabled={
                                                        processing ||
                                                        !isComplete
                                                    }
                                                >
                                                    {processing ? (
                                                        <Spinner />
                                                    ) : null}
                                                    {submitLabel}
                                                </Button>
                                            </div>
                                        </div>
                                    </>
                                ) : (
                                    <>
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
                                                <InputGroup>
                                                    <InputGroupInput
                                                        key={0}
                                                        id="book-isbn-0"
                                                        name="isbns.0"
                                                        autoComplete="new-password"
                                                        autoCorrect="off"
                                                        spellCheck={false}
                                                        data-lpignore="true"
                                                        data-1p-ignore="true"
                                                        data-bwignore="true"
                                                        placeholder="Scan atau ketik ISBN/ISSN 1"
                                                        value={firstIsbn}
                                                        onChange={(e) =>
                                                            setFirstIsbn(
                                                                e.target.value,
                                                            )
                                                        }
                                                        aria-invalid={Boolean(
                                                            errors['isbns.0'],
                                                        )}
                                                    />
                                                    <InputGroupAddon>
                                                        <BarcodeIcon />
                                                    </InputGroupAddon>
                                                </InputGroup>
                                                {Array.from({
                                                    length: maxInputs - 1,
                                                }).map((_, i) => (
                                                    <InputGroup key={i + 1}>
                                                        <InputGroupInput
                                                            id={`book-isbn-${i + 1}`}
                                                            name={`isbns.${i + 1}`}
                                                            autoComplete="new-password"
                                                            autoCorrect="off"
                                                            spellCheck={false}
                                                            data-lpignore="true"
                                                            data-1p-ignore="true"
                                                            data-bwignore="true"
                                                            placeholder={`Scan atau ketik ISBN/ISSN ${i + 2} (opsional)`}
                                                            aria-invalid={Boolean(
                                                                errors[
                                                                    `isbns.${i + 1}`
                                                                ],
                                                            )}
                                                        />
                                                        <InputGroupAddon>
                                                            <BarcodeIcon />
                                                        </InputGroupAddon>
                                                    </InputGroup>
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
                                    </>
                                )}
                            </FieldGroup>
                        </div>

                        {usesBookSearch ? (
                            <BookSearchDialog
                                isOpen={isSearchDialogOpen}
                                onOpenChange={handleSearchDialogChange}
                                bookSearchUrl={bookSearchUrl!}
                                bookSearchMode={bookSearchMode}
                                memberIdentifier={memberIdentifier}
                                onSelectBook={addSelectedBook}
                                selectedBooks={selectedBooks}
                                maxInputs={maxInputs}
                                hasError={hasBookIdsError}
                            />
                        ) : null}
                    </>
                );
            }}
        </Form>
    );
}
