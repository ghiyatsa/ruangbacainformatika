import { Form, usePage } from '@inertiajs/react';
import { Mail, User, Phone, Tag, MessageSquare } from 'lucide-react';
import ContactMessageController from '@/actions/App/Http/Controllers/ContactMessageController';
import InputError from '@/components/common/InputError';
import { PageLayout } from '@/components/layout/PageLayout';
import { PublicPageHero } from '@/components/layout/PublicPageHero';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
    InputGroupTextarea,
} from '@/components/ui/input-group';
import { Label } from '@/components/ui/label';

export function ContactPage() {
    const user = usePage().props.auth?.user;

    return (
        <PageLayout
            title="Hubungi Kami"
            metaDescription="Hubungi Ruang Baca Teknik Informatika Universitas Malikussaleh untuk pertanyaan buku, layanan, dan akses akun."
            maxWidth="5xl"
            showDesktopNoticeInContent={false}
            header={
                <PublicPageHero
                    title={
                        <>
                            Hubungi{' '}
                            <span className="bg-linear-to-r from-primary to-primary/75 bg-clip-text text-transparent">
                                Kami
                            </span>
                        </>
                    }
                    description="Informasi kontak Ruang Baca Teknik Informatika Universitas Malikussaleh."
                    contentClassName="max-w-5xl px-4 sm:px-6 lg:px-8"
                />
            }
        >
            <div className="space-y-10">
                <div className="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                    <Card className="h-fit overflow-hidden border-border/60 bg-card/95 py-0! pt-6! pb-0 shadow-none">
                        <CardHeader className="border-b border-border/50 bg-muted/10">
                            <CardTitle>Kirim pesan</CardTitle>
                            <CardDescription>
                                Sampaikan pertanyaan atau kebutuhan bantuan
                                terkait Ruang Baca.
                            </CardDescription>
                        </CardHeader>
                        <Form
                            action={ContactMessageController.store()}
                            resetOnSuccess
                            className="flex flex-col"
                        >
                            {({ processing, errors, recentlySuccessful }) => (
                                <>
                                    <CardContent className="space-y-5 pt-0">
                                        <div className="grid gap-5 sm:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="contact_name">
                                                    Nama
                                                </Label>
                                                <InputGroup>
                                                    <InputGroupInput
                                                        id="contact_name"
                                                        name="name"
                                                        required
                                                        defaultValue={
                                                            user?.name ?? ''
                                                        }
                                                        placeholder="Nama lengkap Anda"
                                                    />
                                                    <InputGroupAddon>
                                                        <User className="size-4" />
                                                    </InputGroupAddon>
                                                </InputGroup>
                                                <InputError
                                                    message={errors.name}
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="contact_email">
                                                    Email
                                                </Label>
                                                <InputGroup>
                                                    <InputGroupInput
                                                        id="contact_email"
                                                        type="email"
                                                        name="email"
                                                        required
                                                        defaultValue={
                                                            user?.email ?? ''
                                                        }
                                                        placeholder="alamat@email.ac.id"
                                                    />
                                                    <InputGroupAddon>
                                                        <Mail className="size-4" />
                                                    </InputGroupAddon>
                                                </InputGroup>
                                                <InputError
                                                    message={errors.email}
                                                />
                                            </div>
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="contact_phone">
                                                Nomor telepon
                                            </Label>
                                            <InputGroup>
                                                <InputGroupInput
                                                    id="contact_phone"
                                                    name="phone"
                                                    placeholder="Opsional"
                                                />
                                                <InputGroupAddon>
                                                    <Phone className="size-4" />
                                                </InputGroupAddon>
                                            </InputGroup>
                                            <InputError
                                                message={errors.phone}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="contact_subject">
                                                Subjek
                                            </Label>
                                            <InputGroup>
                                                <InputGroupInput
                                                    id="contact_subject"
                                                    name="subject"
                                                    required
                                                    placeholder="Contoh: Permohonan akses akun"
                                                />
                                                <InputGroupAddon>
                                                    <Tag className="size-4" />
                                                </InputGroupAddon>
                                            </InputGroup>
                                            <InputError
                                                message={errors.subject}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="contact_message">
                                                Pesan
                                            </Label>
                                            <InputGroup>
                                                <InputGroupTextarea
                                                    id="contact_message"
                                                    name="message"
                                                    required
                                                    rows={7}
                                                    minLength={20}
                                                    placeholder="Tuliskan pertanyaan atau kebutuhan Anda."
                                                />
                                                <InputGroupAddon className="self-start pt-2.5">
                                                    <MessageSquare className="size-4" />
                                                </InputGroupAddon>
                                            </InputGroup>
                                            <InputError
                                                message={errors.message}
                                            />
                                        </div>
                                    </CardContent>
                                    <CardFooter className="flex flex-col gap-3 border-t border-border/50 bg-muted/5 pt-6 pb-6 sm:flex-row sm:items-center sm:justify-between">
                                        <div className="flex-1">
                                            {recentlySuccessful && (
                                                <p className="text-sm font-medium text-green-600 dark:text-green-400">
                                                    Pesan telah terkirim.
                                                </p>
                                            )}
                                        </div>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Mengirim...'
                                                : 'Kirim pesan'}
                                        </Button>
                                    </CardFooter>
                                </>
                            )}
                        </Form>
                    </Card>

                    <div className="space-y-4">
                        <div className="rounded-3xl border border-primary/20 bg-linear-to-br from-primary/8 via-card to-card p-6 shadow-none">
                            <h3 className="mb-3 text-base font-semibold text-foreground">
                                Alamat layanan
                            </h3>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                Kampus Bukit Indah
                                <br />
                                Program Studi Teknik Informatika
                                <br />
                                Universitas Malikussaleh
                                <br />
                                Lhokseumawe, Aceh
                            </p>
                        </div>

                        <div className="rounded-3xl border border-border/60 bg-card p-6 shadow-none">
                            <h3 className="mb-3 text-base font-semibold text-foreground">
                                Email resmi
                            </h3>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                informatika@unimal.ac.id
                                <br />
                                ruangbacainformatika@unimal.ac.id
                            </p>
                        </div>

                        <div className="rounded-3xl border border-border/60 bg-card p-6 shadow-none">
                            <h3 className="mb-3 text-base font-semibold text-foreground">
                                Jam layanan
                            </h3>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                Hari kerja sesuai jam layanan Program Studi
                                Teknik Informatika.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </PageLayout>
    );
}
