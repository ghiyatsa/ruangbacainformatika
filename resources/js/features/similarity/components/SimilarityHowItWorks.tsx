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
        desc: 'Ketikkan draf judul skripsi lengkap yang ingin Anda ajukan.',
        icon: FileText,
        color: 'text-primary bg-primary/10 border-primary/20',
    },
    {
        title: 'Pemindaian Semantik',
        desc: 'Sistem membandingkan kemiripan makna judul terhadap ribuan skripsi yang terindeks.',
        icon: Search,
        color: 'text-primary bg-primary/10 border-primary/20',
    },
    {
        title: 'Evaluasi & Tindak Lanjut',
        desc: 'Tinjau skor kemiripan dan sesuaikan judul Anda jika diperlukan sebelum proposal diajukan.',
        icon: CheckCircle,
        color: 'text-primary bg-primary/10 border-primary/20',
    },
];

const TIPS = [
    {
        title: 'Gunakan Teknik Parafrase',
        desc: 'Ubah susunan kalimat atau gunakan sinonim kata tanpa mengubah esensi dan makna penelitian Anda.',
        icon: Shuffle,
    },
    {
        title: 'Tentukan Studi Kasus Spesifik',
        desc: 'Batasi cakupan objek penelitian Anda pada instansi, lokasi, atau dataset khusus yang belum pernah diteliti.',
        icon: Target,
    },
    {
        title: 'Terapkan Variasi/Kombinasi Metode',
        desc: 'Gunakan metode atau kombinasi algoritma yang berbeda untuk membedakannya dari skripsi pendahulu.',
        icon: Layers,
    },
];

export function SimilarityHowItWorks() {
    return (
        <div className="space-y-8">
            {/* Steps Timeline Grid */}
            <div className="relative grid gap-6 md:grid-cols-3">
                {STEPS.map(({ title, desc, icon: Icon, color }, i) => (
                    <div
                        key={i}
                        className="group relative rounded-2xl border border-border/60 bg-card p-6 shadow-xs transition-all duration-300 hover:border-primary/25 hover:shadow-md"
                    >
                        {/* Connecting line for larger screens */}
                        {i < STEPS.length - 1 && (
                            <div className="absolute top-10 -right-3 z-10 hidden h-[1px] w-6 bg-border/80 group-hover:bg-primary/20 md:block" />
                        )}

                        <div className="flex items-center justify-between">
                            <span className="text-[10px] font-black tracking-widest text-muted-foreground/60 uppercase">
                                LANGKAH {String(i + 1).padStart(2, '0')}
                            </span>
                            <div className={`rounded-xl border p-2 ${color}`}>
                                <Icon className="size-4" />
                            </div>
                        </div>

                        <h4 className="mt-4 text-sm font-bold text-foreground transition-colors group-hover:text-primary">
                            {title}
                        </h4>

                        <p className="mt-2 text-xs leading-relaxed text-muted-foreground">
                            {desc}
                        </p>
                    </div>
                ))}
            </div>

            {/* Tips Section */}
            <div className="space-y-4 rounded-2xl border border-primary/15 bg-primary/5 p-6">
                <h4 className="flex items-center gap-2 text-sm font-bold text-primary">
                    <Lightbulb className="size-4 animate-pulse text-primary" />
                    Tips Menghindari Tingkat Kemiripan Tinggi (Plagiasi)
                </h4>

                <div className="grid gap-5 pt-1 sm:grid-cols-3">
                    {TIPS.map(({ title, desc, icon: Icon }, idx) => (
                        <div key={idx} className="space-y-2">
                            <div className="flex items-center gap-2 text-primary">
                                <Icon className="size-4 shrink-0" />
                                <h5 className="text-xs font-bold">{title}</h5>
                            </div>
                            <p className="pl-6 text-[11px] leading-relaxed text-muted-foreground">
                                {desc}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
