import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { BookActionForm } from './BookActionForm';

export function ReturnForm({ loanMaxBooks }: { loanMaxBooks: number }) {
    return (
        <BookActionForm
            action={KioskController.storeReturn.form()}
            submitLabel="Kembalikan Buku"
            description="Pastikan ISBN sesuai dengan buku yang dibawa."
            maxInputs={loanMaxBooks}
        />
    );
}
