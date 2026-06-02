import { useScroll, useVelocity, useSpring, useTransform } from 'motion/react';
import React, {
    useCallback,
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';
import { useHasMounted } from '@/hooks/use-has-mounted';

export type VelocityScrollItem =
    | {
          node: React.ReactNode;
          href?: string;
          title?: string;
          ariaLabel?: string;
      }
    | {
          src: string;
          alt?: string;
          href?: string;
          title?: string;
          srcSet?: string;
          sizes?: string;
          width?: number;
          height?: number;
      };

export interface VelocityScrollProps {
    items: VelocityScrollItem[];
    speed?: number;
    direction?: 'left' | 'right' | 'up' | 'down';
    width?: number | string;
    itemHeight?: number;
    gap?: number;
    pauseOnHover?: boolean;
    hoverSpeed?: number;
    fadeOut?: boolean;
    fadeOutColor?: string;
    scaleOnHover?: boolean;
    renderItem?: (item: VelocityScrollItem, key: React.Key) => React.ReactNode;
    ariaLabel?: string;
    className?: string;
    style?: React.CSSProperties;
}

const ANIMATION_CONFIG = {
    SMOOTH_TAU: 0.25,
    MIN_COPIES: 2,
    COPY_HEADROOM: 2,
} as const;

const toCssLength = (value?: number | string): string | undefined =>
    typeof value === 'number' ? `${value}px` : (value ?? undefined);

const cx = (...parts: Array<string | false | null | undefined>) =>
    parts.filter(Boolean).join(' ');

const useResizeObserver = (
    callback: () => void,
    elements: Array<React.RefObject<Element | null>>,
) => {
    useEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        if (!window.ResizeObserver) {
            const handleResize = () => callback();
            window.addEventListener('resize', handleResize);
            callback();

            return () => window.removeEventListener('resize', handleResize);
        }

        const observers = elements.map((ref) => {
            if (!ref.current) {
                return null;
            }

            const observer = new ResizeObserver(callback);
            observer.observe(ref.current);

            return observer;
        });

        callback();

        return () => {
            observers.forEach((observer) => observer?.disconnect());
        };
    }, [callback, elements]);
};

const useImageLoader = (
    seqRef: React.RefObject<HTMLUListElement | null>,
    onLoad: () => void,
) => {
    useEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const images = seqRef.current?.querySelectorAll('img') ?? [];

        if (images.length === 0) {
            onLoad();

            return;
        }

        let remainingImages = images.length;
        const handleImageLoad = () => {
            remainingImages -= 1;

            if (remainingImages === 0) {
                onLoad();
            }
        };

        images.forEach((img) => {
            const htmlImg = img as HTMLImageElement;

            if (htmlImg.complete) {
                handleImageLoad();
            } else {
                htmlImg.addEventListener('load', handleImageLoad, {
                    once: true,
                });
                htmlImg.addEventListener('error', handleImageLoad, {
                    once: true,
                });
            }
        });

        return () => {
            images.forEach((img) => {
                img.removeEventListener('load', handleImageLoad);
                img.removeEventListener('error', handleImageLoad);
            });
        };
    }, [onLoad, seqRef]);
};

const useAnimationLoop = (
    trackRef: React.RefObject<HTMLDivElement | null>,
    targetVelocity: number,
    seqWidth: number,
    seqHeight: number,
    isHovered: boolean,
    hoverSpeed: number | undefined,
    isVertical: boolean,
    velocityFactor: any,
) => {
    const rafRef = useRef<number | null>(null);
    const lastTimestampRef = useRef<number | null>(null);
    const offsetRef = useRef(0);
    const velocityRef = useRef(0);

    useEffect(() => {
        const track = trackRef.current;

        if (!track) {
            return;
        }

        const prefersReduced =
            typeof window !== 'undefined' &&
            window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const seqSize = isVertical ? seqHeight : seqWidth;

        if (seqSize > 0) {
            offsetRef.current =
                ((offsetRef.current % seqSize) + seqSize) % seqSize;
            const transformValue = isVertical
                ? `translate3d(0, ${-offsetRef.current}px, 0)`
                : `translate3d(${-offsetRef.current}px, 0, 0)`;
            track.style.transform = transformValue;
        }

        if (prefersReduced) {
            track.style.transform = isVertical
                ? 'translate3d(0, 0, 0)'
                : 'translate3d(0, 0, 0)';

            return () => {
                lastTimestampRef.current = null;
            };
        }

        const animate = (timestamp: number) => {
            if (lastTimestampRef.current === null) {
                lastTimestampRef.current = timestamp;
            }

            const deltaTime =
                Math.max(0, timestamp - lastTimestampRef.current) / 1000;
            lastTimestampRef.current = timestamp;

            const target =
                isHovered && hoverSpeed !== undefined
                    ? hoverSpeed
                    : targetVelocity;

            const easingFactor =
                1 - Math.exp(-deltaTime / ANIMATION_CONFIG.SMOOTH_TAU);
            velocityRef.current +=
                (target - velocityRef.current) * easingFactor;

            if (seqSize > 0) {
                let moveBy = velocityRef.current * deltaTime;

                // Add scroll velocity factor
                const vFactor = velocityFactor.get();
                const directionFactor = targetVelocity >= 0 ? 1 : -1;
                moveBy += directionFactor * Math.abs(moveBy) * vFactor;

                let nextOffset = offsetRef.current + moveBy;
                nextOffset = ((nextOffset % seqSize) + seqSize) % seqSize;
                offsetRef.current = nextOffset;

                const transformValue = isVertical
                    ? `translate3d(0, ${-offsetRef.current}px, 0)`
                    : `translate3d(${-offsetRef.current}px, 0, 0)`;
                track.style.transform = transformValue;
            }

            rafRef.current = requestAnimationFrame(animate);
        };

        rafRef.current = requestAnimationFrame(animate);

        return () => {
            if (rafRef.current !== null) {
                cancelAnimationFrame(rafRef.current);
                rafRef.current = null;
            }

            lastTimestampRef.current = null;
        };
    }, [
        targetVelocity,
        seqWidth,
        seqHeight,
        isHovered,
        hoverSpeed,
        isVertical,
        trackRef,
        velocityFactor,
    ]);
};

export const VelocityScroll = React.memo<VelocityScrollProps>(
    ({
        items,
        speed = 120,
        direction = 'left',
        width = '100%',
        itemHeight = 28,
        gap = 32,
        pauseOnHover,
        hoverSpeed,
        fadeOut = false,
        fadeOutColor,
        scaleOnHover = false,
        renderItem,
        ariaLabel = 'Scrolling items',
        className,
        style,
    }) => {
        const containerRef = useRef<HTMLDivElement>(null);
        const trackRef = useRef<HTMLDivElement>(null);
        const seqRef = useRef<HTMLUListElement>(null);

        const hasMounted = useHasMounted();

        const [seqWidth, setSeqWidth] = useState<number>(0);
        const [seqHeight, setSeqHeight] = useState<number>(0);
        const [copyCount, setCopyCount] = useState<number>(
            ANIMATION_CONFIG.MIN_COPIES,
        );
        const [isHovered, setIsHovered] = useState<boolean>(false);

        const { scrollY } = useScroll();
        const scrollVelocity = useVelocity(scrollY);
        const smoothVelocity = useSpring(scrollVelocity, {
            damping: 50,
            stiffness: 400,
        });
        const velocityFactor = useTransform(smoothVelocity, [0, 1000], [0, 5], {
            clamp: false,
        });

        const effectiveHoverSpeed = useMemo(() => {
            if (hoverSpeed !== undefined) {
                return hoverSpeed;
            }

            if (pauseOnHover === true) {
                return 0;
            }

            if (pauseOnHover === false) {
                return undefined;
            }

            return 0;
        }, [hoverSpeed, pauseOnHover]);

        const isVertical = direction === 'up' || direction === 'down';

        const targetVelocity = useMemo(() => {
            const magnitude = Math.abs(speed);
            let directionMultiplier: number;

            if (isVertical) {
                directionMultiplier = direction === 'up' ? 1 : -1;
            } else {
                directionMultiplier = direction === 'left' ? 1 : -1;
            }

            const speedMultiplier = speed < 0 ? -1 : 1;

            return magnitude * directionMultiplier * speedMultiplier;
        }, [speed, direction, isVertical]);

        const updateDimensions = useCallback(() => {
            const containerWidth = containerRef.current?.clientWidth ?? 0;
            const sequenceRect = seqRef.current?.getBoundingClientRect?.();
            const sequenceWidth = sequenceRect?.width ?? 0;
            const sequenceHeight = sequenceRect?.height ?? 0;

            if (isVertical) {
                const parentHeight =
                    containerRef.current?.parentElement?.clientHeight ?? 0;

                if (containerRef.current && parentHeight > 0) {
                    const targetHeight = Math.ceil(parentHeight);

                    if (
                        containerRef.current.style.height !==
                        `${targetHeight}px`
                    ) {
                        containerRef.current.style.height = `${targetHeight}px`;
                    }
                }

                if (sequenceHeight > 0) {
                    setSeqHeight(Math.ceil(sequenceHeight));
                    const viewport =
                        containerRef.current?.clientHeight ??
                        parentHeight ??
                        sequenceHeight;
                    const copiesNeeded =
                        Math.ceil(viewport / sequenceHeight) +
                        ANIMATION_CONFIG.COPY_HEADROOM;
                    setCopyCount(
                        Math.max(ANIMATION_CONFIG.MIN_COPIES, copiesNeeded),
                    );
                }
            } else if (sequenceWidth > 0) {
                setSeqWidth(Math.ceil(sequenceWidth));
                const copiesNeeded =
                    Math.ceil(containerWidth / sequenceWidth) +
                    ANIMATION_CONFIG.COPY_HEADROOM;
                setCopyCount(
                    Math.max(ANIMATION_CONFIG.MIN_COPIES, copiesNeeded),
                );
            }
        }, [isVertical]);

        useResizeObserver(updateDimensions, [containerRef, seqRef]);

        useImageLoader(seqRef, updateDimensions);

        useAnimationLoop(
            trackRef,
            targetVelocity,
            seqWidth,
            seqHeight,
            isHovered,
            effectiveHoverSpeed,
            isVertical,
            velocityFactor,
        );

        const cssVariables = useMemo(
            () =>
                ({
                    '--velocity-scroll-gap': `${gap}px`,
                    '--velocity-scroll-itemHeight': `${itemHeight}px`,
                    ...(fadeOutColor && {
                        '--velocity-scroll-fadeColor': fadeOutColor,
                    }),
                }) as React.CSSProperties,
            [gap, itemHeight, fadeOutColor],
        );

        const rootClasses = useMemo(
            () =>
                cx(
                    'relative group',
                    isVertical
                        ? 'overflow-hidden h-full inline-block'
                        : 'overflow-x-hidden',
                    '[--velocity-scroll-gap:32px]',
                    '[--velocity-scroll-itemHeight:28px]',
                    '[--velocity-scroll-fadeColorAuto:#ffffff]',
                    'dark:[--velocity-scroll-fadeColorAuto:#0b0b0b]',
                    scaleOnHover &&
                        'py-[calc(var(--velocity-scroll-itemHeight)*0.1)]',
                    className,
                ),
            [isVertical, scaleOnHover, className],
        );

        const handleMouseEnter = useCallback(() => {
            if (effectiveHoverSpeed !== undefined) {
                setIsHovered(true);
            }
        }, [effectiveHoverSpeed]);

        const handleMouseLeave = useCallback(() => {
            if (effectiveHoverSpeed !== undefined) {
                setIsHovered(false);
            }
        }, [effectiveHoverSpeed]);

        const renderScrollItem = useCallback(
            (
                item: VelocityScrollItem,
                key: React.Key,
                isHiddenCopy = false,
            ) => {
                if (renderItem) {
                    return (
                        <li
                            className={cx(
                                'flex-none text-(length:--velocity-scroll-itemHeight) leading-none',
                                isVertical
                                    ? 'mb-(--velocity-scroll-gap)'
                                    : 'mr-(--velocity-scroll-gap)',
                                scaleOnHover && 'group/item overflow-visible',
                            )}
                            key={key}
                            role="listitem"
                        >
                            {renderItem(item, key)}
                        </li>
                    );
                }

                const isNodeItem = 'node' in item;

                const content = isNodeItem ? (
                    <span
                        className={cx(
                            'inline-flex items-center',
                            'motion-reduce:transition-none',
                            scaleOnHover &&
                                'transition-transform duration-300 ease-in-out group-hover/item:scale-120',
                        )}
                        aria-hidden={
                            !!(item as any).href && !(item as any).ariaLabel
                        }
                    >
                        {(item as any).node}
                    </span>
                ) : (
                    <img
                        className={cx(
                            'block h-(--velocity-scroll-itemHeight) w-auto object-contain',
                            'pointer-events-none [-webkit-user-drag:none]',
                            '[image-rendering:-webkit-optimize-contrast]',
                            'motion-reduce:transition-none',
                            scaleOnHover &&
                                'transition-transform duration-300 ease-in-out group-hover/item:scale-120',
                        )}
                        src={(item as any).src}
                        srcSet={(item as any).srcSet}
                        sizes={(item as any).sizes}
                        width={(item as any).width}
                        height={(item as any).height}
                        alt={(item as any).alt ?? ''}
                        title={(item as any).title}
                        loading="lazy"
                        decoding="async"
                        draggable={false}
                    />
                );

                const itemAriaLabel = isNodeItem
                    ? ((item as any).ariaLabel ?? (item as any).title)
                    : ((item as any).alt ?? (item as any).title);

                const inner = (item as any).href ? (
                    <a
                        className={cx(
                            'inline-flex items-center rounded no-underline',
                            'transition-opacity duration-200 ease-linear',
                            'hover:opacity-80',
                            'focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-current',
                        )}
                        href={(item as any).href}
                        aria-label={itemAriaLabel || 'item link'}
                        tabIndex={isHiddenCopy ? -1 : undefined}
                        target="_blank"
                        rel="noreferrer noopener"
                    >
                        {content}
                    </a>
                ) : (
                    content
                );

                return (
                    <li
                        className={cx(
                            'flex-none text-(length:--velocity-scroll-itemHeight) leading-none',
                            isVertical
                                ? 'mb-(--velocity-scroll-gap)'
                                : 'mr-(--velocity-scroll-gap)',
                            scaleOnHover && 'group/item overflow-visible',
                        )}
                        key={key}
                        role="listitem"
                    >
                        {inner}
                    </li>
                );
            },
            [isVertical, scaleOnHover, renderItem],
        );

        const itemLists = useMemo(
            () =>
                Array.from({ length: copyCount }, (_, copyIndex) => (
                    <ul
                        className={cx(
                            'flex items-center',
                            isVertical && 'flex-col',
                            copyIndex > 0 && 'pointer-events-none',
                        )}
                        key={`copy-${copyIndex}`}
                        role="list"
                        aria-hidden={copyIndex > 0}
                        inert={copyIndex > 0}
                        ref={copyIndex === 0 ? seqRef : undefined}
                    >
                        {items.map((item, itemIndex) =>
                            renderScrollItem(
                                item,
                                `${copyIndex}-${itemIndex}`,
                                copyIndex > 0,
                            ),
                        )}
                    </ul>
                )),
            [copyCount, items, renderScrollItem, isVertical],
        );

        const staticItems = useMemo(
            () =>
                items.map((item, itemIndex) =>
                    renderScrollItem(item, `static-${itemIndex}`),
                ),
            [items, renderScrollItem],
        );

        const containerStyle = useMemo(
            (): React.CSSProperties => ({
                width: isVertical
                    ? toCssLength(width) === '100%'
                        ? undefined
                        : toCssLength(width)
                    : (toCssLength(width) ?? '100%'),
                ...cssVariables,
                ...style,
            }),
            [width, cssVariables, style, isVertical],
        );

        return (
            <div
                ref={containerRef}
                className={rootClasses}
                style={containerStyle}
                role="region"
                aria-label={ariaLabel}
            >
                {!hasMounted ? (
                    <div
                        className={cx(
                            'relative z-0 select-none',
                            isVertical ? 'w-full' : 'overflow-hidden',
                        )}
                    >
                        <ul
                            className={cx(
                                'flex items-center',
                                isVertical ? 'flex-col' : 'w-max',
                            )}
                            ref={seqRef}
                            role="list"
                        >
                            {staticItems}
                        </ul>
                    </div>
                ) : (
                    <>
                        {fadeOut && (
                            <>
                                {isVertical ? (
                                    <>
                                        <div
                                            aria-hidden
                                            className={cx(
                                                'pointer-events-none absolute inset-x-0 top-0 z-10',
                                                'h-[clamp(24px,8%,120px)]',
                                                'bg-[linear-gradient(to_bottom,var(--velocity-scroll-fadeColor,var(--velocity-scroll-fadeColorAuto))_0%,rgba(0,0,0,0)_100%)]',
                                            )}
                                        />
                                        <div
                                            aria-hidden
                                            className={cx(
                                                'pointer-events-none absolute inset-x-0 bottom-0 z-10',
                                                'h-[clamp(24px,8%,120px)]',
                                                'bg-[linear-gradient(to_top,var(--velocity-scroll-fadeColor,var(--velocity-scroll-fadeColorAuto))_0%,rgba(0,0,0,0)_100%)]',
                                            )}
                                        />
                                    </>
                                ) : (
                                    <>
                                        <div
                                            aria-hidden
                                            className={cx(
                                                'pointer-events-none absolute inset-y-0 left-0 z-10',
                                                'w-[clamp(24px,8%,120px)]',
                                                'bg-[linear-gradient(to_right,var(--velocity-scroll-fadeColor,var(--velocity-scroll-fadeColorAuto))_0%,rgba(0,0,0,0)_100%)]',
                                            )}
                                        />
                                        <div
                                            aria-hidden
                                            className={cx(
                                                'pointer-events-none absolute inset-y-0 right-0 z-10',
                                                'w-[clamp(24px,8%,120px)]',
                                                'bg-[linear-gradient(to_left,var(--velocity-scroll-fadeColor,var(--velocity-scroll-fadeColorAuto))_0%,rgba(0,0,0,0)_100%)]',
                                            )}
                                        />
                                    </>
                                )}
                            </>
                        )}

                        <div
                            className={cx(
                                'relative z-0 flex will-change-transform select-none',
                                'motion-reduce:transform-none',
                                isVertical
                                    ? 'h-max w-full flex-col'
                                    : 'w-max flex-row',
                            )}
                            ref={trackRef}
                            onMouseEnter={handleMouseEnter}
                            onMouseLeave={handleMouseLeave}
                        >
                            {itemLists}
                        </div>
                    </>
                )}
            </div>
        );
    },
);

VelocityScroll.displayName = 'VelocityScroll';

export default VelocityScroll;
