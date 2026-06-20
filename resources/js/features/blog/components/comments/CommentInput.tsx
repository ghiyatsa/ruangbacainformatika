import { useForm } from '@inertiajs/react';
import { Send } from 'lucide-react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import commentsRoute from '@/routes/blog/comments';
import type { FormEvent } from 'react';

interface CommentInputProps {
    articleSlug: string;
    parentId?: number | null;
    replyToCommentId?: number | null;
    placeholder?: string;
    onSuccess?: () => void;
    autoFocus?: boolean;
}

export function CommentInput({
    articleSlug,
    parentId = null,
    replyToCommentId = null,
    placeholder = 'Tulis komentar...',
    onSuccess,
    autoFocus,
}: CommentInputProps) {
    const { data, setData, post, processing, reset, errors } = useForm({
        content: '',
        parent_id: parentId,
        reply_to_comment_id: replyToCommentId,
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        if (!data.content.trim()) {
            return;
        }

        post(commentsRoute.store.url(articleSlug), {
            preserveScroll: true,
            onSuccess: () => {
                reset('content', 'reply_to_comment_id');

                if (onSuccess) {
                    onSuccess();
                }

                toast.success('Komentar berhasil dikirim.');
            },
            onError: () => {
                toast.error('Gagal mengirim komentar.');
            },
        });
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-3">
            <textarea
                value={data.content}
                onChange={(e) => setData('content', e.target.value)}
                placeholder={placeholder}
                rows={parentId ? 2 : 3}
                autoFocus={autoFocus}
                maxLength={1000}
                className="w-full resize-none rounded-lg border border-border bg-background/50 p-3 text-sm transition-colors placeholder:text-muted-foreground focus:border-primary focus:outline-hidden"
            />
            {errors.content && (
                <p className="text-xs text-destructive">{errors.content}</p>
            )}
            <div className="flex justify-end gap-2">
                {parentId && onSuccess && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={onSuccess}
                        className="h-8 rounded-lg"
                    >
                        Batal
                    </Button>
                )}
                <Button
                    type="submit"
                    disabled={processing || !data.content.trim()}
                    size="sm"
                    className="h-8 gap-1.5 rounded-lg"
                >
                    <Send className="size-3.5" />
                    {processing ? 'Mengirim...' : parentId ? 'Balas' : 'Kirim'}
                </Button>
            </div>
        </form>
    );
}
