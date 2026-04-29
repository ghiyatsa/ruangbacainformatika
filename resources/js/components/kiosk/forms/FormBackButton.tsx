import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';

export function FormBackButton({ onBack }: { onBack: () => void }) {
    return (
        <Button
            type="button"
            variant="ghost"
            size="sm"
            className="w-fit"
            onClick={onBack}
        >
            <ArrowLeft />
            Kembali
        </Button>
    );
}
