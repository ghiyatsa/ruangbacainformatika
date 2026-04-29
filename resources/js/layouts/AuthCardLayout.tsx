import type { PropsWithChildren } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { FieldDescription } from '@/components/ui/field';
import { AppLogo } from '@/components/welcome/AppLogo';

export default function AuthCardLayout({
    children,
    title,
    description,
}: PropsWithChildren<{
    title?: string;
    description?: string;
}>) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10">
            <div className="flex w-full max-w-sm flex-col gap-4">
                <div className="flex w-full justify-center">
                    <AppLogo />
                </div>
                <Card className="rounded-xl">
                    <CardHeader className="pb-0 text-center">
                        <CardTitle className="text-2xl">{title}</CardTitle>
                        {description ? (
                            <CardDescription>{description}</CardDescription>
                        ) : null}
                    </CardHeader>
                    <CardContent>{children}</CardContent>
                </Card>
                <FieldDescription className="text-center">
                    By clicking continue, you agree to our{' '}
                    <a href="#">Terms of Service</a> and{' '}
                    <a href="#">Privacy Policy</a>.
                </FieldDescription>
            </div>
        </div>
    );
}
