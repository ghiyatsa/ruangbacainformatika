import { ChevronsUpDown } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Sheet, SheetContent } from '@/components/ui/sheet';
import { useIsMobile } from '@/hooks/use-mobile';

interface SearchableCatalogFilterOption {
    id: number;
    name: string;
    slug: string;
    booksCount: number;
}

interface SearchableCatalogFilterProps {
    label: string;
    value: string;
    placeholder: string;
    allLabel: string;
    searchPlaceholder: string;
    emptyMessage: string;
    triggerAriaLabel: string;
    options: SearchableCatalogFilterOption[];
    onValueChange: (value: string) => void;
}

export function SearchableCatalogFilter({
    label,
    value,
    placeholder,
    allLabel,
    searchPlaceholder,
    emptyMessage,
    triggerAriaLabel,
    options,
    onValueChange,
}: SearchableCatalogFilterProps) {
    const MOBILE_SHEET_MIN_HEIGHT = 320;
    const isMobile = useIsMobile();
    const [open, setOpen] = useState(false);
    const [sheetHeight, setSheetHeight] = useState<number>(
        MOBILE_SHEET_MIN_HEIGHT,
    );
    const [isDragging, setIsDragging] = useState(false);
    const dragStartYRef = useRef<number | null>(null);
    const dragStartHeightRef = useRef<number | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [limit, setLimit] = useState(50);
    const sheetRef = useRef<HTMLDivElement | null>(null);
    const animationFrameRef = useRef<number | null>(null);

    const filteredOptions = useMemo(() => {
        if (!searchQuery) {
            return options;
        }

        const query = searchQuery.toLowerCase();

        return options.filter((option) =>
            option.name.toLowerCase().includes(query) ||
            option.slug.toLowerCase().includes(query)
        );
    }, [options, searchQuery]);

    const visibleOptions = useMemo(() => {
        return filteredOptions.slice(0, limit);
    }, [filteredOptions, limit]);

    const handleOpenChange = (newOpen: boolean) => {
        setOpen(newOpen);

        if (newOpen) {
            setLimit(50);
            setSearchQuery('');
        }
    };

    const handleSearchQueryChange = (query: string) => {
        setSearchQuery(query);
        setLimit(50);
    };

    const handleScroll = (e: React.UIEvent<HTMLDivElement>) => {
        const target = e.currentTarget;

        if (target.scrollHeight - target.scrollTop <= target.clientHeight + 50) {
            if (limit < filteredOptions.length) {
                setLimit((prev) => prev + 50);
            }
        }
    };

    const selectedOption = useMemo(
        () => options.find((option) => option.slug === value) ?? null,
        [options, value],
    );

    useEffect(() => {
        if (! open || isMobile) {
            return;
        }

        const timeout = window.setTimeout(() => {
            const input = document.querySelector<HTMLInputElement>(
                '[data-slot="command-input"]',
            );

            input?.focus();
        }, 10);

        return () => window.clearTimeout(timeout);
    }, [open, isMobile]);

    useEffect(() => {
        let timer: number | undefined;

        if (open) {
            timer = window.setTimeout(() => {
                setSheetHeight(MOBILE_SHEET_MIN_HEIGHT);

                if (sheetRef.current) {
                    sheetRef.current.style.height = MOBILE_SHEET_MIN_HEIGHT + "px";
                }
            }, 0);
        } else {
            timer = window.setTimeout(() => {
                setSheetHeight(MOBILE_SHEET_MIN_HEIGHT);

                if (sheetRef.current) {
                    sheetRef.current.style.height = MOBILE_SHEET_MIN_HEIGHT + "px";
                }

                setIsDragging(false);
                dragStartYRef.current = null;
                dragStartHeightRef.current = null;
            }, 0);
        }

        return () => {
            if (timer) {
                window.clearTimeout(timer);
            }
        };
    }, [open]);

    useEffect(() => {
        return () => {
            if (animationFrameRef.current !== null) {
                cancelAnimationFrame(animationFrameRef.current);
            }
        };
    }, []);

    const getSnapPoints = () => {
        const low = MOBILE_SHEET_MIN_HEIGHT;
        const high = typeof window !== 'undefined'
            ? Math.max(low + 120, Math.min(720, window.innerHeight * 0.8))
            : 600;

        return { low, high };
    };

    function handleDragStart(event: React.PointerEvent<HTMLDivElement>): void {
        event.currentTarget.setPointerCapture(event.pointerId);
        dragStartYRef.current = event.clientY;
        dragStartHeightRef.current = sheetRef.current ? parseFloat(sheetRef.current.style.height) || sheetHeight : sheetHeight;
        setIsDragging(true);
    }

    function handleDragMove(event: React.PointerEvent<HTMLDivElement>): void {
        if (
            !isDragging ||
            dragStartYRef.current === null ||
            dragStartHeightRef.current === null ||
            !sheetRef.current
        ) {
            return;
        }

        const clientY = event.clientY;
        const startY = dragStartYRef.current;
        const startHeight = dragStartHeightRef.current;

        if (animationFrameRef.current !== null) {
            cancelAnimationFrame(animationFrameRef.current);
        }

        animationFrameRef.current = requestAnimationFrame(() => {
            if (!sheetRef.current) {
return;
}

            const deltaY = clientY - startY;
            const nextHeight = startHeight - deltaY;
            const maxHeight = typeof window !== 'undefined' ? window.innerHeight - 24 : 700;
            const boundedHeight = Math.max(0, Math.min(maxHeight, nextHeight));
            sheetRef.current.style.height = boundedHeight + "px";
        });
    }

    function handleDragEnd(event: React.PointerEvent<HTMLDivElement>): void {
        if (!isDragging) {
            return;
        }

        setIsDragging(false);
        event.currentTarget.releasePointerCapture(event.pointerId);

        const currentDOMHeight = sheetRef.current ? parseFloat(sheetRef.current.style.height) || sheetHeight : sheetHeight;
        const { low, high } = getSnapPoints();

        // jika user drag lebih rendah dari titik pertama (low) otomatis sheet nutup ketika user lepas drag
        if (currentDOMHeight < low - 45) {
            handleOpenChange(false);
        } else {
            // snap ke titik terdekat
            const distToLow = Math.abs(currentDOMHeight - low);
            const distToHigh = Math.abs(currentDOMHeight - high);
            const targetHeight = distToLow < distToHigh ? low : high;

            setSheetHeight(targetHeight);

            if (sheetRef.current) {
                sheetRef.current.style.height = targetHeight + "px";
            }
        }

        dragStartYRef.current = null;
        dragStartHeightRef.current = null;
    }

    const trigger = (
        <div className="flex flex-1 flex-col gap-1.5 sm:flex-none">
            <Button
                variant="outline"
                onClick={() => handleOpenChange(true)}
                className="h-10 w-full justify-between rounded-lg shadow-xs sm:w-48"
                aria-label={triggerAriaLabel}
                role="combobox"
            >
                <span className="truncate">
                    {selectedOption?.name ?? placeholder}
                </span>
                <ChevronsUpDown className="size-4 shrink-0 text-muted-foreground" />
            </Button>
        </div>
    );

    const commandContent = (
        <Command
            shouldFilter={false}
            className="h-full border-none bg-transparent p-1 **:[[data-slot=command-input-wrapper]]:bg-muted/60 **:[[data-slot=command-input-wrapper]]:rounded-lg **:[[data-slot=command-input-wrapper]]:px-2 **:[[data-slot=command-input-wrapper]]:py-1 **:[[data-slot=command-input-wrapper]]:mb-2"
        >
            <CommandInput
                value={searchQuery}
                onValueChange={handleSearchQueryChange}
                placeholder={searchPlaceholder}
            />
            <CommandList
                onScroll={handleScroll}
                className={
                    isMobile
                        ? 'flex-1 min-h-0 max-h-none pb-3'
                        : 'max-h-[min(26rem,calc(100svh-10rem))] pb-3'
                }
            >
                {filteredOptions.length === 0 && (
                    <div className="py-6 text-center text-sm text-muted-foreground">
                        {emptyMessage}
                    </div>
                )}
                {(!searchQuery || allLabel.toLowerCase().includes(searchQuery.toLowerCase())) && (
                    <CommandItem
                        value={allLabel}
                        data-checked={value === '' ? 'true' : 'false'}
                        onSelect={() => {
                            onValueChange('all');
                            handleOpenChange(false);
                        }}
                    >
                        <span>{allLabel}</span>
                    </CommandItem>
                )}
                {visibleOptions.map((option) => {
                    const isSelected = option.slug === value;

                    return (
                        <CommandItem
                            key={option.id}
                            value={option.name + " " + option.slug}
                            data-checked={isSelected ? 'true' : 'false'}
                            onSelect={() => {
                                onValueChange(option.slug);
                                handleOpenChange(false);
                            }}
                        >
                            <span className="truncate">{option.name}</span>
                        </CommandItem>
                    );
                })}
            </CommandList>
        </Command>
    );

    if (isMobile) {
        return (
            <>
                {trigger}
                <Sheet open={open} onOpenChange={handleOpenChange}>
                    <SheetContent
                        side="bottom"
                        className="gap-0 rounded-t-3xl p-0"
                        showCloseButton={false}
                        onOpenAutoFocus={(event) => event.preventDefault()}
                    >
                        <div
                            ref={sheetRef}
                            className="w-full flex flex-col"
                            style={{
                                height: `${sheetHeight}px`,
                                transition: isDragging ? 'none' : 'height 250ms cubic-bezier(0.16, 1, 0.3, 1)',
                            }}
                        >
                            <div
                                className="w-full flex justify-center px-4 py-3 cursor-grab active:cursor-grabbing hover:bg-muted/10 transition-colors"
                                role="presentation"
                                onPointerDown={handleDragStart}
                                onPointerMove={handleDragMove}
                                onPointerUp={handleDragEnd}
                                onPointerCancel={handleDragEnd}
                                style={{ touchAction: 'none' }}
                            >
                                <div className="h-1.5 w-12 rounded-full bg-muted-foreground/30" />
                            </div>
                            <div className="flex-1 overflow-hidden px-3 pb-3">
                                {commandContent}
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>
            </>
        );
    }

    return (
        <>
            {trigger}
            <Dialog open={open} onOpenChange={handleOpenChange}>
                <DialogContent 
                    className="max-h-[min(34rem,calc(100svh-4rem))] overflow-hidden p-2 sm:max-w-lg"
                    showCloseButton={false}
                >
                    <DialogHeader className="sr-only">
                        <DialogTitle>Pilih {label.toLowerCase()}</DialogTitle>
                        <DialogDescription>
                            Cari lalu pilih {label.toLowerCase()} yang ingin
                            ditampilkan.
                        </DialogDescription>
                    </DialogHeader>
                    {commandContent}
                </DialogContent>
            </Dialog>
        </>
    );
}
