import type { ReactNode } from 'react';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { cn } from '@/lib/utils';

export function KioskField({
    label,
    htmlFor,
    error,
    required = false,
    className,
    children,
}: {
    label: string;
    htmlFor: string;
    error?: string;
    required?: boolean;
    className?: string;
    children: ReactNode;
}) {
    return (
        <Field data-invalid={Boolean(error)} className={cn(className)}>
            <FieldLabel htmlFor={htmlFor}>
                {label}
                {required ? <span className="text-destructive">*</span> : null}
            </FieldLabel>
            {children}
            <FieldError>{error}</FieldError>
        </Field>
    );
}
