import type { PaginationData } from '@/types/pagination';

export interface BlogTaxonomyItem {
    id: number;
    name: string;
    slug: string;
    postsCount: number;
}

export interface BlogPostItem {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    excerpt: string;
    content: string;
    contentText: string;
    coverImageUrl: string;
    status: string;
    publishedAt: string | null;
    publishedAtLabel: string | null;
    reviewedAt: string | null;
    updatedAt: string | null;
    updatedAtLabel: string | null;
    viewCount: number;
    readingMinutes: number;
    author?: {
        name: string;
        avatar: string | null;
    };
    reviewer?: {
        name: string;
    } | null;
    categories: Array<{
        name: string;
        slug: string;
    }>;
    tags: Array<{
        name: string;
        slug: string;
    }>;
}

export interface BlogFilters {
    search: string;
    category: string;
    tag: string;
}

export interface BlogIndexPageProps {
    filters: BlogFilters;
    activeFilterLabels: Array<{
        key: string;
        label: string;
    }>;
    categories: BlogTaxonomyItem[];
    tags: BlogTaxonomyItem[];
    posts: PaginationData<BlogPostItem>;
    popularPosts: BlogPostItem[];
}

export interface BlogShowPageProps {
    post: {
        data: BlogPostItem;
    };
    relatedPosts: BlogPostItem[];
    popularPosts: BlogPostItem[];
    categories: BlogTaxonomyItem[];
    tags: BlogTaxonomyItem[];
}
