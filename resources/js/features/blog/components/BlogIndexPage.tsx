import { CatalogPage } from '@/features/books/components/CatalogPage';
import { CatalogPagination } from '@/features/books/components/CatalogPagination';
import { BlogPostCard } from './BlogPostCard';
import type { BlogIndexPageProps } from '@/features/blog/types';

export function BlogIndexPage({ posts }: BlogIndexPageProps) {
    return (
        <CatalogPage
            title="Artikel"
            metaDescription="Artikel pilihan dari Ruang Baca Informatika."
            resourceName="artikel"
            breadcrumbLabel="Artikel"
            totalCount={posts.total}
            paginationData={posts}
            paginationVisibility="none"
        >
            {posts.data.length === 0 ? (
                <div className="rounded-3xl border border-dashed border-border/70 bg-card px-6 py-14 text-center">
                    <p className="text-lg font-semibold">
                        Artikel tidak ditemukan
                    </p>
                    <p className="mt-2 text-sm text-muted-foreground">
                        Coba gunakan kata kunci atau filter yang berbeda.
                    </p>
                </div>
            ) : (
                <div className="grid gap-5 lg:grid-cols-2">
                    {posts.data.map((post) => (
                        <BlogPostCard key={post.id} post={post} />
                    ))}
                </div>
            )}

            <CatalogPagination data={posts} resourceName="artikel" />
        </CatalogPage>
    );
}
