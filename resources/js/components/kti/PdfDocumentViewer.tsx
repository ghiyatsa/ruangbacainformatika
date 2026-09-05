import {
    FileText,
    Maximize2,
    Minimize2,
    Download,
    ExternalLink,
} from 'lucide-react';
import { useEffect, useState, useRef, useSyncExternalStore } from 'react';
import { createPortal } from 'react-dom';
import { Button } from '@/components/ui/button';

/**
 * No-op subscriber for `useSyncExternalStore`; the hydration state never
 * changes after the initial render.
 */
function subscribeToNoop(): () => void {
    return () => {};
}

interface PdfDocumentViewerProps {
    fileUrl: string;
    title?: string;
    className?: string;
}

export function PdfDocumentViewer({
    fileUrl,
    title = 'Dokumen Laporan',
    className = '',
}: PdfDocumentViewerProps) {
    const [isFullscreen, setIsFullscreen] = useState(false);
    // `document.body` is unavailable during SSR, so portal rendering is guarded.
    // `useSyncExternalStore` reports the hydrated state without a post-render
    // setState call, which avoids an extra render pass on every mount.
    const mounted = useSyncExternalStore(
        subscribeToNoop,
        () => true,
        () => false,
    );
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Escape' && isFullscreen) {
                setIsFullscreen(false);
            }
        };

        if (isFullscreen) {
            document.body.style.overflow = 'hidden';
            window.addEventListener('keydown', handleKeyDown);
        } else {
            document.body.style.overflow = '';
        }

        return () => {
            document.body.style.overflow = '';
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, [isFullscreen]);

    const viewMode = isFullscreen ? 'view=Fit' : 'view=FitH';
    const pdfUrl = `${fileUrl}#${viewMode}&toolbar=0&navpanes=0&scrollbar=1`;

    const viewerContent = (
        <div
            ref={containerRef}
            className={`flex flex-col bg-card ${
                isFullscreen
                    ? 'fixed inset-0 z-[100] h-screen w-screen'
                    : `overflow-hidden rounded-2xl border border-border/70 shadow-sm ${className}`
            }`}
        >
            {/* Viewer Header / Toolbar */}
            <div
                className={`flex items-center justify-between border-b border-border/60 bg-muted/40 ${isFullscreen ? 'px-4 py-3 sm:px-6' : 'p-2.5 sm:px-4 sm:py-3'}`}
            >
                <div className="flex min-w-0 items-center gap-2.5 pr-2">
                    <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <FileText className="size-4" />
                    </div>
                    <span className="truncate text-sm font-semibold text-foreground">
                        {title}
                    </span>
                </div>

                <div className="flex shrink-0 items-center gap-1.5">
                    <Button
                        asChild
                        variant="outline"
                        size="sm"
                        className="h-8 gap-1.5 rounded-lg text-xs"
                    >
                        <a
                            href={fileUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <ExternalLink className="size-3.5" />
                            <span className="hidden sm:inline">
                                Buka Tab Baru
                            </span>
                        </a>
                    </Button>

                    <Button
                        asChild
                        variant="outline"
                        size="sm"
                        className="h-8 gap-1.5 rounded-lg text-xs"
                    >
                        <a
                            href={fileUrl}
                            download
                            target="_blank"
                            rel="noreferrer"
                        >
                            <Download className="size-3.5" />
                            <span className="hidden sm:inline">Unduh PDF</span>
                        </a>
                    </Button>

                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="size-8 rounded-lg"
                        onClick={() => setIsFullscreen(!isFullscreen)}
                        aria-label={
                            isFullscreen
                                ? 'Keluar layar penuh (Esc)'
                                : 'Layar penuh'
                        }
                    >
                        {isFullscreen ? (
                            <Minimize2 className="size-4" />
                        ) : (
                            <Maximize2 className="size-4" />
                        )}
                    </Button>
                </div>
            </div>

            {/* Embedded PDF iframe / object container with padding */}
            <div
                className={`relative w-full bg-muted/10 ${isFullscreen ? 'h-[calc(100vh-57px)] flex-1 p-0' : 'h-[650px] p-2.5 pb-4 sm:h-[800px] sm:p-4 sm:pb-6'}`}
            >
                <div className="h-full w-full overflow-hidden border border-border/40 bg-background shadow-xs">
                    <object
                        data={pdfUrl}
                        type="application/pdf"
                        className="h-full w-full border-none"
                        aria-label={title}
                    >
                        <iframe
                            src={pdfUrl}
                            title={title}
                            className="h-full w-full border-none"
                            loading="lazy"
                        >
                            <div className="flex h-full w-full flex-col items-center justify-center gap-3 p-6 text-center">
                                <FileText className="size-10 text-muted-foreground/60" />
                                <p className="text-sm text-muted-foreground">
                                    Browser Anda tidak mendukung pratinjau PDF
                                    langsung.
                                </p>
                                <Button asChild size="sm">
                                    <a
                                        href={fileUrl}
                                        download
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        <Download className="mr-1.5 size-4" />
                                        Unduh Dokumen PDF
                                    </a>
                                </Button>
                            </div>
                        </iframe>
                    </object>
                </div>
            </div>
        </div>
    );

    if (isFullscreen && mounted) {
        return createPortal(viewerContent, document.body);
    }

    return viewerContent;
}
