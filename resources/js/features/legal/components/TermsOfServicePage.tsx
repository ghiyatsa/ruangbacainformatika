import { BookOpen, CircleCheck, Shield } from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const terms = [
    'Gunakan akun dan layanan sesuai keperluan akademik yang sah.',
    'Jaga kerahasiaan akses akun dan hindari penggunaan oleh pihak lain.',
    'Hormati ketentuan peminjaman, pengembalian, dan penggunaan koleksi.',
    'Dilarang menyalahgunakan sistem, data, atau fasilitas pencarian.',
    'Konten dan layanan dapat disesuaikan mengikuti kebijakan program studi.',
];

export function TermsOfServicePage() {
    return (
        <PageLayout
            title="Syarat Layanan"
            metaDescription="Baca syarat layanan Ruang Baca Teknik Informatika Universitas Malikussaleh, termasuk penggunaan akun, koleksi, dan ketentuan operasional perpustakaan."
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    badge={
                        <>
                            <Shield className="size-4 text-primary" />
                            Informasi Layanan
                        </>
                    }
                    title="Syarat Layanan"
                    description="Ketentuan penggunaan Ruang Baca Teknik Informatika bagi sivitas akademika dan pengguna terkait."
                />
            }
        >
            <div className="grid gap-6 lg:grid-cols-[1.4fr_0.8fr]">
                <Card className="border-border/60 bg-card/90 shadow-sm">
                    <CardHeader>
                        <CardTitle>Ketentuan Penggunaan Layanan</CardTitle>
                        <CardDescription>
                            Layanan ini disediakan untuk mendukung pembelajaran,
                            riset, dan administrasi akademik.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {terms.map((term) => (
                            <div
                                key={term}
                                className="flex gap-3 rounded-2xl border bg-muted/20 p-4"
                            >
                                <CircleCheck className="mt-0.5 size-4 shrink-0 text-primary" />
                                <p className="text-sm leading-7 text-muted-foreground">
                                    {term}
                                </p>
                            </div>
                        ))}
                    </CardContent>
                </Card>

                <div className="space-y-6">
                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <BookOpen className="size-4 text-primary" />
                                Cakupan Layanan
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm leading-7 text-muted-foreground">
                            Meliputi pencarian koleksi, akses informasi buku,
                            karya ilmiah, layanan akun, dan fitur pendukung
                            perpustakaan digital.
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Shield className="size-4 text-primary" />
                                Tanggung Jawab
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm leading-7 text-muted-foreground">
                            Pengguna bertanggung jawab atas data akun, kepatuhan
                            penggunaan layanan, dan interaksi yang dilakukan
                            melalui sistem.
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">
                                Catatan
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm text-muted-foreground">
                            <p>Layanan dapat diperbarui mengikuti kebutuhan institusi.</p>
                            <p>Penggunaan fitur tertentu dapat dibatasi untuk alasan operasional.</p>
                            <p>Kebijakan akademik tetap menjadi acuan utama dalam penggunaan layanan.</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </PageLayout>
    );
}
