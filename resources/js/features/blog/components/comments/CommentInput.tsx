import { useForm } from '@inertiajs/react';
import { Send } from 'lucide-react';
import {  useRef } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import commentsRoute from '@/routes/blog/comments';
import type {FormEvent} from 'react';

interface CommentInputProps {
    articleSlug: string;
    parentId?: number | null;
    replyToCommentId?: number | null;
    placeholder?: string;
    onSuccess?: () => void;
    autoFocus?: boolean;
    mention?: string;
}

export function CommentInput({
    articleSlug,
    parentId = null,
    replyToCommentId = null,
    placeholder = 'Tulis komentar...',
    onSuccess,
    autoFocus,
    mention,
}: CommentInputProps) {
    const { data, setData, post, processing, reset, errors, setError, clearErrors } = useForm({
        content: mention ? `${mention} ` : '',
        parent_id: parentId,
        reply_to_comment_id: replyToCommentId,
    });

    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const backdropRef = useRef<HTMLDivElement>(null);

    const handleScroll = () => {
        if (textareaRef.current && backdropRef.current) {
            backdropRef.current.scrollTop = textareaRef.current.scrollTop;
            backdropRef.current.scrollLeft = textareaRef.current.scrollLeft;
        }
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        const content = data.content.trim();
        let actualComment = content;

        if (mention && content.startsWith(mention)) {
            actualComment = content.slice(mention.length).trim();
        }

        if (!actualComment) {
            setError('content', 'Komentar wajib diisi.');

            return;
        }

        if (actualComment.length < 3) {
            setError('content', 'Komentar minimal 3 karakter.');

            return;
        }

        if (actualComment.length > 1000) {
            setError('content', 'Komentar maksimal 1000 karakter.');

            return;
        }

        // Must contain at least one letter/number segment in the actual comment text
        if (!/[\p{L}\p{M}0-9]+/u.test(actualComment)) {
            setError('content', 'Komentar wajib ditulis dengan jelas.');

            return;
        }

        post(commentsRoute.store.url(articleSlug), {
            preserveScroll: true,
            onSuccess: () => {
                reset('content', 'reply_to_comment_id');

                if (onSuccess) {
                    onSuccess();
                }
            },
            onError: () => {
                if (!errors.content) {
                    toast.error('Gagal mengirim komentar.');
                }
            },
        });
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-3">
            <div className="relative w-full rounded-lg border border-border bg-background/50 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-colors overflow-hidden">
                {/* Backdrop for highlighting mentions */}
                <div
                    ref={backdropRef}
                    className="absolute inset-0 pointer-events-none select-none p-3 text-sm font-sans leading-relaxed text-transparent whitespace-pre-wrap break-words overflow-hidden"
                    aria-hidden="true"
                >
                    {(() => {
                        if (!mention) {
                            return data.content;
                        }

                        const parts = data.content.split(mention);

                        if (parts.length <= 1) {
                            return data.content;
                        }

                        return parts.reduce<any[]>((acc, part, index) => {
                            if (index === 0) {
                                return [part];
                            }

                            return [
                                ...acc,
                                <mark key={index} className="bg-primary/20 text-transparent rounded px-0.5">
                                    {mention}
                                </mark>,
                                part,
                            ];
                        }, []);
                    })()}
                </div>

                <textarea
                    ref={textareaRef}
                    onScroll={handleScroll}
                    value={data.content}
                    onChange={(e) => {
                        setData('content', e.target.value);

                        if (errors.content) {
                            clearErrors('content');
                        }
                    }}
                    placeholder={placeholder}
                    rows={parentId ? 2 : 3}
                    autoFocus={autoFocus}
                    maxLength={1000}
                    className="relative z-10 w-full resize-none bg-transparent p-3 text-sm font-sans leading-relaxed text-foreground focus:outline-hidden placeholder:text-muted-foreground"
                />
            </div>
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
