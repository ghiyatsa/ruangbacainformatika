import type { PaginationData } from '@/types/pagination';

export interface PostTaxonomyItem {
    id: number;
    name: string;
    slug: string;
    postsCount: number;
}

export interface PostItem {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    excerpt: string;
    /**
     * Badan artikel (HTML tersanitasi). Hanya dikirim pada halaman detail;
     * daftar/kartu artikel memakai excerpt saja, jadi field ini bisa kosong.
     */
    content?: string;
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
    comments?: PaginationData<PostCommentItem>;
}

export interface PostCommentItem {
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
    replies?: PostCommentItem[];
    canDelete: boolean;
}

export interface PostFilters {
    search: string;
    category: string;
    tag: string;
}

export interface PostIndexPageProps {
    filters: PostFilters;
    activeFilterLabels: Array<{
        key: string;
        label: string;
    }>;
    categories?: PostTaxonomyItem[];
    tags?: PostTaxonomyItem[];
    posts?: PaginationData<PostItem>;
    popularPosts?: PostItem[];
}

export interface PostShowPageProps {
    post?: {
        data: PostItem;
    } | null;
    relatedPosts?: PostItem[];
    popularPosts?: PostItem[];
    categories?: PostTaxonomyItem[];
    tags?: PostTaxonomyItem[];
    isPreview?: boolean;
}
