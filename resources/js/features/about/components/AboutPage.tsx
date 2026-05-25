import { Users } from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { PublicPageSection } from '@/components/layouts/PublicPageSection';

// PENTING: Silakan sesuaikan nama dan NIM tim Kerja Praktik Anda di sini
const DEVELOPMENT_TEAM = [
    { name: 'Nama Mahasiswa 1', nim: '210180001' },
    { name: 'Nama Mahasiswa 2', nim: '210180002' },
    { name: 'Nama Mahasiswa 3', nim: '210180003' },
    { name: 'Nama Mahasiswa 4', nim: '210180004' },
    { name: 'Nama Mahasiswa 5', nim: '210180005' },
    { name: 'Nama Mahasiswa 6', nim: '210180006' },
    { name: 'Nama Mahasiswa 7', nim: '210180007' },
    { name: 'Nama Mahasiswa 8', nim: '210180008' },
    { name: 'Nama Mahasiswa 9', nim: '210180009' },
    { name: 'Nama Mahasiswa 10', nim: '210180010' },
    { name: 'Nama Mahasiswa 11', nim: '210180011' },
    { name: 'Nama Mahasiswa 12', nim: '210180012' },
];

export function AboutPage() {
    const [coordinator, ...members] = DEVELOPMENT_TEAM;

    return (
        <PageLayout
            title="Tentang Ruang Baca"
            metaDescription="Profil Ruang Baca Teknik Informatika Universitas Malikussaleh."
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
                    description="Ruang Baca mendukung pembelajaran dan riset di Program Studi Teknik Informatika Universitas Malikussaleh."
                />
            }
        >
            <div className="space-y-12">
                <PublicPageSection
                    title="Tim Pengembang"
                    description="Dikembangkan secara kolaboratif dan terus disempurnakan untuk mendukung layanan digital Ruang Baca."
                >
                    <div className="overflow-hidden rounded-3xl border border-border/40 bg-card/50 shadow-sm backdrop-blur-xs">
                        <div className="relative aspect-video w-full bg-linear-to-b from-muted/50 to-muted/80 sm:aspect-21/9">
                            <img
                                src="/images/placeholder-team.jpg"
                                alt="Foto bersama tim pengembang"
                                className="absolute inset-0 h-full w-full object-cover opacity-90 grayscale transition-all duration-500 hover:scale-102 hover:grayscale-0"
                                onError={(event) => {
                                    event.currentTarget.style.display = 'none';
                                    event.currentTarget.nextElementSibling?.classList.remove(
                                        'hidden',
                                    );
                                }}
                            />
                            <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-linear-to-b from-primary/5 to-primary/10 text-muted-foreground">
                                <div className="flex size-14 items-center justify-center rounded-2xl border bg-background/80 shadow-xs backdrop-blur-xs">
                                    <Users className="size-6 text-primary" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-4 pt-2">
                        <div className="flex justify-center">
                            <div className="w-full max-w-sm rounded-3xl border border-primary/20 bg-linear-to-br from-primary/8 via-card to-card px-5 py-5 text-center shadow-sm">
                                <div className="space-y-2">
                                    <p className="text-[11px] font-semibold tracking-[0.2em] text-primary uppercase">
                                        Koordinator
                                    </p>
                                    <h3 className="text-sm font-semibold text-foreground">
                                        {coordinator.name}
                                    </h3>
                                    <p className="font-mono text-xs text-muted-foreground">
                                        NIM {coordinator.nim}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {members.map((member) => (
                                <div
                                    key={member.nim}
                                    className="rounded-3xl border border-border/50 bg-card/60 px-4 py-4 text-center shadow-xs transition-colors hover:border-primary/20 hover:bg-primary/5"
                                >
                                    <div className="space-y-1">
                                        <h3 className="text-sm font-semibold text-foreground">
                                            {member.name}
                                        </h3>
                                        <p className="font-mono text-xs text-muted-foreground">
                                            NIM {member.nim}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </PublicPageSection>
            </div>
        </PageLayout>
    );
}
