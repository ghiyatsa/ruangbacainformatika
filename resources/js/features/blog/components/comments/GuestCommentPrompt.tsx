import { Link } from '@inertiajs/react';
import { LogIn } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { login } from '@/routes';

interface GuestCommentPromptProps {
    googleLoginUrl?: string;
    /** Compact variant used inline inside reply forms. */
    size?: 'sm' | 'xs';
}

export function GuestCommentPrompt({
    size = 'sm',
}: GuestCommentPromptProps) {
    if (size === 'xs') {
        return (
            <div className="space-y-2 py-3 text-center border border-dashed border-border bg-muted/5 rounded-lg">
                <p className="text-xs text-muted-foreground">
                    Silakan masuk untuk membalas komentar ini.
                </p>
                <Button asChild size="xs" className="h-7 gap-1.5 rounded-lg">
                    <Link href={login.url()}>
                        <LogIn className="size-3.5" />
                        Masuk
                    </Link>
                </Button>
            </div>
        );
    }

    return (
        <div className="space-y-3 py-4 text-center">
            <p className="text-sm text-muted-foreground">
                Silakan masuk dengan akun Anda untuk menulis komentar di artikel ini.
            </p>
            <Button asChild size="sm" className="gap-2 rounded-lg">
                <Link href={login.url()}>
                    <LogIn className="size-4" />
                    Masuk
                </Link>
            </Button>
        </div>
    );
}
