import { Form, usePage } from '@inertiajs/react';
import { Flag, Mail, ShieldCheck, UserCircle2 } from 'lucide-react';
import { useState } from 'react';
import CatalogReportController from '@/actions/App/Http/Controllers/CatalogReportController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type CatalogReportCardProps = {
    catalogType: 'book' | 'skripsi' | 'thesis' | 'internship_report';
    catalogId: number;
    catalogLabel: string;
    catalogTitle: string;
};

export function CatalogReportCard({
    catalogType,
    catalogId,
    catalogLabel,
    catalogTitle,
}: CatalogReportCardProps) {
    const [open, setOpen] = useState(false);
    const user = usePage().props.auth?.user;

    return (
        <div className="rounded-2xl border border-amber-500/20 bg-amber-50 shadow-sm dark:bg-amber-950/20">
            <div className="flex items-start gap-3 p-5">
                <div className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-300">
                    <Flag className="size-4" />
                </div>
                <div className="space-y-2">
                    <h2 className="text-sm font-semibold tracking-wide text-foreground uppercase">
                        Laporkan Data Keliru
                    </h2>
                    <p className="text-sm leading-6 text-muted-foreground">
                        Laporkan jika ada data yang keliru pada{' '}
                        {catalogLabel.toLowerCase()} ini.
                    </p>
                </div>
            </div>

            <div className="px-5 pb-5">
                <Dialog open={open} onOpenChange={setOpen}>
                    <DialogTrigger asChild>
                        <Button variant="outline" className="w-full">
                            <Flag data-icon="inline-start" />
                            Laporkan Data
                        </Button>
                    </DialogTrigger>
                    <DialogContent className="sm:max-w-lg">
                        <DialogHeader>
                            <DialogTitle>
                                Laporkan data {catalogLabel.toLowerCase()}
                            </DialogTitle>
                            <DialogDescription>
                                Jelaskan bagian yang keliru pada{' '}
                                <span className="font-medium text-foreground">
                                    {catalogTitle}
                                </span>
                                .
                            </DialogDescription>
                        </DialogHeader>

                        <Form
                            action={CatalogReportController.store()}
                            onSuccess={() => setOpen(false)}
                            resetOnSuccess
                        >
                            {({ errors, processing }) => (
                                <div className="space-y-4">
                                    <input
                                        type="hidden"
                                        name="catalog_type"
                                        value={catalogType}
                                    />
                                    <input
                                        type="hidden"
                                        name="catalog_id"
                                        value={catalogId}
                                    />

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label
                                                htmlFor="reporter_name"
                                                className="flex items-center gap-1.5"
                                            >
                                                <UserCircle2 className="size-3.5 text-muted-foreground" />
                                                Nama pelapor
                                            </Label>
                                            <Input
                                                id="reporter_name"
                                                name="reporter_name"
                                                defaultValue={user?.name ?? ''}
                                                placeholder="Opsional"
                                            />
                                            <InputError
                                                message={errors.reporter_name}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label
                                                htmlFor="reporter_email"
                                                className="flex items-center gap-1.5"
                                            >
                                                <Mail className="size-3.5 text-muted-foreground" />
                                                Email pelapor
                                            </Label>
                                            <Input
                                                id="reporter_email"
                                                type="email"
                                                name="reporter_email"
                                                defaultValue={user?.email ?? ''}
                                                placeholder="Opsional"
                                            />
                                            <InputError
                                                message={errors.reporter_email}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor="catalog_report_message"
                                            className="flex items-center gap-1.5"
                                        >
                                            <ShieldCheck className="size-3.5 text-muted-foreground" />
                                            Penjelasan laporan
                                        </Label>
                                        <Textarea
                                            id="catalog_report_message"
                                            name="message"
                                            minLength={10}
                                            required
                                            rows={6}
                                            placeholder="Contoh: nama penulis salah, tahun terbit tidak sesuai, atau abstrak tidak lengkap."
                                        />
                                        <InputError message={errors.message} />
                                    </div>

                                    <DialogFooter>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => setOpen(false)}
                                        >
                                            Batal
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Kirim Laporan
                                        </Button>
                                    </DialogFooter>
                                </div>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
