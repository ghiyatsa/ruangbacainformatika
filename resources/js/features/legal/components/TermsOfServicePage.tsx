import { BookOpen, CircleCheck, Shield } from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { PublicInfoCard } from '@/components/layouts/PublicInfoCard';
import { PublicPageSection } from '@/components/layouts/PublicPageSection';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const terms = [
    'Gunakan akun dan layanan sesuai kebutuhan yang sah.',
    'Jaga kerahasiaan akses akun dan hindari penggunaan oleh pihak lain.',
    'Hormati ketentuan peminjaman, pengembalian, dan penggunaan buku.',
    'Dilarang menyalahgunakan layanan, data, atau fasilitas pencarian.',
    'Konten dan layanan dapat disesuaikan mengikuti kebijakan program studi.',
];

export function TermsOfServicePage() {
    return (
        <PageLayout
            title="Syarat Layanan"
            metaDescription="Baca syarat penggunaan layanan Ruang Baca Teknik Informatika Universitas Malikussaleh."
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    title="Syarat Layanan"
                    description="Ketentuan penggunaan layanan Ruang Baca Teknik Informatika."
                />
            }
        >
            <div className="space-y-10">
                <div className="grid gap-6 lg:grid-cols-[1.4fr_0.8fr]">
                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle>Ketentuan penggunaan layanan</CardTitle>
                            <CardDescription>
                                Layanan ini disediakan untuk mendukung
                                kegiatan belajar dan penggunaan ruang baca.
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
                        <PublicInfoCard
                            title="Cakupan layanan"
                            icon={BookOpen}
                            tone="accent"
                        >
                            Meliputi pencarian buku, karya ilmiah, layanan
                            akun, dan fitur pendukung ruang baca.
                        </PublicInfoCard>

                        <PublicInfoCard title="Tanggung jawab" icon={Shield}>
                            Pengguna bertanggung jawab atas keamanan akun,
                            kepatuhan penggunaan layanan, dan aktivitas melalui
                            akun.
                        </PublicInfoCard>
                    </div>
                </div>

                <PublicPageSection
                    title="Catatan penting"
                    description="Ketentuan ini mengikuti kebijakan program studi dan dapat diperbarui sesuai kebutuhan layanan."
                >
                    <div className="grid gap-5 md:grid-cols-3">
                        <PublicInfoCard title="Pembaruan layanan">
                            Layanan dapat diperbarui mengikuti kebutuhan
                            institusi dan penyempurnaan layanan.
                        </PublicInfoCard>
                        <PublicInfoCard title="Batasan fitur">
                            Penggunaan fitur tertentu dapat dibatasi untuk
                            alasan keamanan, layanan, atau kepatuhan internal.
                        </PublicInfoCard>
                        <PublicInfoCard title="Acuan utama">
                            Kebijakan akademik program studi tetap menjadi
                            acuan utama dalam penggunaan Ruang Baca.
                        </PublicInfoCard>
                    </div>
                </PublicPageSection>
            </div>
        </PageLayout>
    );
}
