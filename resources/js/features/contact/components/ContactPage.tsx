import { Mail, MapPin, Phone } from 'lucide-react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

export function ContactPage() {
    return (
        <PageLayout
            title="Hubungi Kami"
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
                    description="Punya pertanyaan tentang koleksi, akses akun, atau layanan perpustakaan? Kirim pesan atau hubungi kami melalui kanal yang tersedia."
                />
            }
        >
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                <Card className="h-full border-border/60 bg-card/90 shadow-sm">
                    <CardHeader>
                        <CardTitle>Kirim Pesan</CardTitle>
                        <CardDescription>
                            Isi formulir berikut dan kami akan merespons
                            sesegera mungkin.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form
                            className="space-y-4"
                            onSubmit={(event) => event.preventDefault()}
                        >
                            <div className="space-y-2">
                                <Label htmlFor="name">Nama Lengkap</Label>
                                <Input
                                    id="name"
                                    placeholder="Masukkan nama lengkap Anda"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="email">Alamat Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="Masukkan alamat email Anda"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="subject">Subjek</Label>
                                <Input
                                    id="subject"
                                    placeholder="Pesan ini tentang apa?"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="message">Pesan</Label>
                                <Textarea
                                    id="message"
                                    placeholder="Tulis pesan Anda di sini..."
                                    className="min-h-[120px]"
                                />
                            </div>
                            <Button type="submit" className="w-full">
                                Kirim Pesan
                            </Button>
                        </form>
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
                                <Phone className="h-6 w-6" />
                            </div>
                            <div>
                                <h3 className="mb-1 text-lg font-semibold text-foreground">
                                    Telepon Kami
                                </h3>
                                <p className="text-muted-foreground">
                                    +62 123 4567 8900
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </PageLayout>
    );
}
