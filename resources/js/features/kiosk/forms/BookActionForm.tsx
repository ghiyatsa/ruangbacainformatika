import { Form } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { FieldDescription, FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { KioskField } from '@/features/kiosk/components/KioskField';
import type { RouteFormDefinition } from '@/wayfinder';

export function BookActionForm({
    action,
    submitLabel,
    description,
    maxInputs = 3,
}: {
    action: RouteFormDefinition<'post'>;
    submitLabel: string;
    description: string;
    maxInputs?: number;
}) {
    const [memberIdentifier, setMemberIdentifier] = useState('');
    const [firstIsbn, setFirstIsbn] = useState('');

    const isComplete =
        memberIdentifier.trim() !== '' && firstIsbn.trim() !== '';

    return (
        <Form
            {...action}
            resetOnSuccess
            disableWhileProcessing
            className="flex flex-col gap-6"
        >
            {({ errors, processing }) => {
                // Check if there are any errors related to isbns
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
                                    {Array.from({ length: maxInputs - 1 }).map(
                                        (_, i) => (
                                            <Input
                                                key={i + 1}
                                                id={`book-isbn-${i + 1}`}
                                                name={`isbns.${i + 1}`}
                                                placeholder={`Scan atau ketik ISBN/ISSN ${i + 2} (opsional)`}
                                                aria-invalid={Boolean(
                                                    errors[`isbns.${i + 1}`],
                                                )}
                                            />
                                        ),
                                    )}
                                </div>
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
