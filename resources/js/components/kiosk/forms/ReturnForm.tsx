import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { BookActionForm } from './BookActionForm';

export function ReturnForm({ onBack }: { onBack: () => void }) {
    return (
        <BookActionForm
            action={{
                action: KioskController.storeReturn.url(),
                method: 'post',
            }}
            submitLabel="Kembalikan Buku"
            description="Pastikan ISBN sesuai dengan buku yang dibawa."
            onBack={onBack}
        />
    );
}
