import { Head, Link } from '@inertiajs/react';
import { CheckCircle2, XCircle, BookOpen } from 'lucide-react';
import { RuangBacaLogo } from '@/components/common/RuangBacaLogo';
import { Button } from '@/components/ui/button';

interface Props {
    isValid: boolean;
    submission?: {
        type: string;
        type_label: string;
        receipt_number: string;
        student_name: string;
        student_id: string;
        title: string;
        company_name?: string | null;
        academic_advisor?: string | null;
        author_names?: string | null;
        publisher_name?: string | null;
        isbn?: string | null;
        approved_at: string;
        catalog_url: string | null;
    } | null;
}

export default function VerifyReceiptPage({ isValid, submission }: Props) {
    return (
        <div className="flex min-h-screen items-center justify-center bg-background px-4 py-12">
            <Head
                title={`Verifikasi Tanda Terima - ${submission?.type_label ?? 'Bebas Pustaka'}`}
            />

            <div className="w-full max-w-lg rounded-3xl border border-border bg-card p-6 shadow-2xl sm:p-8">
                <div className="flex flex-col items-center text-center">
                    <RuangBacaLogo className="mb-4 size-14" />

                    {isValid && submission ? (
                        <>
                            <div className="mb-3 inline-flex size-12 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600">
                                <CheckCircle2 className="size-6" />
                            </div>
                            <h1 className="text-xl font-bold tracking-tight text-foreground">
                                Dokumen Terverifikasi Resmi
                            </h1>

                            <div className="my-6 w-full space-y-3 rounded-2xl border border-border/80 bg-muted/30 p-4 text-left text-xs sm:text-sm">
                                <div>
                                    <span className="text-[11px] font-medium text-muted-foreground">
                                        Jenis Penyerahan
                                    </span>
                                    <p className="font-semibold text-primary">
                                        {submission.type_label}
                                    </p>
                                </div>
                                <div>
                                    <span className="text-[11px] font-medium text-muted-foreground">
                                        Nomor Dokumen
                                    </span>
                                    <p className="font-mono font-bold text-foreground">
                                        {submission.receipt_number}
                                    </p>
                                </div>
                                <div className="grid grid-cols-2 gap-2">
                                    <div>
                                        <span className="text-[11px] font-medium text-muted-foreground">
                                            Nama Mahasiswa
                                        </span>
                                        <p className="font-semibold text-foreground">
                                            {submission.student_name}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-[11px] font-medium text-muted-foreground">
                                            NIM
                                        </span>
                                        <p className="font-mono font-semibold text-foreground">
                                            {submission.student_id}
                                        </p>
                                    </div>
                                </div>

                                {submission.company_name ? (
                                    <div>
                                        <span className="text-[11px] font-medium text-muted-foreground">
                                            Instansi / Perusahaan
                                        </span>
                                        <p className="font-medium text-foreground">
                                            {submission.company_name}
                                        </p>
                                    </div>
                                ) : null}

                                <div>
                                    <span className="text-[11px] font-medium text-muted-foreground">
                                        Judul Dokumen / Karya
                                    </span>
                                    <p className="font-semibold text-foreground">
                                        {submission.title}
                                    </p>
                                </div>

                                {submission.academic_advisor ? (
                                    <div>
                                        <span className="text-[11px] font-medium text-muted-foreground">
                                            Dosen Pembimbing
                                        </span>
                                        <p className="font-medium text-foreground">
                                            {submission.academic_advisor}
                                        </p>
                                    </div>
                                ) : null}

                                {submission.author_names ? (
                                    <div>
                                        <span className="text-[11px] font-medium text-muted-foreground">
                                            Pengarang / Penerbit
                                        </span>
                                        <p className="font-medium text-foreground">
                                            {submission.author_names}{' '}
                                            {submission.publisher_name
                                                ? `(${submission.publisher_name})`
                                                : ''}
                                        </p>
                                    </div>
                                ) : null}

                                <div>
                                    <span className="text-[11px] font-medium text-muted-foreground">
                                        Tanggal Disetujui
                                    </span>
                                    <p className="text-foreground">
                                        {submission.approved_at}
                                    </p>
                                </div>
                            </div>

                            {submission.catalog_url ? (
                                <Button asChild className="w-full gap-2">
                                    <Link href={submission.catalog_url}>
                                        <BookOpen className="size-4" />
                                        Buka Dokumen di Katalog
                                    </Link>
                                </Button>
                            ) : null}
                        </>
                    ) : (
                        <>
                            <div className="mb-3 inline-flex size-12 items-center justify-center rounded-full bg-destructive/10 text-destructive">
                                <XCircle className="size-6" />
                            </div>
                            <h1 className="text-xl font-bold tracking-tight text-foreground">
                                Dokumen Tidak Ditemukan
                            </h1>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Kode verifikasi tidak valid atau dokumen tanda
                                terima belum terdaftar resmi di sistem.
                            </p>
                            <Button
                                asChild
                                variant="outline"
                                className="mt-6 w-full"
                            >
                                <Link href="/">Kembali ke Beranda</Link>
                            </Button>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
