import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { BookActionForm } from './BookActionForm';

export function ReturnForm({ loanMaxBooks }: { loanMaxBooks: number }) {
    return (
        <BookActionForm
            action={KioskController.storeReturn.form()}
            submitLabel="Kembalikan Buku"
            description="Cari pinjaman aktif lalu selesaikan pengembalian."
            maxInputs={loanMaxBooks}
            bookSearchUrl={KioskController.searchBooks.url()}
            bookSearchMode="return"
        />
    );
}
