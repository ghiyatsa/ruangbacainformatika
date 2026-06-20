import { LogIn } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface GuestCommentPromptProps {
    googleLoginUrl: string;
    /** Compact variant used inline inside reply forms. */
    size?: 'sm' | 'xs';
}

export function GuestCommentPrompt({
    googleLoginUrl,
    size = 'sm',
}: GuestCommentPromptProps) {
    if (size === 'xs') {
        return (
            <div className="space-y-2 py-3 text-center border border-dashed border-border bg-muted/5 rounded-lg">
                <p className="text-xs text-muted-foreground">
                    Silakan masuk dengan Google untuk membalas komentar ini.
                </p>
                <Button asChild size="xs" className="h-7 gap-1.5 rounded-lg">
                    <a href={googleLoginUrl}>
                        <LogIn className="size-3.5" />
                        Masuk dengan Google
                    </a>
                </Button>
            </div>
        );
    }

    return (
        <div className="space-y-3 py-4 text-center">
            <p className="text-sm text-muted-foreground">
                Silakan masuk dengan akun Google Anda untuk menulis komentar di
                artikel ini.
            </p>
            <Button asChild size="sm" className="gap-2 rounded-lg">
                <a href={googleLoginUrl}>
                    <LogIn className="size-4" />
                    Masuk dengan Google
                </a>
            </Button>
        </div>
    );
}
