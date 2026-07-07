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
    'ml-5 sm:ml-[3.25rem]';

type CommentKind = 'komentar' | 'balasan';

const KIND_LABEL: Record<CommentKind, string> = {
    komentar: 'Komentar',
    balasan: 'Balasan',
};

if (typeof window !== 'undefined') {
    try {
        const keysToRemove: string[] = [];

        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);

            if (key && key.startsWith('expanded_comments_')) {
                keysToRemove.push(key);
            }
        }

        keysToRemove.forEach((key) => sessionStorage.removeItem(key));
    } catch (e) {}
}

export function CommentItem({
    comment,
    articleSlug,
    currentUser,
    googleLoginUrl,
    allowComments = true,
}: CommentItemProps) {
    const [isReplying, setIsReplying] = useState(false);
    const storageKey = `expanded_comments_${articleSlug}`;
    const [showReplies, setShowRepliesState] = useState<boolean>(() => {
        try {
            const saved = sessionStorage.getItem(storageKey);

            if (saved) {
                const ids = JSON.parse(saved);

                return Array.isArray(ids) && ids.includes(comment.id);
            }
        } catch (e) {}

        return false;
    });

    const setShowReplies = (value: boolean | ((current: boolean) => boolean)) => {
        setShowRepliesState((current) => {
            const next = typeof value === 'function' ? value(current) : value;

            try {
                const saved = sessionStorage.getItem(storageKey);
                let ids = saved ? JSON.parse(saved) : [];

                if (!Array.isArray(ids)) {
                    ids = [];
                }

                if (next) {
                    if (!ids.includes(comment.id)) {
                        ids.push(comment.id);
                    }
                } else {
                    ids = ids.filter((id: number) => id !== comment.id);
                }

                sessionStorage.setItem(storageKey, JSON.stringify(ids));
            } catch (e) {}

            return next;
        });
    };
    const [replyTargetId, setReplyTargetId] = useState<number>(comment.id);
    const [commentToDelete, setCommentToDelete] = useState<{
        id: number;
        kind: CommentKind;
    } | null>(null);
    const [isLoadingReplies, setIsLoadingReplies] = useState(false);

    const replies = (comment.replies ?? []).filter(
        (reply, index, self) => self.findIndex((r) => r.id === reply.id) === index
    );

    const handleToggleReplies = () => {
        setShowReplies((current) => {
            const next = !current;

            if (next) {
                setIsLoadingReplies(true);
                setTimeout(() => {
                    setIsLoadingReplies(false);
                }, 400);
            }

            return next;
        });
    };

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
        <div key={reply.id} className="relative flex gap-3">
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

                <p className="text-sm whitespace-pre-line text-foreground">
                    {(() => {
                        if (!reply.replyToUser) {
                            return reply.content;
                        }

                        const mention = `@${reply.replyToUser.name}`;
                        const parts = reply.content.split(mention);

                        if (parts.length <= 1) {
                            return reply.content;
                        }

                        return parts.reduce((acc: any[], part: string, index: number) => {
                            if (index === 0) {
                                return [part];
                            }

                            return [
                                ...acc,
                                <span key={index} className="font-semibold text-primary">
                                    {mention}
                                </span>,
                                part,
                            ];
                        }, []);
                    })()}
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

                    <div className="flex items-center gap-2 pt-1">
                        {allowComments !== false && (
                            <button
                                onClick={() => openReplyForm(comment)}
                                className="cursor-pointer text-xs font-semibold text-primary hover:underline"
                            >
                                Balas
                            </button>
                        )}

                        {replies.length > 0 && (
                            <>
                                {allowComments !== false && (
                                    <span className="text-xs text-muted-foreground/40">•</span>
                                )}
                                <button
                                    type="button"
                                    onClick={handleToggleReplies}
                                    className="flex cursor-pointer items-center gap-1 text-xs font-semibold text-primary hover:underline"
                                >
                                    {showReplies ? (
                                        <span>Sembunyikan balasan</span>
                                    ) : (
                                        <span>
                                            Lihat {replies.length} balasan
                                        </span>
                                    )}
                                </button>
                            </>
                        )}
                    </div>
                </div>
            </div>

            {/* Thread children (replies and/or reply input form) */}
            {(showReplies && replies.length > 0) || (isReplying && allowComments !== false) ? (
                <div className={`mt-3 space-y-4 ${REPLIES_WRAPPER_CLASS}`}>
                    {/* Replies list */}
                    {showReplies && replies.length > 0 && (
                        <div className="space-y-4">
                            {isLoadingReplies ? (
                                <div className="space-y-4">
                                    {replies.map((reply) => (
                                        <div key={reply.id} className="flex gap-3 animate-pulse">
                                            <div className="size-8 rounded-full bg-muted" />
                                            <div className="flex-1 space-y-2 py-1">
                                                <div className="h-3 w-1/4 rounded bg-muted" />
                                                <div className="h-4 w-3/4 rounded bg-muted" />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                replies.map((reply) => renderReply(reply))
                            )}
                        </div>
                    )}

                    {/* Reply Input Form */}
                    {isReplying && allowComments !== false && (
                        <div className="pt-2">
                            {currentUser ? (
                                <div className="space-y-2">
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
                                        onSuccess={() => {
                                            setIsReplying(false);
                                            setShowReplies(true);
                                            setIsLoadingReplies(true);
                                            setTimeout(() => {
                                                setIsLoadingReplies(false);
                                            }, 400);
                                        }}
                                        mention={
                                            replyTarget.user && replyTarget.user.id !== currentUser?.id
                                                ? `@${replyTarget.user.name}`
                                                : undefined
                                        }
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
