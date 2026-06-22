import { router } from '@inertiajs/react';
import { LogIn } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { openGoogleLoginPopup } from '@/lib/auth';

interface GuestCommentPromptProps {
    googleLoginUrl: string;
    /** Compact variant used inline inside reply forms. */
    size?: 'sm' | 'xs';
}

export function GuestCommentPrompt({
    googleLoginUrl,
    size = 'sm',
}: GuestCommentPromptProps) {
    const handleLogin = (e: React.MouseEvent) => {
        e.preventDefault();
        openGoogleLoginPopup(googleLoginUrl)
            .then((url) => {
                if (url) {
                    router.visit(url);
                } else {
                    router.reload();
                }
            })
            .catch((err) => {
                console.error(err);
            });
    };

    if (size === 'xs') {
        return (
            <div className="space-y-2 py-3 text-center border border-dashed border-border bg-muted/5 rounded-lg">
                <p className="text-xs text-muted-foreground">
                    Silakan masuk dengan Google untuk membalas komentar ini.
                </p>
                <Button size="xs" className="h-7 gap-1.5 rounded-lg" onClick={handleLogin}>
                    <LogIn className="size-3.5" />
                    Masuk dengan Google
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
            <Button size="sm" className="gap-2 rounded-lg" onClick={handleLogin}>
                <LogIn className="size-4" />
                Masuk dengan Google
            </Button>
        </div>
    );
}
