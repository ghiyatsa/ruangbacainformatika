import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { BookActionForm } from './BookActionForm';

export function BorrowForm({ loanMaxBooks }: { loanMaxBooks: number }) {
    return (
        <BookActionForm
            action={{
                action: KioskController.borrow.url(),
                method: 'post',
            }}
            submitLabel="Pinjam Buku"
            description={`Maksimal ${loanMaxBooks} buku aktif per anggota.`}
            maxInputs={loanMaxBooks}
        />
    );
}
