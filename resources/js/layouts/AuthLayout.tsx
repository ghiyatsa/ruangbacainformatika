import { BackgroundPattern } from '@/components/layout/BackgroundPattern';
import { AppLogo } from '@/components/layout/header/AppLogo';
import GoogleOneTapPrompt from '@/features/auth/components/GoogleOneTapPrompt';
import type { ReactNode } from 'react';

export default function AuthLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: ReactNode;
}) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
            <BackgroundPattern />
            <GoogleOneTapPrompt />
            <div className="flex w-full max-w-sm flex-col gap-6">
                <div className="flex w-full justify-center">
                    <AppLogo />
                </div>

                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-2 text-center">
                        <h1 className="text-xl font-medium">{title}</h1>
                        {description ? (
                            <p className="text-sm text-muted-foreground">
                                {description}
                            </p>
                        ) : null}
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
