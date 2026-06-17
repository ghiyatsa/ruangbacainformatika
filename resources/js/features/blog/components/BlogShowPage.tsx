import { Link } from '@inertiajs/react';
import { CalendarDays, Clock3, Eye, Tag } from 'lucide-react';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { PageLayout } from '@/components/layout/PageLayout';
import { PublicPageHero } from '@/components/layout/PublicPageHero';
import { StaticPageContent } from '@/components/layout/StaticPageContent';
import { Badge } from '@/components/ui/badge';
import blog from '@/routes/blog';
import { BlogPostCard } from './BlogPostCard';
import type { BlogShowPageProps } from '@/features/blog/types';

export function BlogShowPage({ post, relatedPosts }: BlogShowPageProps) {
    const article = post.data;

    return (
        <PageLayout
            title={article.title}
            metaDescription={article.summary ?? article.excerpt}
            maxWidth="7xl"
            className="pt-0 pb-16"
            showDesktopNoticeInContent={false}
            header={
                <div className="relative overflow-hidden">
                    <PublicPageHero
                        eyebrow="Publikasi"
                        title={article.title}
                        description={article.summary ?? article.excerpt}
                        contentClassName="max-w-7xl px-4 sm:px-6 lg:px-8"
                        align="left"
                        className="border-b-0 pb-10"
                    />

                    <div className="mx-auto -mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
                        <Breadcrumbs
                            breadcrumbs={[
                                { title: 'Beranda', href: '/' },
                                { title: 'Blog', href: blog.index.url() },
                                { title: article.title, href: blog.show.url(article.slug) },
                            ]}
                        />
                    </div>
                </div>
            }
        >
            <div className="-mt-4 grid gap-8 xl:grid-cols-[minmax(0,1fr)_22rem]">
                <div className="space-y-6">
                    <section className="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm">
                        <div className="aspect-[16/8] bg-muted">
                            <img
                                src={article.coverImageUrl}
                                alt=""
                                width={1200}
                                height={600}
                                className="h-full w-full object-cover"
                            />
                        </div>

                        <div className="border-t border-border/60 px-6 py-5 sm:px-8">
                            <div className="flex flex-wrap gap-2">
                                {article.categories.map((category) => (
                                    <Link
                                        key={category.slug}
                                        href={blog.index.url({
                                            query: { category: category.slug },
                                        })}
                                    >
                                        <Badge className="rounded-full">
                                            {category.name}
                                        </Badge>
                                    </Link>
                                ))}
                            </div>

                            <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted-foreground">
                                <span>{article.author?.name ?? 'Penulis tidak diketahui'}</span>
                                <span className="inline-flex items-center gap-1.5">
                                    <CalendarDays className="size-4" />
                                    {article.publishedAtLabel ?? 'Belum terbit'}
                                </span>
                                <span className="inline-flex items-center gap-1.5">
                                    <Clock3 className="size-4" />
                                    {article.readingMinutes} menit baca
                                </span>
                                <span className="inline-flex items-center gap-1.5">
                                    <Eye className="size-4" />
                                    {article.viewCount.toLocaleString('id-ID')} kali dilihat
                                </span>
                            </div>
                        </div>
                    </section>

                    <StaticPageContent html={article.content} className="max-w-none" />
                </div>

                <aside className="space-y-6">
                    <section className="rounded-3xl border border-border/60 bg-card p-5 shadow-sm">
                        <h2 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                            Tag
                        </h2>
                        <div className="mt-4 flex flex-wrap gap-2">
                            {article.tags.length > 0 ? (
                                article.tags.map((tagItem) => (
                                    <Link
                                        key={tagItem.slug}
                                        href={blog.index.url({
                                            query: { tag: tagItem.slug },
                                        })}
                                    >
                                        <Badge variant="secondary" className="rounded-full">
                                            <Tag className="mr-1 size-3" />
                                            {tagItem.name}
                                        </Badge>
                                    </Link>
                                ))
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    Artikel ini belum memiliki tag.
                                </p>
                            )}
                        </div>
                    </section>

                    {relatedPosts.length > 0 ? (
                        <section className="space-y-4">
                            <div>
                                <h2 className="text-xl font-bold tracking-tight">
                                    Artikel Terkait
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Pilihan bacaan lain pada topik serupa.
                                </p>
                            </div>

                            <div className="grid gap-4">
                                {relatedPosts.map((relatedPost) => (
                                    <BlogPostCard
                                        key={relatedPost.id}
                                        post={relatedPost}
                                        compact
                                    />
                                ))}
                            </div>
                        </section>
                    ) : null}
                </aside>
            </div>
        </PageLayout>
    );
}
