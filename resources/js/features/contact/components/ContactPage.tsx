import { Clock3, Mail, MapPin } from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

export function ContactPage() {
    return (
        <PageLayout
            title="Hubungi Kami"
            metaDescription="Hubungi Ruang Baca Teknik Informatika Universitas Malikussaleh untuk pertanyaan layanan, koleksi, dan akses akun."
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    badge={
                        <>
                            <Mail className="size-4 text-primary" />
                            Layanan Bantuan
                        </>
                    }
                    title={
                        <>
                            Kami Siap{' '}
                            <span className="bg-linear-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                                Membantu
                            </span>
                        </>
                    }
                    description="Hubungi kami untuk pertanyaan seputar koleksi, akses akun, dan layanan perpustakaan."
                />
            }
        >
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                <Card className="h-full border-border/60 bg-card/90 shadow-sm">
                    <CardHeader>
                        <CardTitle>Informasi Kontak</CardTitle>
                        <CardDescription>
                            Gunakan kanal resmi berikut untuk kebutuhan
                            informasi dan koordinasi layanan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4 text-sm leading-7 text-muted-foreground">
                        <p>
                            Untuk pertanyaan umum, silakan kirim email ke
                            alamat resmi program studi atau pengelola ruang
                            baca.
                        </p>
                        <p>
                            Jika permintaan berkaitan dengan akun, peminjaman,
                            atau pembaruan data, sertakan nama dan identitas
                            akademik Anda agar proses tindak lanjut lebih cepat.
                        </p>
                        <p>
                            Untuk kebutuhan yang bersifat administratif,
                            gunakan bahasa yang singkat dan jelas agar proses
                            verifikasi dapat dilakukan lebih cepat.
                        </p>
                    </CardContent>
                </Card>

                <div className="space-y-6">
                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardContent className="flex items-start gap-4 p-6">
                            <div className="shrink-0 rounded-full bg-primary/10 p-3 text-primary">
                                <MapPin className="h-6 w-6" />
                            </div>
                            <div>
                                <h3 className="mb-1 text-lg font-semibold text-foreground">
                                    Lokasi Kami
                                </h3>
                                <p className="text-muted-foreground">
                                    Kampus Bukit Indah
                                    <br />
                                    Program Studi Teknik Informatika
                                    <br />
                                    Universitas Malikussaleh
                                    <br />
                                    Lhokseumawe, Aceh
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardContent className="flex items-start gap-4 p-6">
                            <div className="shrink-0 rounded-full bg-primary/10 p-3 text-primary">
                                <Mail className="h-6 w-6" />
                            </div>
                            <div>
                                <h3 className="mb-1 text-lg font-semibold text-foreground">
                                    Email Kami
                                </h3>
                                <p className="text-muted-foreground">
                                    info.tif@unimal.ac.id
                                    <br />
                                    ruangbaca.tif@unimal.ac.id
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardContent className="flex items-start gap-4 p-6">
                            <div className="shrink-0 rounded-full bg-primary/10 p-3 text-primary">
                                <Clock3 className="h-6 w-6" />
                            </div>
                            <div>
                                <h3 className="mb-1 text-lg font-semibold text-foreground">
                                    Waktu Layanan
                                </h3>
                                <p className="text-muted-foreground">
                                    Hari kerja sesuai jam operasional program
                                    studi dan perpustakaan.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </PageLayout>
    );
}
