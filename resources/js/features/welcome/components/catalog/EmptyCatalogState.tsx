import { Library } from 'lucide-react';
import { motion } from 'motion/react';

interface EmptyCatalogStateProps {
    title?: string;
    description?: string;
}

export default function EmptyCatalogState({
    title = 'Belum ada koleksi terbaru',
    description = 'Koleksi baru akan tampil di sini.',
}: EmptyCatalogStateProps) {
    return (
        <motion.div
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.4 }}
            className="flex flex-col items-center gap-4 rounded-2xl border border-dashed bg-muted/30 px-6 py-16 text-center"
        >
            <div className="flex size-14 items-center justify-center rounded-full bg-muted">
                <Library className="size-7 text-muted-foreground" />
            </div>
            <div className="flex flex-col gap-1">
                <p className="font-semibold text-foreground">{title}</p>
                <p className="text-sm text-muted-foreground">{description}</p>
            </div>
        </motion.div>
    );
}
