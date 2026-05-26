import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    BookMarked,
    Building2,
    GraduationCap,
    Users,
} from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { PublicPageSection } from '@/components/layouts/PublicPageSection';
import { Button } from '@/components/ui/button';
import { aboutTeam, contact } from '@/routes';

const HIGHLIGHTS = [
    {
        title: 'Pusat koleksi akademik',
        description:
            'Mengelola akses ke buku, skripsi, tesis, dan laporan kerja praktik dalam satu alur yang lebih mudah dipahami.',
        icon: BookMarked,
    },
    {
        title: 'Mendukung kegiatan belajar',
        description:
            'Membantu mahasiswa dan dosen menemukan referensi yang relevan untuk perkuliahan, penyusunan tugas akhir, dan penelitian.',
        icon: GraduationCap,
    },
    {
        title: 'Layanan yang terus berkembang',
        description:
            'Platform ini dirancang untuk berkembang bersama kebutuhan program studi, dengan fokus pada pengalaman pengguna yang lebih rapi dan profesional.',
        icon: Building2,
    },
];

const PRINCIPLES = [
    'Akses informasi akademik yang lebih cepat dan terstruktur.',
    'Tampilan layanan publik yang jelas untuk mahasiswa, dosen, dan pengelola.',
    'Pengembangan berkelanjutan melalui kolaborasi antara institusi dan tim teknis.',
];

export function AboutPage() {
    return (
        <PageLayout
            title="Tentang Ruang Baca"
            metaDescription="Profil Ruang Baca Teknik Informatika Universitas Malikussaleh, layanan, fokus, dan arah pengembangannya."
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    title={
                        <>
                            Tentang{' '}
                            <span className="bg-linear-to-r from-primary to-primary/75 bg-clip-text text-transparent">
                                Ruang Baca
                            </span>
                        </>
                    }
                    description="Ruang Baca menjadi ruang layanan akademik digital untuk mendukung akses koleksi, aktivitas belajar, dan kebutuhan riset di Program Studi Teknik Informatika Universitas Malikussaleh."
                    contentClassName="max-w-4xl"
                />
            }
        >
            <div className="space-y-12">
                <PublicPageSection
                    eyebrow="Profil layanan"
                    title="Layanan informasi yang dibuat lebih fokus dan meyakinkan"
                    description="Halaman ini merangkum peran Ruang Baca sebagai wajah layanan akademik digital, sementara profil tim pengembang kini ditempatkan pada halaman terpisah agar struktur informasi terasa lebih profesional."
                    action={
                        <Button asChild size="sm" className="rounded-full px-4">
                            <Link href={aboutTeam.url()}>
                                Lihat tim pengembang
                                <ArrowRight className="size-4" />
                            </Link>
                        </Button>
                    }
                >
                    <div className="grid gap-4 md:grid-cols-3">
                        {HIGHLIGHTS.map(
                            ({ title, description, icon: Icon }) => (
                                <div
                                    key={title}
                                    className="rounded-3xl border border-border/50 bg-card/80 p-6 shadow-xs backdrop-blur-xs"
                                >
                                    <div className="space-y-4">
                                        <div className="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                            <Icon className="size-5" />
                                        </div>
                                        <div className="space-y-2">
                                            <h3 className="text-base font-semibold text-foreground">
                                                {title}
                                            </h3>
                                            <p className="text-sm leading-6 text-muted-foreground">
                                                {description}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            ),
                        )}
                    </div>
                </PublicPageSection>

                <PublicPageSection
                    eyebrow="Arah pengembangan"
                    title="Prinsip yang menjaga layanan tetap relevan"
                    description="Pengembangan Ruang Baca diarahkan agar pengunjung cepat memahami fungsi layanan, menemukan informasi penting, dan percaya pada kualitas pengelolaannya."
                >
                    <div className="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                        <div className="rounded-[2rem] border border-border/50 bg-linear-to-br from-card via-card to-primary/5 p-6 shadow-sm">
                            <div className="space-y-4">
                                <p className="text-sm leading-7 text-muted-foreground">
                                    Ruang Baca tidak hanya menjadi tempat
                                    menampilkan koleksi, tetapi juga bagian dari
                                    pengalaman akademik yang membantu civitas
                                    memahami layanan, prosedur, dan kualitas
                                    dukungan yang tersedia.
                                </p>
                                <p className="text-sm leading-7 text-muted-foreground">
                                    Dengan pemisahan halaman profil layanan dan
                                    halaman tim, informasi institusional menjadi
                                    lebih ringkas, sedangkan apresiasi terhadap
                                    para pengembang tetap hadir dalam ruang yang
                                    tepat.
                                </p>
                            </div>
                        </div>

                        <div className="rounded-[2rem] border border-primary/15 bg-primary/6 p-6 shadow-xs">
                            <div className="flex items-center gap-3">
                                <div className="flex size-11 items-center justify-center rounded-2xl bg-background text-primary shadow-xs">
                                    <Users className="size-5" />
                                </div>
                                <div>
                                    <p className="text-sm font-semibold text-foreground">
                                        Struktur informasi baru
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        Lebih tertata untuk pengunjung umum dan
                                        pihak institusi.
                                    </p>
                                </div>
                            </div>

                            <ul className="mt-5 space-y-3">
                                {PRINCIPLES.map((principle) => (
                                    <li
                                        key={principle}
                                        className="rounded-2xl border border-primary/10 bg-background/80 px-4 py-3 text-sm leading-6 text-muted-foreground"
                                    >
                                        {principle}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </PublicPageSection>

                <section className="overflow-hidden rounded-[2rem] border border-border/50 bg-linear-to-r from-primary/10 via-card to-card shadow-sm">
                    <div className="grid gap-6 px-6 py-8 md:grid-cols-[1fr_auto] md:items-center md:px-8">
                        <div className="space-y-2">
                            <p className="text-sm font-semibold tracking-[0.18em] text-primary uppercase">
                                Informasi tambahan
                            </p>
                            <h2 className="text-2xl font-bold tracking-tight text-foreground">
                                Butuh mengenal tim di balik platform ini?
                            </h2>
                            <p className="max-w-2xl text-sm leading-7 text-muted-foreground">
                                Kunjungi halaman khusus tim pengembang untuk
                                melihat profil kolaborator yang membangun dan
                                menyempurnakan Ruang Baca.
                            </p>
                        </div>

                        <div className="flex flex-col gap-3 sm:flex-row">
                            <Button asChild className="rounded-full px-5">
                                <Link href={aboutTeam.url()}>
                                    Tentang Tim
                                    <ArrowRight className="size-4" />
                                </Link>
                            </Button>
                            <Button
                                asChild
                                variant="outline"
                                className="rounded-full px-5"
                            >
                                <Link href={contact.url()}>Hubungi Kami</Link>
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </PageLayout>
    );
}
