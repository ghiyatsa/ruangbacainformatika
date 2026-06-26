import {
    Lightbulb,
    Search,
    FileText,
    CheckCircle,
    Shuffle,
    Target,
    Layers,
} from 'lucide-react';

const STEPS = [
    {
        title: 'Input Judul Skripsi',
        desc: 'Ketik judul skripsi yang ingin Anda periksa.',
        icon: FileText,
    },
    {
        title: 'Pemindaian Semantik',
        desc: 'Sistem membandingkan judul Anda dengan data skripsi yang tersedia.',
        icon: Search,
    },
    {
        title: 'Evaluasi & Tindak Lanjut',
        desc: 'Tinjau hasilnya, lalu sesuaikan judul jika diperlukan.',
        icon: CheckCircle,
    },
];

const TIPS = [
    {
        title: 'Gunakan Teknik Parafrase',
        desc: 'Ubah susunan kalimat atau pilih kata lain tanpa mengubah makna utamanya.',
        icon: Shuffle,
    },
    {
        title: 'Tentukan Studi Kasus Spesifik',
        desc: 'Perjelas objek penelitian, misalnya lokasi, instansi, atau data yang digunakan.',
        icon: Target,
    },
    {
        title: 'Terapkan Variasi/Metode Baru',
        desc: 'Gunakan metode yang berbeda agar judul Anda lebih jelas dan tidak terlalu mirip.',
        icon: Layers,
    },
];

export function SimilarityHowItWorks() {
    return (
        <div className="space-y-8">
            {/* Steps Timeline */}
            <div className="space-y-6">
                <h4 className="text-xs font-bold tracking-widest text-muted-foreground/90 uppercase">
                    Alur Pemeriksaan
                </h4>
                <div className="relative pl-4 border-l border-border/60 space-y-6">
                    {STEPS.map(({ title, desc, icon: Icon }, i) => (
                        <div key={i} className="relative space-y-1">
                            {/* Dot on the timeline */}
                            <div className="absolute -left-[21px] top-1 flex size-2.5 items-center justify-center rounded-full bg-background border border-border" />
                            
                            <div className="flex items-center gap-2">
                                <Icon className="size-3.5 text-primary/70" />
                                <h5 className="text-xs font-bold text-foreground">
                                    {title}
                                </h5>
                            </div>
                            <p className="text-[11px] leading-relaxed text-muted-foreground/90 pl-5.5">
                                {desc}
                            </p>
                        </div>
                    ))}
                </div>
            </div>

            {/* Tips Section */}
            <div className="space-y-4 pt-4 border-t border-border/60">
                <h4 className="flex items-center gap-2 text-xs font-bold tracking-widest text-muted-foreground/90 uppercase">
                    <Lightbulb className="size-3.5 text-yellow-500" />
                    Tips Reduksi Kemiripan
                </h4>

                <div className="space-y-4">
                    {TIPS.map(({ title, desc, icon: Icon }, idx) => (
                        <div key={idx} className="space-y-1">
                            <div className="flex items-center gap-2">
                                <Icon className="size-3.5 text-primary/70" />
                                <h5 className="text-xs font-bold text-foreground">{title}</h5>
                            </div>
                            <p className="pl-5.5 text-[11px] leading-relaxed text-muted-foreground/90">
                                {desc}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
