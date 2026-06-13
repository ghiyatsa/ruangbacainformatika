import { Deferred, Link } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import booksRoute from '@/routes/books';
import BookCollectionViewToggle from './BookCollectionViewToggle';
import BookGrid from './BookGrid';
import DeferredCatalogRescue from './DeferredCatalogRescue';
import SectionHeader from './SectionHeader';
import type { WelcomeProps } from '@/features/welcome/types';
import type { BookCollectionViewMode } from './BookCollectionViewToggle';

export default function PopularCategoryShelves({
    popularCategoryShelves,
}: {
    popularCategoryShelves: WelcomeProps['popularCategoryShelves'];
}) {
    const [viewMode, setViewMode] = useState<BookCollectionViewMode>('grid');
    const shelves = popularCategoryShelves ?? [];

    return (
        <Deferred
            data="popularCategoryShelves"
            fallback={
                <div className="flex flex-col gap-12">
                    {Array.from({ length: 3 }).map((_, index) => (
                        <div
                            key={`popular-category-shelf-skeleton-${index}`}
                            className="flex flex-col gap-8 sm:gap-10"
                        >
                            <SectionHeader
                                title="Kategori populer"
                                subtitle="Daftar buku pada kategori ini sedang dimuat."
                                action={
                                    <BookCollectionViewToggle
                                        viewMode={viewMode}
                                        onChange={setViewMode}
                                    />
                                }
                            />

                            <BookGrid
                                books={[]}
                                viewMode={viewMode}
                                skeletonCount={6}
                                isLoading={true}
                            />
                        </div>
                    ))}
                </div>
            }
            rescue={({ reloading }) => (
                <DeferredCatalogRescue
                    dataKey="popularCategoryShelves"
                    title="Daftar buku kategori populer belum tersedia"
                    description="Bagian ini dapat dimuat ulang tanpa memuat ulang seluruh halaman."
                    reloading={reloading}
                />
            )}
        >
            <div className="flex flex-col gap-12 sm:gap-14">
                {shelves.map((shelf) => (
                    <div key={shelf.id} className="flex flex-col gap-8 sm:gap-10">
                        <SectionHeader
                            title={shelf.name}
                            subtitle={
                                shelf.description ||
                                `${shelf.booksCount.toLocaleString('id-ID')} judul tersedia pada kategori ini.`
                            }
                            action={
                                <BookCollectionViewToggle
                                    viewMode={viewMode}
                                    onChange={setViewMode}
                                />
                            }
                        />

                        <BookGrid
                            books={shelf.books}
                            viewMode={viewMode}
                            keyPrefix={`popular-category-${shelf.slug}`}
                            emptyTitle={`Belum ada buku pada kategori ${shelf.name}`}
                            emptyDescription="Daftar buku untuk kategori ini akan tampil di sini."
                        />

                        <div className="flex justify-center">
                            <Button asChild size="lg" className="rounded-xl px-8">
                                <Link
                                    href={booksRoute.index.url({
                                        query: {
                                            category: shelf.slug,
                                        },
                                    })}
                                >
                                    Lihat semua buku {shelf.name}
                                </Link>
                            </Button>
                        </div>
                    </div>
                ))}
            </div>
        </Deferred>
    );
}
