import { Lock, Mail, ShieldCheck } from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
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
            metaDescription="Pelajari cara Ruang Baca Teknik Informatika Universitas Malikussaleh mengelola data akun, aktivitas layanan, dan perlindungan informasi pengguna."
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    title="Kebijakan Privasi"
                    description="Penjelasan singkat mengenai pengelolaan data pengguna di Ruang Baca Teknik Informatika."
                />
            }
        >
            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="border-border/60 bg-card/90 shadow-sm lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Prinsip Pengelolaan Data</CardTitle>
                        <CardDescription>
                            Kami mengelola data seperlunya untuk layanan akun,
                            peminjaman, dan akses koleksi.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-6 text-sm leading-7 text-muted-foreground">
                        <section>
                            <h2 className="text-base font-semibold text-foreground">
                                Data yang digunakan
                            </h2>
                            <p className="mt-2">
                                Data yang dapat kami gunakan meliputi identitas
                                akun, alamat email, nomor WhatsApp, riwayat
                                peminjaman, dan aktivitas yang terkait dengan
                                layanan perpustakaan.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-base font-semibold text-foreground">
                                Tujuan penggunaan
                            </h2>
                            <p className="mt-2">
                                Data digunakan untuk autentikasi akun, proses
                                peminjaman dan pengembalian, notifikasi layanan,
                                pengelolaan koleksi, serta peningkatan mutu
                                layanan perpustakaan.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-base font-semibold text-foreground">
                                Penyimpanan dan perlindungan
                            </h2>
                            <p className="mt-2">
                                Kami berupaya menjaga keamanan data melalui
                                kontrol akses sistem, pencatatan aktivitas
                                layanan, dan pembatasan penggunaan hanya untuk
                                kepentingan akademik dan operasional yang sah.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-base font-semibold text-foreground">
                                Hak pengguna
                            </h2>
                            <p className="mt-2">
                                Pengguna dapat meminta pembaruan data profil
                                atau klarifikasi penggunaan data melalui kontak
                                resmi program studi.
                            </p>
                        </section>
                    </CardContent>
                </Card>

                <div className="space-y-6">
                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <ShieldCheck className="size-4 text-primary" />
                                Prinsip Utama
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm text-muted-foreground">
                            <p>Data digunakan secara proporsional.</p>
                            <p>Akses dibatasi untuk kebutuhan layanan.</p>
                            <p>
                                Informasi tidak dipublikasikan tanpa dasar yang
                                sah.
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Lock className="size-4 text-primary" />
                                Catatan
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">
                            Kebijakan ini dapat diperbarui menyesuaikan
                            kebutuhan layanan dan ketentuan institusi.
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Mail className="size-4 text-primary" />
                                Kontak
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">
                            informatika@unimal.ac.id
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">
                                Ruang Lingkup
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm text-muted-foreground">
                            <p>Data akun dan identitas pengguna.</p>
                            <p>Riwayat akses layanan dan peminjaman.</p>
                            <p>
                                Informasi yang dibutuhkan untuk operasional
                                perpustakaan.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </PageLayout>
    );
}
