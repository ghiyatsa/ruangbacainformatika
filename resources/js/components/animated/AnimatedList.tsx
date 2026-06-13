import { motion, useInView } from 'motion/react';
import React, { useRef, useState, useEffect, useCallback } from 'react';
import type { ReactNode, MouseEventHandler, UIEvent } from 'react';

interface AnimatedItemProps {
    children: ReactNode;
    delay?: number;
    index: number;
    onMouseEnter?: MouseEventHandler<HTMLDivElement>;
    onClick?: MouseEventHandler<HTMLDivElement>;
}

const AnimatedItem: React.FC<AnimatedItemProps> = ({
    children,
    delay = 0,
    index,
    onMouseEnter,
    onClick,
}) => {
    const ref = useRef<HTMLDivElement>(null);
    const inView = useInView(ref, { amount: 0.5, once: false });

    return (
        <motion.div
            ref={ref}
            data-index={index}
            onMouseEnter={onMouseEnter}
            onClick={onClick}
            initial={{ scale: 0.7, opacity: 0 }}
            animate={
                inView ? { scale: 1, opacity: 1 } : { scale: 0.7, opacity: 0 }
            }
            transition={{ duration: 0.2, delay }}
            className="cursor-pointer"
        >
            {children}
        </motion.div>
    );
};

interface AnimatedListProps<T = any> {
    items?: T[];
    onItemSelect?: (item: T, index: number) => void;
    renderItem?: (item: T, index: number, isSelected: boolean) => ReactNode;
    showGradients?: boolean;
    enableArrowNavigation?: boolean;
    className?: string;
    itemClassName?: string;
    displayScrollbar?: boolean;
    initialSelectedIndex?: number;
    maxHeight?: string;
}

function AnimatedList<T>({
    items = [],
    onItemSelect,
    renderItem,
    showGradients = true,
    enableArrowNavigation = true,
    className = '',
    itemClassName = '',
    displayScrollbar = true,
    initialSelectedIndex = -1,
    maxHeight = '400px',
}: AnimatedListProps<T>) {
    const listRef = useRef<HTMLDivElement>(null);
    const [selectedIndex, setSelectedIndex] =
        useState<number>(initialSelectedIndex);
    const [keyboardNav, setKeyboardNav] = useState<boolean>(false);
    const [topGradientOpacity, setTopGradientOpacity] = useState<number>(0);
    const [bottomGradientOpacity, setBottomGradientOpacity] =
        useState<number>(1);

    const handleItemMouseEnter = useCallback((index: number) => {
        setSelectedIndex(index);
    }, []);

    const handleItemClick = useCallback(
        (item: T, index: number) => {
            setSelectedIndex(index);

            if (onItemSelect) {
                onItemSelect(item, index);
            }
        },
        [onItemSelect],
    );

    const handleScroll = (e: UIEvent<HTMLDivElement>) => {
        const { scrollTop, scrollHeight, clientHeight } =
            e.target as HTMLDivElement;
        setTopGradientOpacity(Math.min(scrollTop / 50, 1));
        const bottomDistance = scrollHeight - (scrollTop + clientHeight);
        setBottomGradientOpacity(
            scrollHeight <= clientHeight ? 0 : Math.min(bottomDistance / 50, 1),
        );
    };

    useEffect(() => {
        if (!enableArrowNavigation) {
            return;
        }

        const handleKeyDown = (e: KeyboardEvent) => {
            if (!items.length) {
                return;
            }

            if (e.key === 'ArrowDown' || (e.key === 'Tab' && !e.shiftKey)) {
                e.preventDefault();
                setKeyboardNav(true);
                setSelectedIndex((prev) =>
                    Math.min(prev + 1, items.length - 1),
                );
            } else if (e.key === 'ArrowUp' || (e.key === 'Tab' && e.shiftKey)) {
                e.preventDefault();
                setKeyboardNav(true);
                setSelectedIndex((prev) => Math.max(prev - 1, 0));
            } else if (e.key === 'Enter') {
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    e.preventDefault();

                    if (onItemSelect) {
                        onItemSelect(items[selectedIndex], selectedIndex);
                    }
                }
            }
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [items, selectedIndex, onItemSelect, enableArrowNavigation]);

    useEffect(() => {
        if (!keyboardNav || selectedIndex < 0 || !listRef.current) {
            return;
        }

        const container = listRef.current;
        const selectedItem = container.querySelector(
            `[data-index="${selectedIndex}"]`,
        ) as HTMLElement | null;

        if (selectedItem) {
            const extraMargin = 50;
            const containerScrollTop = container.scrollTop;
            const containerHeight = container.clientHeight;
            const itemTop = selectedItem.offsetTop;
            const itemBottom = itemTop + selectedItem.offsetHeight;

            if (itemTop < containerScrollTop + extraMargin) {
                container.scrollTo({
                    top: itemTop - extraMargin,
                    behavior: 'smooth',
                });
            } else if (
                itemBottom >
                containerScrollTop + containerHeight - extraMargin
            ) {
                container.scrollTo({
                    top: itemBottom - containerHeight + extraMargin,
                    behavior: 'smooth',
                });
            }
        }

        setKeyboardNav(false);
    }, [selectedIndex, keyboardNav]);

    return (
        <div className={`relative w-full ${className}`}>
            <div
                ref={listRef}
                className={`overflow-y-auto ${
                    displayScrollbar
                        ? '[&::-webkit-scrollbar]:w-[4px] [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-muted-foreground/20 [&::-webkit-scrollbar-track]:bg-transparent'
                        : 'scrollbar-hide'
                }`}
                onScroll={handleScroll}
                style={{
                    maxHeight,
                    scrollbarWidth: displayScrollbar ? 'thin' : 'none',
                }}
            >
                <div className="flex flex-col gap-1 p-2">
                    {items.map((item, index) => (
                        <AnimatedItem
                            key={index}
                            delay={index * 0.05}
                            index={index}
                            onMouseEnter={() => handleItemMouseEnter(index)}
                            onClick={() => handleItemClick(item, index)}
                        >
                            {renderItem ? (
                                renderItem(item, index, selectedIndex === index)
                            ) : (
                                <div
                                    className={`rounded-lg bg-muted/50 p-3 ${selectedIndex === index ? 'bg-muted' : ''} ${itemClassName}`}
                                >
                                    <p className="m-0 text-sm">
                                        {(item as any)?.toString() ||
                                            `Item ${index + 1}`}
                                    </p>
                                </div>
                            )}
                        </AnimatedItem>
                    ))}
                </div>
            </div>
            {showGradients && items.length > 0 && (
                <>
                    <div
                        className="ease pointer-events-none absolute top-0 right-0 left-0 h-8 bg-linear-to-b from-popover to-transparent transition-opacity duration-300"
                        style={{ opacity: topGradientOpacity }}
                    ></div>
                    <div
                        className="ease pointer-events-none absolute right-0 bottom-0 left-0 h-12 bg-linear-to-t from-popover to-transparent transition-opacity duration-300"
                        style={{ opacity: bottomGradientOpacity }}
                    ></div>
                </>
            )}
        </div>
    );
}

export default AnimatedList;
