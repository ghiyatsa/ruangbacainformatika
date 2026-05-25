import { Form, usePage } from '@inertiajs/react';
import { Clock3, Mail, MapPin, Phone } from 'lucide-react';
import ContactMessageController from '@/actions/App/Http/Controllers/ContactMessageController';
import InputError from '@/components/common/InputError';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { PublicInfoCard } from '@/components/layouts/PublicInfoCard';
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
                            Hubungi{' '}
                            <span className="bg-linear-to-r from-primary to-primary/75 bg-clip-text text-transparent">
                                Kami
                            </span>
                        </>
                    }
                    description="Kontak resmi Ruang Baca Teknik Informatika Universitas Malikussaleh."
                    contentClassName="max-w-3xl"
                />
            }
        >
            <div className="space-y-10">
                <div className="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                    <Card className="overflow-hidden border-border/60 bg-card/95 shadow-sm">
                        <CardHeader className="border-b border-border/50 bg-muted/10">
                            <CardTitle>Kirim pesan</CardTitle>
                            <CardDescription>
                                Sampaikan pertanyaan atau kebutuhan bantuan
                                terkait layanan Ruang Baca.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-6">
                            <Form
                                action={ContactMessageController.store()}
                                resetOnSuccess
                                className="space-y-5"
                            >
                                {({
                                    processing,
                                    errors,
                                    recentlySuccessful,
                                }) => (
                                    <>
                                        <div className="grid gap-5 sm:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="contact_name">
                                                    Nama
                                                </Label>
                                                <Input
                                                    id="contact_name"
                                                    name="name"
                                                    required
                                                    defaultValue={
                                                        user?.name ?? ''
                                                    }
                                                    placeholder="Nama lengkap"
                                                />
                                                <InputError
                                                    message={errors.name}
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="contact_email">
                                                    Email
                                                </Label>
                                                <Input
                                                    id="contact_email"
                                                    type="email"
                                                    name="email"
                                                    required
                                                    defaultValue={
                                                        user?.email ?? ''
                                                    }
                                                    placeholder="nama@email.ac.id"
                                                />
                                                <InputError
                                                    message={errors.email}
                                                />
                                            </div>
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="contact_phone">
                                                Nomor telepon
                                            </Label>
                                            <Input
                                                id="contact_phone"
                                                name="phone"
                                                placeholder="Opsional"
                                            />
                                            <InputError
                                                message={errors.phone}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="contact_subject">
                                                Subjek
                                            </Label>
                                            <Input
                                                id="contact_subject"
                                                name="subject"
                                                required
                                                placeholder="Contoh: Akses akun"
                                            />
                                            <InputError
                                                message={errors.subject}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="contact_message">
                                                Pesan
                                            </Label>
                                            <Textarea
                                                id="contact_message"
                                                name="message"
                                                required
                                                rows={7}
                                                minLength={20}
                                                placeholder="Tuliskan kebutuhan atau pertanyaan Anda."
                                            />
                                            <InputError
                                                message={errors.message}
                                            />
                                        </div>

                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            {recentlySuccessful ? (
                                                <p className="text-sm font-medium text-green-600 dark:text-green-400">
                                                    Pesan berhasil dikirim.
                                                </p>
                                            ) : (
                                                <p className="text-sm text-muted-foreground">
                                                    Kami akan menindaklanjuti
                                                    pesan Anda melalui kontak
                                                    yang tersedia.
                                                </p>
                                            )}

                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                {processing
                                                    ? 'Mengirim...'
                                                    : 'Kirim pesan'}
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>

                    <div className="space-y-5">
                        <PublicInfoCard
                            title="Alamat layanan"
                            icon={MapPin}
                            tone="accent"
                        >
                            Kampus Bukit Indah
                            <br />
                            Program Studi Teknik Informatika
                            <br />
                            Universitas Malikussaleh
                            <br />
                            Lhokseumawe, Aceh
                        </PublicInfoCard>

                        <PublicInfoCard title="Email resmi" icon={Mail}>
                            info.tif@unimal.ac.id
                            <br />
                            ruangbaca.tif@unimal.ac.id
                        </PublicInfoCard>

                        <PublicInfoCard title="Jam layanan" icon={Clock3}>
                            Hari kerja pada jam operasional program studi.
                        </PublicInfoCard>
                    </div>
                </div>
            </div>
        </PageLayout>
    );
}
