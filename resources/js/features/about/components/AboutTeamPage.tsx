import { Link } from '@inertiajs/react';
import { ArrowRight, Mail } from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { Button } from '@/components/ui/button';
import { contact } from '@/routes';

export function AboutTeamPage() {
    return (
        <PageLayout
            title="Tentang Tim"
            metaDescription="Foto bersama tim pengelola dan pengembang Ruang Baca Teknik Informatika Universitas Malikussaleh."
            maxWidth="4xl"
            header={
                <LibraryPageHero
                    eyebrow="Tim Pengelola"
                    title={
                        <>
                            Tim &{' '}
                            <span className="bg-linear-to-r from-primary to-primary/75 bg-clip-text text-transparent">
                                Pengelola
                            </span>
                        </>
                    }
                    description="Tim yang mengelola layanan dan pengembangan Ruang Baca Teknik Informatika."
                    contentClassName="max-w-4xl"
                    align="center"
                />
            }
        >
            <div className="space-y-12">
                {/* Team Photo Container */}
                <div className="group relative overflow-hidden rounded-[2rem] border border-border/80 bg-card p-3 shadow-xl transition-all duration-300 hover:border-primary/20 hover:shadow-2xl hover:shadow-primary/5">
                    <div className="relative aspect-video overflow-hidden rounded-[1.7rem] bg-muted">
                        <img
                            src="/images/team_photo.png"
                            alt="Foto Bersama Tim Ruang Baca"
                            className="h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.02]"
                        />
                        {/* Overlay caption */}
                        <div className="absolute inset-x-0 bottom-0 bg-linear-to-t from-black/80 via-black/40 to-transparent p-6 pt-12 text-white">
                            <p className="text-sm font-semibold tracking-wide uppercase opacity-90 sm:text-base">
                                Tim Pengelola & Pengembang
                            </p>
                            <p className="mt-1 text-xs opacity-75 sm:text-sm">
                                Ruang Baca Teknik Informatika Universitas
                                Malikussaleh
                            </p>
                        </div>
                    </div>
                </div>

                {/* Call to Action (CTA) Section */}
                <section className="relative overflow-hidden rounded-[2rem] border border-border/60 bg-linear-to-br from-card via-card to-primary/5 p-8 shadow-sm sm:p-10">
                    <div className="absolute -right-12 -bottom-12 -z-10 h-40 w-40 rounded-full bg-primary/10 blur-2xl" />
                    <div className="absolute -top-12 -left-12 -z-10 h-40 w-40 rounded-full bg-primary/5 blur-2xl" />

                    <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div className="space-y-3">
                            <div className="inline-flex items-center gap-2 text-primary">
                                <div className="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                                    <Mail className="size-4" />
                                </div>
                                <span className="text-xs font-bold tracking-[0.12em] uppercase">
                                    Hubungi Kami
                                </span>
                            </div>
                            <h2 className="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                                Ada pertanyaan atau butuh bantuan layanan?
                            </h2>
                            <p className="max-w-2xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                                Untuk usulan buku, masukan layanan, atau
                                pertanyaan umum, tim kami siap membantu melalui
                                halaman kontak.
                            </p>
                        </div>

                        <Button
                            asChild
                            size="lg"
                            className="group rounded-full px-6 shadow-md transition-all duration-200 hover:shadow-lg"
                        >
                            <Link href={contact.url()}>
                                Hubungi Kami
                                <ArrowRight className="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                            </Link>
                        </Button>
                    </div>
                </section>
            </div>
        </PageLayout>
    );
}
