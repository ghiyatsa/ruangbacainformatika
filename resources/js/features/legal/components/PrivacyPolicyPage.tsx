import { Lock, Mail, ShieldCheck } from 'lucide-react';
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

export function PrivacyPolicyPage() {
    return (
        <PageLayout
            title="Kebijakan Privasi"
            metaDescription="Pelajari cara Ruang Baca Teknik Informatika Universitas Malikussaleh menggunakan dan menjaga data pengguna."
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    title="Kebijakan Privasi"
                    description="Ringkasan penggunaan dan perlindungan data pengguna di Ruang Baca Teknik Informatika."
                />
            }
        >
            <div className="space-y-10">
                <div className="grid gap-6 lg:grid-cols-3">
                    <Card className="border-border/60 bg-card/90 shadow-sm lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Prinsip pengelolaan data</CardTitle>
                            <CardDescription>
                                Data digunakan seperlunya untuk akun,
                                peminjaman, dan kebutuhan layanan ruang baca.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6 text-sm leading-7 text-muted-foreground">
                            <section>
                                <h2 className="text-base font-semibold text-foreground">
                                    Data yang digunakan
                                </h2>
                                <p className="mt-2">
                                    Data yang dapat digunakan meliputi identitas
                                    akun, email, nomor WhatsApp, riwayat
                                    peminjaman, serta aktivitas yang terkait
                                    dengan penggunaan layanan ruang baca.
                                </p>
                            </section>

                            <section>
                                <h2 className="text-base font-semibold text-foreground">
                                    Tujuan penggunaan
                                </h2>
                                <p className="mt-2">
                                    Data digunakan untuk autentikasi akun,
                                    proses peminjaman dan pengembalian,
                                    notifikasi layanan, pengelolaan buku, serta
                                    peningkatan mutu layanan.
                                </p>
                            </section>

                            <section>
                                <h2 className="text-base font-semibold text-foreground">
                                    Penyimpanan dan perlindungan
                                </h2>
                                <p className="mt-2">
                                    Kami berupaya menjaga keamanan data melalui
                                    pembatasan akses, pencatatan aktivitas
                                    layanan, dan penggunaan yang seperlunya.
                                </p>
                            </section>

                            <section>
                                <h2 className="text-base font-semibold text-foreground">
                                    Hak pengguna
                                </h2>
                                <p className="mt-2">
                                    Pengguna dapat meminta pembaruan data profil
                                    atau klarifikasi penggunaan data melalui
                                    kontak resmi yang disediakan program studi.
                                </p>
                            </section>
                        </CardContent>
                    </Card>

                    <div className="space-y-6">
                        <PublicInfoCard
                            title="Prinsip utama"
                            icon={ShieldCheck}
                            tone="accent"
                        >
                            <p>Data digunakan secara proporsional.</p>
                            <p>Akses dibatasi untuk kebutuhan layanan.</p>
                            <p>
                                Informasi tidak dipublikasikan tanpa dasar yang
                                sah.
                            </p>
                        </PublicInfoCard>

                        <PublicInfoCard title="Catatan" icon={Lock}>
                            Kebijakan ini dapat diperbarui menyesuaikan
                            kebutuhan layanan dan ketentuan institusi.
                        </PublicInfoCard>

                        <PublicInfoCard title="Kontak" icon={Mail}>
                            informatika@unimal.ac.id
                        </PublicInfoCard>
                    </div>
                </div>

                <PublicPageSection
                    title="Ruang lingkup perlindungan"
                    description="Mencakup data yang diperlukan untuk identitas akun dan kebutuhan layanan."
                >
                    <div className="grid gap-5 md:grid-cols-3">
                        <PublicInfoCard title="Akun pengguna">
                            Data identitas dasar digunakan untuk mengelola akses
                            akun dan memastikan layanan diberikan kepada
                            pengguna yang tepat.
                        </PublicInfoCard>
                        <PublicInfoCard title="Riwayat layanan">
                            Aktivitas peminjaman dan penggunaan fitur dapat
                            dicatat untuk kebutuhan layanan serta evaluasi
                            layanan.
                        </PublicInfoCard>
                        <PublicInfoCard title="Komunikasi resmi">
                            Informasi kontak dipakai seperlunya untuk
                            notifikasi, verifikasi, dan tindak lanjut layanan.
                        </PublicInfoCard>
                    </div>
                </PublicPageSection>
            </div>
        </PageLayout>
    );
}
