import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';

export function AboutPage() {
    return (
        <PageLayout
            title="Tentang Layanan"
            metaDescription="Mengenal Ruang Baca Teknik Informatika Universitas Malikussaleh dan layanan yang tersedia di dalamnya."
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    eyebrow="Tentang Ruang Baca"
                    title={
                        <>
                            Tentang{' '}
                            <span className="bg-linear-to-r from-primary to-primary/75 bg-clip-text text-transparent">
                                Ruang Baca
                            </span>
                        </>
                    }
                    description="Informasi singkat tentang Ruang Baca Teknik Informatika Universitas Malikussaleh."
                    contentClassName="max-w-4xl"
                    align="center"
                />
            }
        >
            <div className="space-y-12">
                {/* Introduction Section */}
                <div className="mx-auto max-w-4xl space-y-4 rounded-3xl border border-border/60 bg-card/85 p-8 text-center shadow-xs backdrop-blur-xs">
                    <h2 className="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                        Ruang Baca yang Lebih Mudah Diakses
                    </h2>
                    <p className="mx-auto max-w-3xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                        Ruang Baca Teknik Informatika membantu mahasiswa dan
                        dosen mencari buku, melihat informasi layanan, dan
                        memakai fasilitas ruang baca dengan lebih mudah.
                    </p>
                </div>

                {/* Features Grid */}
                <div className="space-y-6">
                    <div className="space-y-2 text-center">
                        <h2 className="text-lg text-xs font-bold tracking-tight tracking-widest text-primary uppercase">
                            Fitur dan Layanan
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Ringkasan layanan utama yang tersedia di Ruang
                            Baca.
                        </p>
                    </div>

                    <div className="grid gap-6 md:grid-cols-2">
                        {/* OPAC Card */}
                        <div className="space-y-3 rounded-3xl border border-primary/20 bg-linear-to-br from-primary/8 via-card to-card p-6 shadow-sm shadow-primary/5 backdrop-blur-sm">
                            <h3 className="text-base font-semibold text-foreground">
                                Katalog Terpadu (OPAC)
                            </h3>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                Pencarian online untuk buku, laporan kerja
                                praktik, skripsi, tesis, dan dokumen lain yang
                                tersedia di ruang baca.
                            </p>
                        </div>

                        {/* Kiosk Card */}
                        <div className="space-y-3 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm backdrop-blur-sm">
                            <h3 className="text-base font-semibold text-foreground">
                                Kiosk Mandiri
                            </h3>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                Antarmuka kiosk di lokasi untuk pencatatan
                                kunjungan, pendaftaran anggota, serta proses
                                peminjaman dan pengembalian secara mandiri.
                            </p>
                        </div>

                        {/* Circulation Card */}
                        <div className="space-y-3 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm backdrop-blur-sm">
                            <h3 className="text-base font-semibold text-foreground">
                                Sirkulasi & Pengajuan Online
                            </h3>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                Pengguna terdaftar dapat mengajukan peminjaman
                                buku, memantau batas pengembalian, dan meninjau
                                riwayat layanan melalui akun masing-masing.
                            </p>
                        </div>

                        {/* Similarity Card */}
                        <div className="space-y-3 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm backdrop-blur-sm">
                            <h3 className="text-base font-semibold text-foreground">
                                Pemeriksaan Kemiripan Dokumen
                            </h3>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                Fitur pemeriksaan awal untuk melihat kemiripan
                                judul atau dokumen sebelum diajukan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </PageLayout>
    );
}
