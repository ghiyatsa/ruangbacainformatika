import { Deferred, usePage } from '@inertiajs/react';
import * as React from 'react';
import { KtiCardSkeleton } from '@/components/kti/KtiCardSkeleton';
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import AcademicWorkCard from '@/features/academic-works/components/AcademicWorkCard';
import { BlogPostCard } from '@/features/blog/components/BlogPostCard';
import BookCard from '@/features/books/components/BookCard';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import { CatalogPageLayout } from '@/features/books/components/CatalogPageLayout';
import InternshipReportCard from '@/features/internship-report/components/InternshipReportCard';
import type { AcademicWorkData } from '@/features/academic-works/types';
import type { BlogPostItem } from '@/features/blog/types';
import type { InternshipReportData } from '@/features/internship-report/types';
import type { CatalogBook } from '@/features/welcome/types';
import type { Auth, LoanRequestCart } from '@/types';

interface SearchProps {
    query: string;
    results?: {
        books: CatalogBook[];
        posts: BlogPostItem[];
        skripsis: AcademicWorkData[];
        internshipReports: InternshipReportData[];
        theses: AcademicWorkData[];
    };
}

function SearchSkeleton() {
    return (
        <div className="space-y-12">
            {/* Books Skeleton Section */}
            <section className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div className="flex max-w-2xl flex-col gap-2">
                        <Skeleton className="h-7 w-32 rounded" />
                    </div>
                </div>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <BookCardSkeleton variant="compact" />
                    <BookCardSkeleton variant="compact" />
                    <BookCardSkeleton variant="compact" />
                </div>
            </section>

            {/* Hatched Divider */}
            <div className="-mx-4 sm:-mx-6 lg:-mx-8 border-y border-border/60 my-8">
                <div
                    className="h-6"
                    style={{
                        backgroundImage:
                            'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                    }}
                />
            </div>

            {/* Academic Works Skeleton Section */}
            <section className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div className="flex max-w-2xl flex-col gap-2">
                        <Skeleton className="h-7 w-28 rounded" />
                    </div>
                </div>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <KtiCardSkeleton />
                    <KtiCardSkeleton />
                    <KtiCardSkeleton />
                </div>
            </section>
        </div>
    );
}

export default function SearchIndex({ query, results }: SearchProps) {
    const { auth, loanRequestCart } = usePage<{
        auth: Auth;
        loanRequestCart: LoanRequestCart | null;
    }>().props;

    const totalCount = results
        ? results.books.length +
          results.posts.length +
          results.skripsis.length +
          results.internshipReports.length +
          results.theses.length
        : 0;

    const customHeader = (
        <div className="relative -mt-20 overflow-hidden bg-background sm:-mt-28 md:-mt-24">
            <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-12 sm:px-6 sm:pt-30 lg:px-8">
                <div className="flex flex-col gap-4">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                            {query ? `Hasil: "${query}"` : 'Pencarian'}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    );

    return (
        <CatalogPageLayout
            title={query ? `Hasil: "${query}"` : 'Pencarian'}
            metaDescription={query ? `Hasil pencarian untuk "${query}"` : 'Halaman pencarian koleksi.'}
            resourceName="item"
            breadcrumbLabel="Pencarian"
            totalCount={totalCount}
            header={customHeader}
        >
            <div className="space-y-10">
                <Deferred data="results" fallback={<SearchSkeleton />}>
                    {results && totalCount === 0 ? (
                        <Card className="border-dashed py-12 rounded-2xl bg-card">
                            <CardContent className="flex flex-col items-center justify-center text-center">
                                {query ? (
                                    // Query ada tapi tidak ada hasil
                                    <>
                                        <p className="text-lg font-bold text-muted-foreground">
                                            Tidak ada hasil yang cocok untuk &ldquo;{query}&rdquo;.
                                        </p>
                                        <p className="text-sm text-muted-foreground mt-1">
                                            Silakan coba kata kunci lain atau periksa ejaan Anda.
                                        </p>
                                    </>
                                ) : (
                                    // Tidak ada query sama sekali
                                    <>
                                        <p className="text-lg font-bold text-muted-foreground">
                                            Ketik kata kunci untuk mulai mencari.
                                        </p>
                                        <p className="text-sm text-muted-foreground mt-1">
                                            Gunakan tombol pencarian atau tekan <kbd className="rounded border px-1.5 py-0.5 text-xs font-mono">Ctrl K</kbd> untuk membuka pencarian.
                                        </p>
                                    </>
                                )}
                            </CardContent>
                        </Card>
                    ) : results ? (
                        (() => {
                            const activeSections: { id: string; title: string; content: React.ReactNode }[] = [];

                            if (results.books.length > 0) {
                                activeSections.push({
                                    id: 'books',
                                    title: 'Buku',
                                    content: (
                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                            {results.books.map((book) => (
                                                <BookCard
                                                    key={`book-${book.id}`}
                                                    book={book}
                                                    auth={auth}
                                                    loanRequestCart={loanRequestCart}
                                                    variant="compact"
                                                />
                                            ))}
                                        </div>
                                    )
                                });
                            }

                            if (results.posts.length > 0) {
                                activeSections.push({
                                    id: 'posts',
                                    title: 'Artikel',
                                    content: (
                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                            {results.posts.map((post) => (
                                                <BlogPostCard
                                                    key={`post-${post.id}`}
                                                    post={post}
                                                />
                                            ))}
                                        </div>
                                    )
                                });
                            }

                            if (results.skripsis.length > 0) {
                                activeSections.push({
                                    id: 'skripsis',
                                    title: 'Skripsi',
                                    content: (
                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                            {results.skripsis.map((skripsi) => (
                                                <AcademicWorkCard
                                                    key={`skripsi-${skripsi.id}`}
                                                    work={skripsi}
                                                    workType="skripsi"
                                                />
                                            ))}
                                        </div>
                                    )
                                });
                            }

                            if (results.internshipReports.length > 0) {
                                activeSections.push({
                                    id: 'internship-reports',
                                    title: 'Laporan Kerja Praktik',
                                    content: (
                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                            {results.internshipReports.map((report) => (
                                                <InternshipReportCard
                                                    key={`report-${report.id}`}
                                                    report={report}
                                                />
                                            ))}
                                        </div>
                                    )
                                });
                            }

                            if (results.theses.length > 0) {
                                activeSections.push({
                                    id: 'theses',
                                    title: 'Tesis',
                                    content: (
                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                            {results.theses.map((thesis) => (
                                                <AcademicWorkCard
                                                    key={`thesis-${thesis.id}`}
                                                    work={thesis}
                                                    workType="thesis"
                                                />
                                            ))}
                                        </div>
                                    )
                                });
                            }

                            return (
                                <div className="space-y-12">
                                    {activeSections.map((section, idx) => (
                                        <React.Fragment key={section.id}>
                                            {idx > 0 && (
                                                <div className="-mx-4 sm:-mx-6 lg:-mx-8 border-y border-border/60 my-8">
                                                    <div
                                                        className="h-6"
                                                        style={{
                                                            backgroundImage:
                                                                'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                                                        }}
                                                    />
                                                </div>
                                            )}
                                            <section className="space-y-6">
                                                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                                                    <div className="flex max-w-2xl flex-col gap-2">
                                                        <h2 className="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                                                            {section.title}
                                                        </h2>
                                                    </div>
                                                </div>
                                                {section.content}
                                            </section>
                                        </React.Fragment>
                                    ))}
                                </div>
                            );
                        })()
                    ) : null}
                </Deferred>
            </div>
        </CatalogPageLayout>
    );
}
