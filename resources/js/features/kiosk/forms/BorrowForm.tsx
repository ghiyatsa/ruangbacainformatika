import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { BookActionForm } from './BookActionForm';

export function BorrowForm({ loanMaxBooks }: { loanMaxBooks: number }) {
    return (
        <BookActionForm
            action={KioskController.borrow.form()}
            submitLabel="Pinjam Buku"
            description={`Maksimal ${loanMaxBooks} buku aktif per anggota.`}
            maxInputs={loanMaxBooks}
            bookSearchUrl={KioskController.searchBooks.url()}
        />
    );
}
