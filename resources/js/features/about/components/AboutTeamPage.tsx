import { Link } from '@inertiajs/react';
import { ArrowRight, Users } from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { PublicPageSection } from '@/components/layouts/PublicPageSection';
import { Button } from '@/components/ui/button';
import { contact } from '@/routes';

export function AboutTeamPage() {
    return (
        <PageLayout
            title="Tentang Tim"
            metaDescription="Profil tim pengembang Ruang Baca Teknik Informatika Universitas Malikussaleh."
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    title={
                        <>
                            Tim{' '}
                            <span className="bg-linear-to-r from-primary to-primary/75 bg-clip-text text-transparent">
                                Pengembang
                            </span>
                        </>
                    }
                    description="Halaman khusus yang menampilkan tim pengembang Ruang Baca secara lebih rapi, terpisah dari profil layanan utama."
                    contentClassName="max-w-4xl"
                />
            }
        >
            <div className="space-y-12">
                <PublicPageSection
                    eyebrow="Kolaborasi pengembangan"
                    title="Orang-orang di balik Ruang Baca"
                    description="Tim ini berkolaborasi untuk membangun pengalaman digital yang lebih tertata, mudah diakses, dan sesuai kebutuhan lingkungan akademik."
                >
                    <div className="overflow-hidden rounded-[2rem] border border-border/50 bg-card/80 shadow-sm backdrop-blur-xs">
                        <div className="relative flex min-h-64 items-center justify-center bg-linear-to-br from-primary/8 via-muted/20 to-primary/12 px-6 py-10">
                            <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.28),transparent_45%)]" />
                            <div className="relative z-10 flex max-w-2xl flex-col items-center text-center">
                                <div className="flex size-16 items-center justify-center rounded-3xl border border-primary/15 bg-background/85 shadow-sm backdrop-blur-xs">
                                    <Users className="size-7 text-primary" />
                                </div>
                                <h3 className="mt-5 text-2xl font-semibold tracking-tight text-foreground">
                                    Tim Pengembang Ruang Baca
                                </h3>
                            </div>
                        </div>
                    </div>
                </PublicPageSection>

                <section className="rounded-[2rem] border border-border/50 bg-muted/15 px-6 py-8 shadow-xs">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div className="space-y-2">
                            <h2 className="text-xl font-semibold text-foreground">
                                Ingin terhubung dengan pengelola layanan?
                            </h2>
                            <p className="max-w-2xl text-sm leading-7 text-muted-foreground">
                                Untuk kebutuhan koordinasi, masukan, atau
                                pertanyaan layanan, gunakan halaman kontak resmi
                                Ruang Baca.
                            </p>
                        </div>

                        <Button asChild className="rounded-full px-5">
                            <Link href={contact.url()}>
                                Hubungi Kami
                                <ArrowRight className="size-4" />
                            </Link>
                        </Button>
                    </div>
                </section>
            </div>
        </PageLayout>
    );
}
