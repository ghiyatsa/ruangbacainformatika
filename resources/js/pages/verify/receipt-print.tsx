import { Head } from '@inertiajs/react';
import { CheckCircle2, Printer } from 'lucide-react';
import { RuangBacaLogo } from '@/components/common/RuangBacaLogo';
import { Button } from '@/components/ui/button';

interface Props {
    receipt: {
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
        copies_count?: number | null;
        batch_token?: string | null;
        batch_items?: Array<{
            id: number;
            title: string;
            author_names?: string | null;
            publisher_name?: string | null;
            isbn?: string | null;
            copies_count?: number | null;
            receipt_number?: string | null;
        }>;
        approved_at: string;
        verification_url: string;
        qr_svg?: string | null;
        catalog_url?: string | null;
    };
}

export default function ReceiptPrintPage({ receipt }: Props) {
    return (
        <>
            <Head
                title={`Tanda Terima ${receipt.type_label} - ${receipt.student_name}`}
            >
                <style type="text/css">
                    {`
                        @page {
                            size: A4 portrait;
                            margin: 0;
                        }
                        @media print {
                            *, *::before, *::after {
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                            }
                            html, body {
                                width: 210mm !important;
                                min-height: 297mm !important;
                                height: auto !important;
                                background: #ffffff !important;
                                color: #000000 !important;
                                margin: 0 !important;
                                padding: 0 !important;
                            }
                            body * {
                                visibility: hidden !important;
                            }
                            #print-container, #print-container * {
                                visibility: visible !important;
                            }
                            #print-container {
                                position: absolute !important;
                                left: 0 !important;
                                top: 0 !important;
                                width: 210mm !important;
                                min-height: 297mm !important;
                                margin: 0 !important;
                                padding: 18mm 20mm !important;
                                box-sizing: border-box !important;
                                box-shadow: none !important;
                                border: none !important;
                                background: #ffffff !important;
                            }
                            .no-print {
                                display: none !important;
                            }
                        }
                    `}
                </style>
            </Head>

            <div className="min-h-screen bg-neutral-100 py-8 dark:bg-neutral-900 print:min-h-0 print:bg-white print:py-0">
                <div className="mx-auto max-w-3xl">
                    {/* Top Action Bar (Hidden on print) */}
                    <div className="no-print mb-6 flex items-center justify-between px-4 sm:px-0">
                        <Button asChild variant="outline" size="sm">
                            <a href="/dashboard">Kembali ke Dashboard</a>
                        </Button>

                        <Button
                            type="button"
                            size="sm"
                            className="gap-2 shadow-sm"
                            onClick={() => window.print()}
                        >
                            <Printer className="size-4" />
                            Cetak / Simpan Dokumen PDF
                        </Button>
                    </div>

                    {/* Official Certificate / Receipt (A4 Format) */}
                    <div
                        id="print-container"
                        className="rounded-2xl border border-neutral-300 bg-white p-8 text-neutral-900 shadow-xl sm:p-12 print:rounded-none print:border-none print:p-0 print:shadow-none"
                    >
                        {/* Header Kop Surat Resmi */}
                        <div className="flex items-center gap-5 border-b-2 border-neutral-900 pb-5">
                            <RuangBacaLogo className="size-16 shrink-0" />
                            <div className="flex-1 text-center sm:text-left">
                                <h1 className="text-base font-extrabold tracking-tight text-neutral-900 uppercase sm:text-lg">
                                    Ruang Baca Teknik Informatika
                                </h1>
                                <p className="text-xs font-medium text-neutral-700 sm:text-sm">
                                    Jurusan Teknik Informatika &bull; Fakultas
                                    Teknik &bull; Universitas Malikussaleh
                                </p>
                                <p className="text-[11px] text-neutral-500">
                                    Kampus Utama Bukit Indah, Lhokseumawe &bull;
                                    Email: ruangbaca.if@unimal.ac.id
                                </p>
                            </div>
                        </div>

                        {/* Title & Document Number */}
                        <div className="my-7 text-center">
                            <h2 className="text-base font-bold tracking-wider uppercase underline underline-offset-4 sm:text-lg">
                                Bukti Penyerahan {receipt.type_label}
                            </h2>
                            <p className="mt-1 font-mono text-xs font-semibold text-neutral-600 sm:text-sm">
                                Nomor: {receipt.receipt_number}
                            </p>
                        </div>

                        {/* Content Statement */}
                        <div className="space-y-4 text-xs leading-relaxed sm:text-sm">
                            <p className="text-justify text-neutral-800">
                                Pengelola Ruang Baca Jurusan Teknik Informatika
                                Universitas Malikussaleh menerangkan bahwa
                                mahasiswa berikut:
                            </p>

                            {/* Data Table */}
                            <div className="my-4 space-y-2.5 py-2">
                                <div className="grid grid-cols-[150px_1fr] gap-2">
                                    <span className="font-semibold text-neutral-600">
                                        Nama Mahasiswa
                                    </span>
                                    <span className="font-bold text-neutral-900">
                                        : {receipt.student_name}
                                    </span>
                                </div>
                                <div className="grid grid-cols-[150px_1fr] gap-2">
                                    <span className="font-semibold text-neutral-600">
                                        NIM
                                    </span>
                                    <span className="font-mono font-bold text-neutral-900">
                                        : {receipt.student_id}
                                    </span>
                                </div>

                                {receipt.company_name ? (
                                    <div className="grid grid-cols-[150px_1fr] gap-2">
                                        <span className="font-semibold text-neutral-600">
                                            Tempat / Instansi KP
                                        </span>
                                        <span className="text-neutral-900">
                                            : {receipt.company_name}
                                        </span>
                                    </div>
                                ) : null}

                                {receipt.academic_advisor ? (
                                    <div className="grid grid-cols-[150px_1fr] gap-2">
                                        <span className="font-semibold text-neutral-600">
                                            Dosen Pembimbing
                                        </span>
                                        <span className="text-neutral-900">
                                            : {receipt.academic_advisor}
                                        </span>
                                    </div>
                                ) : null}

                                <div className="grid grid-cols-[150px_1fr] gap-2">
                                    <span className="font-semibold text-neutral-600">
                                        Judul Karya / Buku
                                    </span>
                                    <span className="font-semibold text-neutral-900">
                                        : {receipt.title}
                                    </span>
                                </div>

                                {receipt.author_names ? (
                                    <div className="grid grid-cols-[150px_1fr] gap-2">
                                        <span className="font-semibold text-neutral-600">
                                            Pengarang / Penerbit
                                        </span>
                                        <span className="text-neutral-900">
                                            : {receipt.author_names}{' '}
                                            {receipt.publisher_name
                                                ? `(${receipt.publisher_name})`
                                                : ''}
                                        </span>
                                    </div>
                                ) : null}

                                {receipt.copies_count ? (
                                    <div className="grid grid-cols-[150px_1fr] gap-2">
                                        <span className="font-semibold text-neutral-600">
                                            Jumlah Eksemplar
                                        </span>
                                        <span className="font-semibold text-neutral-900">
                                            : {receipt.copies_count} Eksemplar
                                        </span>
                                    </div>
                                ) : null}

                                {receipt.batch_items &&
                                receipt.batch_items.length > 1 ? (
                                    <div className="mt-4 border-t border-neutral-200 pt-3">
                                        <p className="mb-2 text-xs font-bold text-neutral-700">
                                            Daftar Buku Diserahkan Dalam Batch
                                            Ini ({receipt.batch_items.length}{' '}
                                            Judul Buku):
                                        </p>
                                        <table className="w-full border border-neutral-300 text-left text-xs">
                                            <thead className="bg-neutral-100 text-neutral-700">
                                                <tr>
                                                    <th className="w-8 border-b border-neutral-300 p-1.5 text-center">
                                                        No
                                                    </th>
                                                    <th className="border-b border-neutral-300 p-1.5">
                                                        Judul Buku
                                                    </th>
                                                    <th className="border-b border-neutral-300 p-1.5">
                                                        Penulis / Penerbit
                                                    </th>
                                                    <th className="w-16 border-b border-neutral-300 p-1.5 text-center">
                                                        Jml
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {receipt.batch_items.map(
                                                    (item, idx) => (
                                                        <tr
                                                            key={item.id}
                                                            className="border-b border-neutral-200"
                                                        >
                                                            <td className="p-1.5 text-center align-top">
                                                                {idx + 1}
                                                            </td>
                                                            <td className="p-1.5 align-top font-semibold text-neutral-900">
                                                                {item.title}
                                                            </td>
                                                            <td className="p-1.5 align-top text-neutral-700">
                                                                {[
                                                                    item.author_names,
                                                                    item.publisher_name,
                                                                ]
                                                                    .filter(
                                                                        Boolean,
                                                                    )
                                                                    .join(
                                                                        ' • ',
                                                                    ) || '-'}
                                                            </td>
                                                            <td className="p-1.5 text-center align-top whitespace-nowrap">
                                                                {item.copies_count ??
                                                                    1}{' '}
                                                                eks
                                                            </td>
                                                        </tr>
                                                    ),
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : null}
                            </div>

                            <p className="text-justify text-neutral-800">
                                Telah menyelesaikan penyerahan naskah / dokumen{' '}
                                <strong>{receipt.type_label}</strong> secara
                                lengkap dan disetujui untuk diarsipkan dalam
                                Sistem Informasi Perpustakaan Ruang Baca Teknik
                                Informatika UNIMAL sebagai syarat administrasi
                                akademik / bebas pustaka.
                            </p>
                        </div>

                        {/* Signatures & QR Code Section */}
                        <div className="mt-8 grid grid-cols-2 items-end pt-4">
                            {/* QR Code Validation */}
                            <div className="space-y-2">
                                {receipt.qr_svg ? (
                                    <div
                                        className="size-32 bg-white text-neutral-900 [&>svg]:size-full [&>svg]:h-full [&>svg]:w-full"
                                        dangerouslySetInnerHTML={{
                                            __html: receipt.qr_svg,
                                        }}
                                    />
                                ) : null}

                                <p className="max-w-[220px] text-[10px] leading-tight text-neutral-500">
                                    Pindai QR code di atas untuk memvalidasi
                                    keaslian dokumen tanda terima ini.
                                </p>
                            </div>

                            {/* Official Admin Signature Box */}
                            <div className="text-right">
                                <p className="text-xs text-neutral-700">
                                    Lhokseumawe, {receipt.approved_at}
                                </p>
                                <p className="mt-1 text-xs font-semibold text-neutral-900">
                                    Pengelola Ruang Baca Teknik Informatika
                                </p>
                                <div className="my-8" />
                                <p className="text-xs font-bold text-neutral-900 underline underline-offset-2">
                                    Petugas Layanan & Administrasi
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
