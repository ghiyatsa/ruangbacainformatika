import { CheckCircle2 } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';

export function FlashMessage({ message }: { message?: string }) {
    if (!message) {
        return null;
    }

    return (
        <Alert>
            <CheckCircle2 />
            <AlertDescription>{message}</AlertDescription>
        </Alert>
    );
}
