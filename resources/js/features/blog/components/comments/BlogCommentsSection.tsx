import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { CommentAvatar } from '@/features/blog/components/comments/CommentAvatar';
import { CommentInput } from '@/features/blog/components/comments/CommentInput';
import { CommentItem } from '@/features/blog/components/comments/CommentItem';
import { GuestCommentPrompt } from '@/features/blog/components/comments/GuestCommentPrompt';
import type { CommentUser } from '@/features/blog/components/comments/CommentItem';
import type { BlogPostComment } from '@/features/blog/types';
import type { PaginationData } from '@/types/pagination';

interface BlogCommentsSectionProps {
    comments: BlogPostComment[];
    commentsCount?: number;
    articleSlug: string;
    currentUser: CommentUser | null;
    googleLoginUrl: string;
    pagination?: PaginationData<BlogPostComment>;
    allowComments?: boolean;
}

export function BlogCommentsSection({
    comments,
    commentsCount,
    articleSlug,
    currentUser,
    googleLoginUrl,
    pagination,
    allowComments = true,
}: BlogCommentsSectionProps) {
    const [loadingMore, setLoadingMore] = useState(false);

    const handleLoadMore = () => {
        if (!pagination || loadingMore) {
            return;
        }

        setLoadingMore(true);

        router.get(
            window.location.pathname,
            { comments_page: pagination.current_page + 1 },
            {
                only: ['post'],
                preserveScroll: true,
                onFinish: () => setLoadingMore(false),
            },
        );
    };

    return (
        <section className="space-y-4 border-t border-border/80 pt-8">
            <div className="flex items-center gap-2">
                <h2 className="text-lg font-bold text-foreground">
                    Komentar ({commentsCount ?? comments.length})
                </h2>
            </div>

            {/* Input Form for New Comment */}
            {allowComments ? (
                <div className="rounded-lg border border-border bg-card p-4">
                    {currentUser ? (
                        <div className="space-y-4">
                            <div className="flex items-center gap-3">
                                <CommentAvatar
                                    avatarUrl={currentUser.avatar}
                                    initials={currentUser.initials}
                                    size="sm"
                                />
                                <div className="text-sm">
                                    <span className="text-muted-foreground">
                                        Berkomentar sebagai{' '}
                                    </span>
                                    <span className="font-semibold text-foreground">
                                        {currentUser.name}
                                    </span>
                                </div>
                            </div>
                            <CommentInput articleSlug={articleSlug} />
                        </div>
                    ) : (
                        <GuestCommentPrompt googleLoginUrl={googleLoginUrl} />
                    )}
                </div>
            ) : (
                <div className="rounded-lg border border-dashed border-border bg-muted/5 p-6 text-center text-sm text-muted-foreground">
                    Kolom komentar dinonaktifkan untuk artikel ini.
                </div>
            )}

            {/* Comments List */}
            {(() => {
                const uniqueComments = comments.filter(
                    (comment, index, self) => self.findIndex((c) => c.id === comment.id) === index
                );

                if (uniqueComments.length === 0) {
                    return (
                        <div className="rounded-lg border border-dashed border-border bg-muted/5 p-8 text-center text-muted-foreground">
                            Belum ada komentar. Jadilah yang pertama memberikan
                            komentar!
                        </div>
                    );
                }

                return (
                    <div className="space-y-4">
                        <div className="divide-y divide-border/60">
                            {uniqueComments.map((comment) => (
                                <CommentItem
                                    key={comment.id}
                                    comment={comment}
                                    articleSlug={articleSlug}
                                    currentUser={currentUser}
                                    googleLoginUrl={googleLoginUrl}
                                    allowComments={allowComments}
                                />
                            ))}
                        </div>

                        {pagination && pagination.current_page < pagination.last_page && (
                            <div className="pt-2 flex justify-center">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    disabled={loadingMore}
                                    onClick={handleLoadMore}
                                    className="rounded-full px-6"
                                >
                                    {loadingMore ? 'Memuat...' : 'Muat Komentar Lainnya'}
                                </Button>
                            </div>
                        )}
                    </div>
                );
            })()}
        </section>
    );
}
