import { CheckCircle2 } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

export function FlashMessage({ message }: { message?: string }) {
    if (!message) {
        return null;
    }

    return (
        <Alert className="relative z-[70] border-emerald-500/30 bg-emerald-500/10 text-emerald-700 shadow-sm dark:text-emerald-400">
            <CheckCircle2 className="size-4 text-emerald-600 dark:text-emerald-400" />
            <AlertTitle className="font-semibold">Berhasil</AlertTitle>
            <AlertDescription className="text-emerald-700/80 dark:text-emerald-400/80">
                {message}
            </AlertDescription>
        </Alert>
    );
}
