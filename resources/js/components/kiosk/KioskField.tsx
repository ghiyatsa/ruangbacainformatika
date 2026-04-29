import type { ReactNode } from 'react';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';

export function KioskField({
    label,
    htmlFor,
    error,
    required = false,
    children,
}: {
    label: string;
    htmlFor: string;
    error?: string;
    required?: boolean;
    children: ReactNode;
}) {
    return (
        <Field data-invalid={Boolean(error)}>
            <FieldLabel htmlFor={htmlFor}>
                {label}
                {required ? <span className="text-destructive">*</span> : null}
            </FieldLabel>
            {children}
            <FieldError>{error}</FieldError>
        </Field>
    );
}
