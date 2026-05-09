import { BookOpen, GraduationCap, LibraryBig } from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

export function AboutPage() {
    return (
        <PageLayout
            title="Tentang Kami"
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    badge={
                        <>
                            <LibraryBig className="size-4 text-primary" />
                            Profil Ruang Baca
                        </>
                    }
                    title={
                        <>
                            Tentang{' '}
                            <span className="bg-linear-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                                Ruang Baca
                            </span>
                        </>
                    }
                    description="Perpustakaan digital Teknik Informatika Universitas Malikussaleh yang dirancang untuk membantu mahasiswa, dosen, dan peneliti menemukan referensi akademik dengan lebih mudah."
                />
            }
        >
            <div className="grid gap-6 lg:grid-cols-[1.35fr_0.85fr]">
                <Card className="border-border/60 bg-card/90 shadow-sm">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <BookOpen className="size-5 text-primary" />
                            Misi Kami
                        </CardTitle>
                        <CardDescription>
                            Membuka akses referensi akademik yang rapi, cepat,
                            dan relevan untuk kebutuhan belajar dan riset.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="prose prose-slate dark:prose-invert max-w-none">
                        <p className="mb-4 text-base leading-relaxed">
                            Ruang Baca adalah perpustakaan digital dan pusat
                            sumber belajar untuk Program Studi Teknik
                            Informatika di Universitas Malikussaleh. Misi kami
                            adalah menyediakan akses yang mudah dan menyeluruh
                            bagi mahasiswa, dosen, dan peneliti terhadap
                            koleksi literatur akademik, termasuk buku ajar,
                            bahan referensi, karya ilmiah, dan koleksi
                            skripsi.
                        </p>
                        <p className="mb-4 text-base leading-relaxed">
                            Kami ingin mendorong budaya belajar berkelanjutan
                            dan riset yang unggul melalui platform modern dan
                            mudah digunakan, sehingga civitas akademika dapat
                            dengan mudah menemukan, meminjam, dan membaca bahan
                            yang relevan untuk studi maupun proyek mereka.
                        </p>
                        <h3 className="mt-6 mb-3 text-xl font-semibold text-foreground">
                            Visi Kami
                        </h3>
                        <p className="mb-0 text-base leading-relaxed">
                            Menjadi repositori digital dan pusat pengetahuan
                            yang unggul untuk mendukung komunitas Teknik
                            Informatika melalui akses yang lancar ke sumber
                            informasi berkualitas tinggi, demi mendorong
                            inovasi dan kemajuan teknologi.
                        </p>
                    </CardContent>
                </Card>

                <div className="grid gap-6">
                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <GraduationCap className="size-5 text-primary" />
                                Koleksi Utama
                            </CardTitle>
                            <CardDescription>
                                Konten yang paling sering digunakan untuk
                                pembelajaran, referensi, dan inspirasi riset.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="rounded-2xl border bg-muted/30 p-4">
                                <p className="font-semibold">Buku</p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Referensi pemrograman, rekayasa perangkat
                                    lunak, jaringan, dan ilmu komputer.
                                </p>
                            </div>
                            <div className="rounded-2xl border bg-muted/30 p-4">
                                <p className="font-semibold">Skripsi</p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Arsip penelitian mahasiswa terdahulu untuk
                                    referensi dan inspirasi topik baru.
                                </p>
                            </div>
                            <div className="rounded-2xl border bg-muted/30 p-4">
                                <p className="font-semibold">Jurnal & Artikel</p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Publikasi akademik pilihan yang relevan
                                    dengan bidang informatika.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </PageLayout>
    );
}
