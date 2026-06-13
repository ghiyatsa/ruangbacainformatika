import { ChevronsUpDown } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
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
            }, 0);
        } else {
            timer = window.setTimeout(() => {
                setSheetHeight(MOBILE_SHEET_MIN_HEIGHT);
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

    function handleDragStart(clientY: number): void {
        dragStartYRef.current = clientY;
        dragStartHeightRef.current = sheetHeight;
        setIsDragging(true);
    }

    function handleDragMove(clientY: number): void {
        if (
            dragStartYRef.current === null ||
            dragStartHeightRef.current === null
        ) {
            return;
        }

        const deltaY = clientY - dragStartYRef.current;
        const maxHeight = Math.max(
            MOBILE_SHEET_MIN_HEIGHT,
            window.innerHeight - 24,
        );
        const nextHeight = dragStartHeightRef.current - deltaY;

        setSheetHeight(
            Math.min(maxHeight, Math.max(MOBILE_SHEET_MIN_HEIGHT, nextHeight)),
        );
    }

    function handleDragEnd(): void {
        if (
            dragStartHeightRef.current !== null &&
            dragStartYRef.current !== null &&
            dragStartHeightRef.current - sheetHeight > 0
        ) {
            setIsDragging(false);
            dragStartYRef.current = null;
            dragStartHeightRef.current = null;

            return;
        }

        if (sheetHeight <= MOBILE_SHEET_MIN_HEIGHT + 12) {
            setOpen(false);
        }

        setIsDragging(false);
        dragStartYRef.current = null;
        dragStartHeightRef.current = null;
    }

    const trigger = (
        <div className="flex w-full min-w-0 items-center gap-2 sm:w-auto sm:flex-none">
            <Button
                type="button"
                variant="outline"
                className="h-10 w-full justify-between rounded-lg px-3 text-left font-normal shadow-xs sm:w-[220px] sm:flex-none"
                aria-label={triggerAriaLabel}
                aria-expanded={open}
                role="combobox"
                onClick={() => setOpen(true)}
            >
                <span className="truncate">
                    {selectedOption?.name ?? placeholder}
                </span>
                <ChevronsUpDown className="size-4 shrink-0 text-muted-foreground" />
            </Button>
        </div>
    );

    const commandContent = (
        <Command className="h-full border-none bg-transparent p-1 **:[[data-slot=command-input-wrapper]]:bg-muted/60 **:[[data-slot=command-input-wrapper]]:rounded-lg **:[[data-slot=command-input-wrapper]]:px-2 **:[[data-slot=command-input-wrapper]]:py-1 **:[[data-slot=command-input-wrapper]]:mb-2">
            <CommandInput placeholder={searchPlaceholder} />
            <CommandList
                className={
                    isMobile
                        ? 'h-full max-h-none pb-3'
                        : 'max-h-[min(26rem,calc(100svh-10rem))] pb-3'
                }
            >
                <CommandEmpty>{emptyMessage}</CommandEmpty>
                <CommandItem
                    value={allLabel}
                    data-checked={value === '' ? 'true' : 'false'}
                    onSelect={() => {
                        onValueChange('all');
                        setOpen(false);
                    }}
                >
                    <span>{allLabel}</span>
                </CommandItem>
                {options.map((option) => {
                    const isSelected = option.slug === value;

                    return (
                        <CommandItem
                            key={option.id}
                            value={`${option.name} ${option.slug}`}
                            data-checked={isSelected ? 'true' : 'false'}
                            onSelect={() => {
                                onValueChange(option.slug);
                                setOpen(false);
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
                <Sheet open={open} onOpenChange={setOpen}>
                    <SheetContent
                        side="bottom"
                        className="gap-0 rounded-t-3xl p-0"
                        showCloseButton={false}
                        onOpenAutoFocus={(event) => event.preventDefault()}
                        style={{
                            height: `${sheetHeight}px`,
                            transition: isDragging ? 'none' : 'height 180ms ease-out',
                        }}
                    >
                        <div
                            className="flex justify-center px-4 pt-3 pb-2"
                            role="presentation"
                            onPointerDown={(event) =>
                                handleDragStart(event.clientY)
                            }
                            onPointerMove={(event) =>
                                handleDragMove(event.clientY)
                            }
                            onPointerUp={handleDragEnd}
                            onPointerCancel={handleDragEnd}
                            style={{ touchAction: 'none' }}
                        >
                            <div className="h-1.5 w-12 rounded-full bg-muted-foreground/30" />
                        </div>
                        <div className="flex-1 overflow-hidden px-3 pb-3">
                            {commandContent}
                        </div>
                    </SheetContent>
                </Sheet>
            </>
        );
    }

    return (
        <>
            {trigger}
            <Dialog open={open} onOpenChange={setOpen}>
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
