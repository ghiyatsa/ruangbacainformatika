import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { FieldDescription } from '@/components/ui/field';
import { AppLogo } from '@/features/welcome/components/AppLogo';
import { cn } from '@/lib/utils';

export default function AuthLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: ReactNode;
}) {
    const { component } = usePage();
    const isCardLayout =
        component === 'auth/login' || component === 'auth/register';

    return (
        <div
            className={cn(
                'flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10',
                isCardLayout ? 'bg-muted' : 'bg-background',
            )}
        >
            <div className="flex w-full max-w-sm flex-col gap-6">
                <div className="flex w-full justify-center">
                    <AppLogo />
                </div>

                {isCardLayout ? (
                    <Card className="rounded-xl">
                        <CardHeader className="pb-0 text-center">
                            <CardTitle className="text-2xl">{title}</CardTitle>
                            {description ? (
                                <CardDescription>{description}</CardDescription>
                            ) : null}
                        </CardHeader>
                        <CardContent className="pt-6">{children}</CardContent>
                    </Card>
                ) : (
                    <div className="flex flex-col gap-6">
                        <div className="space-y-2 text-center">
                            <h1 className="text-xl font-medium">{title}</h1>
                            <p className="text-sm text-muted-foreground">
                                {description}
                            </p>
                        </div>
                        {children}
                    </div>
                )}

                {isCardLayout && (
                    <FieldDescription className="text-center">
                        By clicking continue, you agree to our{' '}
                        <a href="#" className="underline underline-offset-4">
                            Terms of Service
                        </a>{' '}
                        and{' '}
                        <a href="#" className="underline underline-offset-4">
                            Privacy Policy
                        </a>
                        .
                    </FieldDescription>
                )}
            </div>
        </div>
    );
}
