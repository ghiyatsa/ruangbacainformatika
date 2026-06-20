import { router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { CommentAvatar } from '@/features/blog/components/comments/CommentAvatar';
import { CommentInput } from '@/features/blog/components/comments/CommentInput';
import { GuestCommentPrompt } from '@/features/blog/components/comments/GuestCommentPrompt';
import commentsRoute from '@/routes/blog/comments';
import type { BlogPostComment } from '@/features/blog/types';

interface CommentItemProps {
    comment: BlogPostComment;
    articleSlug: string;
    currentUser: CommentUser | null;
    googleLoginUrl: string;
    allowComments?: boolean;
}

/** Shared shape for the currently authenticated user (kept loose to match the page). */
export interface CommentUser {
    id: number;
    name: string;
    avatar: string | null;
    initials: string;
}

const REPLIES_WRAPPER_CLASS =
    'ml-5 pl-4 border-l border-border/80 sm:ml-[3.25rem]';

type CommentKind = 'komentar' | 'balasan';

const KIND_LABEL: Record<CommentKind, string> = {
    komentar: 'Komentar',
    balasan: 'Balasan',
};

export function CommentItem({
    comment,
    articleSlug,
    currentUser,
    googleLoginUrl,
    allowComments = true,
}: CommentItemProps) {
    const [isReplying, setIsReplying] = useState(false);
    const [showReplies, setShowReplies] = useState(false);
    const [replyTargetId, setReplyTargetId] = useState<number>(comment.id);
    const [commentToDelete, setCommentToDelete] = useState<{
        id: number;
        kind: CommentKind;
    } | null>(null);

    const replies = comment.replies ?? [];
    const firstReply = replies[0];
    const remainingReplies = replies.slice(1);

    const confirmDelete = () => {
        if (!commentToDelete) {
            return;
        }

        const { id, kind } = commentToDelete;
        const label = KIND_LABEL[kind];

        router.delete(commentsRoute.destroy.url(id), {
            preserveScroll: true,
            onSuccess: () => {
                setCommentToDelete(null);
                toast.success(`${label} berhasil dihapus.`);
            },
            onError: () => {
                setCommentToDelete(null);
                toast.error(`Gagal menghapus ${label.toLowerCase()}.`);
            },
        });
    };

    const openReplyForm = (target: BlogPostComment) => {
        const isTogglingSameTarget = isReplying && replyTargetId === target.id;
        setReplyTargetId(target.id);
        setIsReplying(!isTogglingSameTarget);
    };

    // The reply target is whichever comment/reply the user clicked "Balas" on.
    const replyTarget =
        [comment, ...replies].find((item) => item.id === replyTargetId) ??
        comment;

    const renderReply = (reply: BlogPostComment) => (
        <div key={reply.id} className="relative mt-3 flex gap-3">
            <CommentAvatar
                avatarUrl={reply.user?.avatar}
                initials={reply.user?.initials}
                size="sm"
            />

            <div className="flex-1 space-y-1">
                <div className="flex items-center justify-between gap-4">
                    <div className="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <span className="text-sm font-bold text-foreground">
                            {reply.user?.name ?? 'Pengguna'}
                        </span>
                        <span className="text-xs text-muted-foreground">
                            {reply.createdAtLabel}
                        </span>
                    </div>

                    {reply.canDelete && (
                        <button
                            onClick={() =>
                                setCommentToDelete({ id: reply.id, kind: 'balasan' })
                            }
                            className="cursor-pointer text-muted-foreground transition-colors hover:text-destructive"
                            title="Hapus balasan"
                        >
                            <Trash2 className="size-3.5" />
                        </button>
                    )}
                </div>

                {reply.replyToUser && (
                    <p className="text-xs font-medium text-muted-foreground">
                        Membalas @{reply.replyToUser.name}
                    </p>
                )}

                <p className="text-sm whitespace-pre-line text-foreground">
                    {reply.content}
                </p>

                {allowComments !== false && (
                    <div className="pt-1">
                        <button
                            onClick={() => openReplyForm(reply)}
                            className="cursor-pointer text-xs font-semibold text-primary hover:underline"
                        >
                            Balas
                        </button>
                    </div>
                )}
            </div>
        </div>
    );

    const pendingDeleteLabel = commentToDelete
        ? KIND_LABEL[commentToDelete.kind]
        : '';

    return (
        <div className="space-y-3 py-4">
            <div className="flex gap-3">
                <CommentAvatar
                    avatarUrl={comment.user?.avatar}
                    initials={comment.user?.initials}
                    size="md"
                />

                <div className="flex-1 space-y-1">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                            <span className="text-sm font-bold text-foreground">
                                {comment.user?.name ?? 'Pengguna'}
                            </span>
                            <span className="text-xs text-muted-foreground">
                                {comment.createdAtLabel}
                            </span>
                        </div>

                        {comment.canDelete && (
                            <button
                                onClick={() =>
                                    setCommentToDelete({
                                        id: comment.id,
                                        kind: 'komentar',
                                    })
                                }
                                className="cursor-pointer text-muted-foreground transition-colors hover:text-destructive"
                                title="Hapus komentar"
                            >
                                <Trash2 className="size-4" />
                            </button>
                        )}
                    </div>

                    <p className="text-sm whitespace-pre-line text-foreground">
                        {comment.content}
                    </p>

                    {allowComments !== false && (
                        <div className="pt-1">
                            <button
                                onClick={() => openReplyForm(comment)}
                                className="cursor-pointer text-xs font-semibold text-primary hover:underline"
                            >
                                Balas
                            </button>
                        </div>
                    )}
                </div>
            </div>

            {/* Single reply displayed directly under the comment */}
            {firstReply ? (
                <div className={`space-y-3 ${REPLIES_WRAPPER_CLASS}`}>
                    {renderReply(firstReply)}
                </div>
            ) : null}

            {/* Toggle button for remaining replies */}
            {replies.length > 1 ? (
                <div className={REPLIES_WRAPPER_CLASS}>
                    <button
                        type="button"
                        onClick={() => setShowReplies((current) => !current)}
                        className="flex cursor-pointer items-center gap-1 py-1 text-xs font-semibold text-primary hover:underline"
                    >
                        {showReplies ? (
                            <span>Sembunyikan balasan</span>
                        ) : (
                            <span>
                                Lihat {replies.length - 1} balasan lainnya
                            </span>
                        )}
                    </button>
                </div>
            ) : null}

            {/* Remaining replies if expanded */}
            {showReplies && remainingReplies.length > 0 ? (
                <div className={`space-y-4 ${REPLIES_WRAPPER_CLASS}`}>
                    {remainingReplies.map((reply) => renderReply(reply))}
                </div>
            ) : null}

            {/* Reply Input Form */}
            {isReplying && allowComments !== false ? (
                <div className={`pt-2 ${REPLIES_WRAPPER_CLASS}`}>
                    {currentUser ? (
                        <div className="space-y-3">
                            <p className="text-xs text-muted-foreground">
                                Membalas{' '}
                                <span className="font-semibold text-foreground">
                                    {replyTarget.user?.name ?? 'komentar ini'}
                                </span>
                            </p>
                            <CommentInput
                                // Reset internal form state when switching reply targets.
                                key={`${comment.id}-${replyTargetId}`}
                                articleSlug={articleSlug}
                                parentId={comment.id}
                                replyToCommentId={replyTargetId}
                                placeholder={`Balas ${replyTarget.user?.name ?? 'komentar ini'}...`}
                                onSuccess={() => setIsReplying(false)}
                                autoFocus
                            />
                        </div>
                    ) : (
                        <GuestCommentPrompt
                            googleLoginUrl={googleLoginUrl}
                            size="xs"
                        />
                    )}
                </div>
            ) : null}

            <Dialog
                open={commentToDelete !== null}
                onOpenChange={(open) => !open && setCommentToDelete(null)}
            >
                <DialogContent className="max-w-sm rounded-lg border border-border bg-card shadow-none">
                    <DialogHeader>
                        <DialogTitle className="text-base font-bold text-foreground">
                            Hapus {pendingDeleteLabel}
                        </DialogTitle>
                        <DialogDescription className="text-xs text-muted-foreground">
                            Apakah Anda yakin ingin menghapus{' '}
                            {pendingDeleteLabel.toLowerCase()} ini? Tindakan ini
                            tidak dapat dibatalkan.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="mt-2 flex gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setCommentToDelete(null)}
                            className="rounded-lg"
                        >
                            Batal
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            onClick={confirmDelete}
                            className="rounded-lg"
                        >
                            Hapus
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
