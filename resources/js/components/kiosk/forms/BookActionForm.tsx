import { Form } from '@inertiajs/react';
import type { RouteFormDefinition } from '@/wayfinder';
import { Button } from '@/components/ui/button';
import { FieldDescription, FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { KioskField } from '@/components/kiosk/KioskField';
import { FormBackButton } from './FormBackButton';

export function BookActionForm({
    action,
    submitLabel,
    description,
    onBack,
}: {
    action: RouteFormDefinition<'post'>;
    submitLabel: string;
    description: string;
    onBack: () => void;
}) {
    return (
        <Form
            {...action}
            resetOnSuccess
            disableWhileProcessing
            className="flex flex-col gap-6"
        >
            {({ errors, processing }) => (
                <>
                    <FormBackButton onBack={onBack} />
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
                                aria-invalid={Boolean(errors.member_identifier)}
                            />
                        </KioskField>

                        <KioskField
                            label="ISBN / ISSN Buku"
                            htmlFor="book-isbn"
                            error={errors.isbn}
                            required
                        >
                            <Input
                                id="book-isbn"
                                name="isbn"
                                placeholder="Scan atau ketik ISBN/ISSN"
                                aria-invalid={Boolean(errors.isbn)}
                            />
                        </KioskField>
                    </FieldGroup>

                    <Button type="submit" size="lg" disabled={processing}>
                        {processing ? <Spinner /> : null}
                        {submitLabel}
                    </Button>
                </>
            )}
        </Form>
    );
}
