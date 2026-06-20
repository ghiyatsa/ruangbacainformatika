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
    allowComments: boolean;
    author?: {
        name: string;
        avatar: string | null;
        initials?: string;
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
    commentsCount?: number;
    comments?: PaginationData<BlogPostComment>;
}

export interface BlogPostComment {
    id: number;
    content: string;
    parentId: number | null;
    replyToCommentId: number | null;
    replyToUser: {
        id: number;
        name: string;
    } | null;
    createdAt: string;
    createdAtLabel: string;
    user: {
        id: number;
        name: string;
        avatar: string | null;
        initials: string;
    } | null;
    replies?: BlogPostComment[];
    canDelete: boolean;
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
    categories?: BlogTaxonomyItem[];
    tags?: BlogTaxonomyItem[];
    posts?: PaginationData<BlogPostItem>;
    popularPosts?: BlogPostItem[];
}

export interface BlogShowPageProps {
    post?: {
        data: BlogPostItem;
    } | null;
    relatedPosts?: BlogPostItem[];
    popularPosts?: BlogPostItem[];
    categories?: BlogTaxonomyItem[];
    tags?: BlogTaxonomyItem[];
}
