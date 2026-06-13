import { usePage } from '@inertiajs/react';
import { Share2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { useClipboard } from '@/hooks/use-clipboard';
import { cn } from '@/lib/utils';

type SiteProps = {
    name: string;
    site: {
        url: string;
    };
};

interface KtiShareButtonProps {
    title: string;
    subtitle?: string | null;
    kindLabel: string;
    className?: string;
}

export function KtiShareButton({
    title,
    subtitle,
    kindLabel,
    className,
}: KtiShareButtonProps) {
    const page = usePage<SiteProps>();
    const [, copy] = useClipboard();
    const [isSharing, setIsSharing] = useState(false);

    const shareUrl = `${page.props.site.url}${page.url}`;
    const shareTitle = title;
    const shareText = [kindLabel, subtitle].filter(Boolean).join(' • ');

    async function handleShare() {
        if (isSharing) {
            return;
        }

        setIsSharing(true);

        try {
            if (navigator.share) {
                await navigator.share({
                    title: shareTitle,
                    text: shareText,
                    url: shareUrl,
                });

                return;
            }

            const copied = await copy(shareUrl);

            if (copied) {
                toast.success('Tautan berhasil disalin.');

                return;
            }

            toast.error('Tautan belum bisa dibagikan di perangkat ini.');
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return;
            }

            toast.error('Gagal membagikan tautan.');
        } finally {
            setIsSharing(false);
        }
    }

    return (
        <Button
            type="button"
            variant="outline"
            className={cn(
                'h-auto gap-2 rounded-full px-4 py-2 text-sm font-medium',
                className,
            )}
            onClick={() => void handleShare()}
            disabled={isSharing}
        >
            <Share2 className="size-4" />
            Bagikan
        </Button>
    );
}

