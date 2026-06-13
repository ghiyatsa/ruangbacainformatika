import { Form } from '@inertiajs/react';
import { UserIcon, BarcodeIcon, SearchIcon, QrCode } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { FieldGroup } from '@/components/ui/field';
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
    memberFieldMode = 'required',
    onActionSubmit,
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
    memberFieldMode?: 'required' | 'hidden';
    onActionSubmit?: (data: {
        memberIdentifier: string;
        selectedBooks: KioskBookSearchResult[];
        firstIsbn: string;
    }) => void;
}) {
    const [memberIdentifier, setMemberIdentifier] = useState('');
    const [firstIsbn, setFirstIsbn] = useState('');
    const [isSearchDialogOpen, setIsSearchDialogOpen] = useState(false);
    const [selectedBooks, setSelectedBooks] = useState<KioskBookSearchResult[]>(
        [],
    );

    const usesBookSearch = Boolean(bookSearchUrl);
    const requiresMemberBeforeSearch = bookSearchMode === 'return';
    const requiresMemberField = memberFieldMode === 'required';
    const memberIdentifierTrimmed = memberIdentifier.trim();

    const isComplete =
        (!requiresMemberField || memberIdentifierTrimmed !== '') &&
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
                                {requiresMemberField ? (
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
                                                        onChange={(e) =>
                                                            setMemberIdentifier(
                                                                e.target.value,
                                                            )
                                                        }
                                                        aria-invalid={Boolean(
                                                            errors.member_identifier,
                                                        )}
                                                        className="h-full text-base"
                                                    />
                                                    <InputGroupAddon>
                                                        <UserIcon />
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
                                        </KioskField>
                                    </div>
                                ) : (
                                    <div className="flex justify-end gap-2">
                                        {usesBookSearch ? (
                                            <Button
                                                type="button"
                                                variant="default"
                                                className="rounded-md px-4 text-sm font-medium"
                                                disabled={
                                                    selectedBooks.length >=
                                                    maxInputs
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
                                        ) : null}
                                        {onScanQr ? (
                                            <Button
                                                type="button"
                                                variant="secondary"
                                                className="rounded-md px-4 text-sm font-medium"
                                                onClick={onScanQr}
                                            >
                                                <QrCode className="size-4" />
                                                Scan QR
                                            </Button>
                                        ) : null}
                                    </div>
                                )}

                                {usesBookSearch ? (
                                    <>
                                        {hasBookIdsError ? (
                                            <p className="text-sm text-destructive">
                                                {(errors.book_ids as string) ||
                                                    'Pilih buku yang valid dari hasil pencarian.'}
                                            </p>
                                        ) : null}

                                        <div className="mt-2 space-y-4">
                                            <div className="rounded-lg border border-border/70">
                                                <div className="flex items-center justify-between gap-3 border-b border-border/70 px-4 py-3">
                                                    <p className="text-sm font-semibold text-foreground">
                                                        Buku dipilih
                                                    </p>
                                                    <span className="text-xs font-medium text-muted-foreground">
                                                        {selectedBooks.length} /{' '}
                                                        {maxInputs}
                                                    </span>
                                                </div>

                                                <div className="max-h-[24rem] overflow-y-auto p-3">
                                                    {selectedBooks.length > 0 ? (
                                                        <div className="grid gap-2">
                                                            {selectedBooks.map(
                                                                (
                                                                    book,
                                                                    index,
                                                                ) => (
                                                                    <div
                                                                        key={
                                                                            book.id
                                                                        }
                                                                        className="flex items-center gap-3 rounded-lg border border-border/60 p-3 transition hover:bg-accent/40"
                                                                    >
                                                                        <input
                                                                            type="hidden"
                                                                            name={`book_ids.${index}`}
                                                                            value={
                                                                                book.id
                                                                            }
                                                                        />
                                                                        <img
                                                                            src={
                                                                                book.coverImageUrl
                                                                            }
                                                                            alt={
                                                                                book.title
                                                                            }
                                                                            width={48}
                                                                            height={64}
                                                                            className="h-16 w-12 shrink-0 rounded-md border border-border/70 object-cover"
                                                                            loading="lazy"
                                                                        />
                                                                        <div className="min-w-0 flex-1 space-y-1">
                                                                            <p className="line-clamp-1 text-sm font-semibold text-foreground">
                                                                                {
                                                                                    book.title
                                                                                }
                                                                            </p>
                                                                            <p className="line-clamp-1 text-xs text-muted-foreground">
                                                                                {book.authors?.join(
                                                                                    ', ',
                                                                                ) ||
                                                                                    'Penulis belum tersedia'}
                                                                                {
                                                                                    ' | '
                                                                                }
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
                                                        </div>
                                                    ) : (
                                                        <p className="px-2 py-6 text-center text-sm text-muted-foreground">
                                                            Belum ada buku
                                                            dipilih.
                                                        </p>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="mt-4">
                                                <Button
                                                    type={
                                                        onActionSubmit
                                                            ? 'button'
                                                            : 'submit'
                                                    }
                                                    size="lg"
                                                    className="h-12 w-full text-base"
                                                    disabled={
                                                        processing ||
                                                        !isComplete
                                                    }
                                                    onClick={
                                                        onActionSubmit
                                                            ? () =>
                                                                  onActionSubmit(
                                                                      {
                                                                          memberIdentifier,
                                                                          selectedBooks,
                                                                          firstIsbn,
                                                                      },
                                                                  )
                                                            : undefined
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
                                            type={
                                                onActionSubmit
                                                    ? 'button'
                                                    : 'submit'
                                            }
                                            size="lg"
                                            className="h-12 w-full text-base"
                                            disabled={processing || !isComplete}
                                            onClick={
                                                onActionSubmit
                                                    ? () =>
                                                          onActionSubmit({
                                                              memberIdentifier,
                                                              selectedBooks,
                                                              firstIsbn,
                                                          })
                                                    : undefined
                                            }
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
