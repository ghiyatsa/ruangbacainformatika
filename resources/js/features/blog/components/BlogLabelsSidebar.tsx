import { Link } from '@inertiajs/react';
import { useState } from 'react';
import blog from '@/routes/blog';
import type { BlogTaxonomyItem } from '@/features/blog/types';

interface BlogLabelsSidebarProps {
    categories: BlogTaxonomyItem[];
    tags: BlogTaxonomyItem[];
    activeCategory?: string;
    activeTag?: string;
}

export function BlogLabelsSidebar({
    categories,
    tags,
    activeCategory,
    activeTag,
}: BlogLabelsSidebarProps) {
    const [showAllCategories, setShowAllCategories] = useState(false);
    const [showAllTags, setShowAllTags] = useState(false);

    const hasCats = categories.length > 0;
    const hasTags = tags.length > 0;

    if (!hasCats && !hasTags) {
        return null;
    }

    const visibleCategories = showAllCategories ? categories : categories.slice(0, 6);
    const visibleTags = showAllTags ? tags : tags.slice(0, 15);

    return (
        <section className="rounded-2xl border border-border/60 bg-card overflow-hidden">
            <div className="border-b border-border/60 px-5 py-3.5">
                <h2 className="text-sm font-bold tracking-wide text-foreground">
                    Kategori &amp; Label
                </h2>
            </div>

            <div className="p-4 space-y-4">
                {hasCats && (
                    <div>
                        <p className="mb-2 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                            Kategori
                        </p>
                        <div className="grid grid-cols-2 gap-2">
                            {visibleCategories.map((cat) => {
                                const isActive = activeCategory === cat.slug;

                                return (
                                    <Link
                                        key={cat.slug}
                                        href={blog.index.url({
                                            query: isActive ? undefined : { category: cat.slug },
                                        })}
                                        className={`flex items-center justify-between gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors min-w-0 ${
                                            isActive
                                                ? 'border-primary/40 bg-primary/10 text-primary'
                                                : 'border-border/60 bg-background text-foreground hover:border-primary/30 hover:bg-primary/5 hover:text-primary'
                                        }`}
                                    >
                                        <span className="truncate flex-1 text-left" title={cat.name}>
                                            {cat.name}
                                        </span>
                                        <span
                                            className={`rounded text-[10px] font-semibold px-1 shrink-0 ${
                                                isActive
                                                    ? 'bg-primary/20 text-primary'
                                                    : 'bg-muted text-muted-foreground'
                                            }`}
                                        >
                                            {cat.postsCount}
                                        </span>
                                    </Link>
                                );
                            })}
                        </div>
                        {categories.length > 6 && (
                            <button
                                onClick={() => setShowAllCategories(!showAllCategories)}
                                className="mt-2.5 text-xs font-semibold text-primary hover:underline cursor-pointer block text-left"
                            >
                                {showAllCategories ? 'Lihat lebih sedikit' : `Lihat semua (${categories.length})`}
                            </button>
                        )}
                    </div>
                )}

                {hasTags && (
                    <div>
                        <p className="mb-2 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                            Tag
                        </p>
                        <div className="flex flex-wrap gap-1.5">
                            {visibleTags.map((tag) => {
                                const isActive = activeTag === tag.slug;

                                return (
                                    <Link
                                        key={tag.slug}
                                        href={blog.index.url({
                                            query: isActive ? undefined : { tag: tag.slug },
                                        })}
                                        className={`inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-medium transition-colors ${
                                            isActive
                                                ? 'bg-primary text-primary-foreground'
                                                : 'bg-muted text-muted-foreground hover:bg-primary/10 hover:text-primary'
                                        }`}
                                    >
                                        {tag.name}
                                        <span className="opacity-70">({tag.postsCount})</span>
                                    </Link>
                                );
                            })}
                        </div>
                        {tags.length > 15 && (
                            <button
                                onClick={() => setShowAllTags(!showAllTags)}
                                className="mt-2.5 text-xs font-semibold text-primary hover:underline cursor-pointer block text-left"
                            >
                                {showAllTags ? 'Lihat lebih sedikit' : `Lihat semua (${tags.length})`}
                            </button>
                        )}
                    </div>
                )}
            </div>
        </section>
    );
}
