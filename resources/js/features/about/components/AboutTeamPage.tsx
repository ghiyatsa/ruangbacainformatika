import { Link } from '@inertiajs/react';
import { ArrowRight, Mail } from 'lucide-react';
import { useState } from 'react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { Button } from '@/components/ui/button';
import { contact } from '@/routes';

interface TeamMember {
    id: string;
    name: string;
    role: string;
    x: number; // percentage from left (0 - 100)
    y: number; // percentage from top (0 - 100)
}

const TEAM_MEMBERS: TeamMember[] = [
    {
        id: '1',
        name: 'Said',
        role: 'Koordinator',
        x: 31,
        y: 65,
    },
    {
        id: '2',
        name: 'Anisah',
        role: 'Anggota',
        x: 60,
        y: 65,
    },
    {
        id: '3',
        name: 'Ayu',
        role: 'Anggota',
        x: 43,
        y: 65,
    },
    {
        id: '4',
        name: 'Nafis',
        role: 'Anggota',
        x: 82,
        y: 46,
    },
    {
        id: '5',
        name: 'Ichsan',
        role: 'Anggota',
        x: 62,
        y: 41,
    },
    {
        id: '6',
        name: 'Zulfathan',
        role: 'Anggota',
        x: 89,
        y: 60,
    },
    {
        id: '7',
        name: 'Ibnu',
        role: 'Anggota',
        x: 16,
        y: 62,
    },
    {
        id: '8',
        name: 'Elandri',
        role: 'Anggota',
        x: 51,
        y: 39,
    },
    {
        id: '9',
        name: 'Shobi',
        role: 'Anggota',
        x: 24,
        y: 40,
    },
    {
        id: '10',
        name: 'Aiman',
        role: 'Anggota',
        x: 71,
        y: 42,
    },
    {
        id: '11',
        name: 'Rahmad',
        role: 'Anggota',
        x: 38,
        y: 40,
    },
    {
        id: '12',
        name: 'Taufiq',
        role: 'Anggota',
        x: 74,
        y: 63,
    },
];

export function AboutTeamPage() {
    const [activeId, setActiveId] = useState<string | null>(null);

    return (
        <PageLayout
            title="Tentang Tim"
            metaDescription="Foto bersama tim mahasiswa kerja praktek pengembang aplikasi Ruang Baca Teknik Informatika Universitas Malikussaleh."
            maxWidth="4xl"
            header={
                <LibraryPageHero
                    title={
                        <>
                            Tim{' '}
                            <span className="bg-linear-to-r from-primary to-primary/75 bg-clip-text text-transparent">
                                Kerja Praktek
                            </span>
                        </>
                    }
                    description="Tim mahasiswa kerja praktek yang membangun dan mengembangkan sistem Ruang Baca Teknik Informatika."
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
                            src="/images/team-photo.jpg"
                            alt="Foto Bersama Tim Ruang Baca"
                            className="h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.01]"
                        />

                        {/* Interactive Hotspots (Invisible triggers) */}
                        {TEAM_MEMBERS.map((member) => (
                            <button
                                key={`hotspot-${member.id}`}
                                className="absolute z-20 size-16 -translate-x-1/2 -translate-y-1/2 cursor-pointer rounded-full bg-transparent focus:outline-hidden"
                                style={{
                                    left: `${member.x}%`,
                                    top: `${member.y}%`,
                                }}
                                onMouseEnter={() => setActiveId(member.id)}
                                onMouseLeave={() => setActiveId(null)}
                                onClick={() =>
                                    setActiveId(
                                        activeId === member.id
                                            ? null
                                            : member.id,
                                    )
                                }
                                aria-label={`Lihat info ${member.name}`}
                            />
                        ))}

                        {/* Floating Tooltips */}
                        {TEAM_MEMBERS.map((member) => (
                            <div
                                key={`tooltip-${member.id}`}
                                className="absolute z-30 transition-all duration-300 ease-out"
                                style={{
                                    left: `${member.x}%`,
                                    top: `${member.y}%`,
                                    transform: `translate(-50%, ${activeId === member.id ? 'calc(-100% - 10px)' : 'calc(-100% - 2px)'})`,
                                    pointerEvents: 'none',
                                    opacity: activeId === member.id ? 1 : 0,
                                    visibility:
                                        activeId === member.id
                                            ? 'visible'
                                            : 'hidden',
                                }}
                            >
                                <div className="relative rounded-xl border border-border/80 bg-background/95 px-3 py-2 text-center shadow-lg backdrop-blur-md">
                                    <p className="text-xs font-bold whitespace-nowrap text-foreground">
                                        {member.name}
                                    </p>
                                    <p className="text-[10px] font-medium whitespace-nowrap text-primary">
                                        {member.role}
                                    </p>
                                    {/* Perfectly Centered Arrow */}
                                    <div className="absolute top-[calc(100%-5px)] left-1/2 z-[-1] h-2.5 w-2.5 -translate-x-1/2 rotate-45 border-r border-b border-border/80 bg-background/95" />
                                </div>
                            </div>
                        ))}

                        {/* Overlay caption */}
                        <div className="pointer-events-none absolute inset-x-0 bottom-0 bg-linear-to-t from-black/85 via-black/40 to-transparent p-6 pt-12 text-white">
                            <p className="text-sm font-semibold tracking-wide uppercase opacity-90 sm:text-base">
                                Tim Kerja Praktek
                            </p>
                            <p className="mt-1 text-xs opacity-75 sm:text-sm">
                                Ruang Baca Teknik Informatika Universitas
                                Malikussaleh
                            </p>
                        </div>
                    </div>
                </div>

                {/* Team Members List / Cards */}
                <div className="space-y-4">
                    <h3 className="text-lg font-bold text-foreground">
                        Daftar Anggota Tim
                    </h3>
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        {TEAM_MEMBERS.map((member) => (
                            <div
                                key={`card-${member.id}`}
                                className={`cursor-pointer rounded-2xl border p-4 text-center transition-all duration-300 ${
                                    activeId === member.id
                                        ? 'border-primary bg-primary/5 shadow-md ring-1 shadow-primary/5 ring-primary'
                                        : 'border-border bg-card hover:border-primary/30 hover:bg-muted/30'
                                }`}
                                onMouseEnter={() => setActiveId(member.id)}
                                onMouseLeave={() => setActiveId(null)}
                            >
                                <p className="text-sm leading-tight font-bold text-foreground">
                                    {member.name}
                                </p>
                                <p className="mt-1 text-xs font-medium text-muted-foreground">
                                    {member.role}
                                </p>
                            </div>
                        ))}
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
                                Ada pertanyaan atau butuh bantuan?
                            </h2>
                            <p className="max-w-2xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                                Untuk usulan buku, masukan layanan, atau
                                pertanyaan umum, tim kami siap membantu.
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
