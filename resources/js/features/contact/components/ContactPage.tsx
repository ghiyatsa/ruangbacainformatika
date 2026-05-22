import { Form, usePage } from '@inertiajs/react';
import {
    Clock3,
    Mail,
    MapPin,
    MessageSquareText,
    Phone,
    Send,
    User,
} from 'lucide-react';
import ContactMessageController from '@/actions/App/Http/Controllers/ContactMessageController';
import InputError from '@/components/common/InputError';
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
    const user = usePage().props.auth?.user;

    return (
        <PageLayout
            title="Hubungi Kami"
            metaDescription="Hubungi Ruang Baca Teknik Informatika Universitas Malikussaleh untuk pertanyaan layanan, koleksi, dan akses akun."
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    title={
                        <>
                            Kami Siap{' '}
                            <span className="bg-linear-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                                Membantu
                            </span>
                        </>
                    }
                    description="Hubungi kami untuk pertanyaan layanan, koleksi, atau akses akun."
                />
            }
        >
            <div className="grid grid-cols-1 gap-8 lg:grid-cols-[1.05fr_0.95fr]">
                <div className="space-y-6">
                    <Card className="border-border/60 bg-card/90 shadow-sm">
                        <CardHeader>
                            <CardTitle>Informasi Kontak</CardTitle>
                            <CardDescription>
                                Gunakan kontak resmi berikut untuk informasi dan
                                layanan.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 text-sm leading-7 text-muted-foreground">
                            <p>
                                Untuk pertanyaan umum, gunakan email resmi
                                program studi atau pengelola ruang baca.
                            </p>
                            <p>
                                Jika terkait akun, peminjaman, atau data,
                                sertakan nama dan identitas akademik Anda.
                            </p>
                            <p>
                                Gunakan pesan yang singkat dan jelas agar tindak
                                lanjut lebih cepat.
                            </p>
                        </CardContent>
                    </Card>

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

                <Card className="border-border/60 bg-card/95 shadow-sm">
                    <CardHeader>
                        <CardTitle>Kirim Pesan</CardTitle>
                        <CardDescription>
                            Sampaikan pertanyaan atau kendala melalui form ini.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            action={ContactMessageController.store()}
                            resetOnSuccess
                            className="space-y-5"
                        >
                            {({ processing, errors, recentlySuccessful }) => (
                                <>
                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label
                                                htmlFor="contact_name"
                                                className="flex items-center gap-1.5"
                                            >
                                                <User className="size-3.5 text-muted-foreground" />
                                                Nama
                                            </Label>
                                            <Input
                                                id="contact_name"
                                                name="name"
                                                required
                                                defaultValue={user?.name ?? ''}
                                                placeholder="Nama lengkap"
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label
                                                htmlFor="contact_email"
                                                className="flex items-center gap-1.5"
                                            >
                                                <Mail className="size-3.5 text-muted-foreground" />
                                                Email
                                            </Label>
                                            <Input
                                                id="contact_email"
                                                type="email"
                                                name="email"
                                                required
                                                defaultValue={user?.email ?? ''}
                                                placeholder="nama@email.com"
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor="contact_phone"
                                            className="flex items-center gap-1.5"
                                        >
                                            <Phone className="size-3.5 text-muted-foreground" />
                                            Nomor telepon
                                        </Label>
                                        <Input
                                            id="contact_phone"
                                            name="phone"
                                            placeholder="Opsional"
                                        />
                                        <InputError message={errors.phone} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor="contact_subject"
                                            className="flex items-center gap-1.5"
                                        >
                                            <MessageSquareText className="size-3.5 text-muted-foreground" />
                                            Subjek
                                        </Label>
                                        <Input
                                            id="contact_subject"
                                            name="subject"
                                            required
                                            placeholder="Contoh: Kendala akses akun"
                                        />
                                        <InputError message={errors.subject} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor="contact_message"
                                            className="flex items-center gap-1.5"
                                        >
                                            <Send className="size-3.5 text-muted-foreground" />
                                            Pesan
                                        </Label>
                                        <Textarea
                                            id="contact_message"
                                            name="message"
                                            required
                                            rows={7}
                                            minLength={20}
                                            placeholder="Tulis pesan Anda secara singkat dan jelas."
                                        />
                                        <InputError message={errors.message} />
                                    </div>

                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <p className="text-sm text-muted-foreground">
                                            Pesan akan diterima tim pengelola.
                                        </p>

                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Mengirim...'
                                                : 'Kirim pesan'}
                                        </Button>
                                    </div>

                                    {recentlySuccessful ? (
                                        <p className="text-sm font-medium text-green-600 dark:text-green-400">
                                            Pesan berhasil dikirim.
                                        </p>
                                    ) : null}
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </PageLayout>
    );
}
