import { FileSearch, ShieldCheck, Sparkles } from 'lucide-react';

const STEPS = [
    {
        icon: FileSearch,
        title: 'Masukkan Judul',
        desc: 'Ketikkan judul skripsi yang ingin Anda ajukan.',
    },
    {
        icon: Sparkles,
        title: 'Analisis Semantik',
        desc: 'Sistem membandingkan kemiripan secara semantik dengan database.',
    },
    {
        icon: ShieldCheck,
        title: 'Lihat Hasilnya',
        desc: 'Dapatkan laporan kemiripan beserta tingkat risikonya.',
    },
];

export function SimilarityHowItWorks() {
    return (
        <div className="grid gap-4 sm:grid-cols-3">
            {STEPS.map(({ icon: Icon, title, desc }, i) => (
                <div
                    key={i}
                    className="flex flex-col items-center gap-3 rounded-xl border bg-card p-5 text-center shadow-sm"
                >
                    <div className="flex size-10 items-center justify-center rounded-xl bg-primary/10">
                        <Icon className="size-5 text-primary" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold">{title}</p>
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            {desc}
                        </p>
                    </div>
                </div>
            ))}
        </div>
    );
}
