import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { BookActionForm } from './BookActionForm';

export function BorrowForm({
    loanMaxBooks,
    onBack,
}: {
    loanMaxBooks: number;
    onBack: () => void;
}) {
    return (
        <BookActionForm
            action={{
                action: KioskController.borrow.url(),
                method: 'post',
            }}
            submitLabel="Pinjam Buku"
            description={`Maksimal ${loanMaxBooks} buku aktif per anggota.`}
            onBack={onBack}
        />
    );
}
