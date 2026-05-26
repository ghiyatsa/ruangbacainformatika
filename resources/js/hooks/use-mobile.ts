import * as React from 'react';

const MOBILE_BREAKPOINT = 768;

export function useIsMobile() {
    const [isMobile, setIsMobile] = React.useState<boolean | undefined>(() => {
        if (typeof window === 'undefined') {
            return undefined;
        }

        return window.innerWidth < MOBILE_BREAKPOINT;
    });

    React.useEffect(() => {
        const mediaQuery = window.matchMedia(
            `(max-width: ${MOBILE_BREAKPOINT - 1}px)`,
        );
        const handleChange = () => {
            setIsMobile(window.innerWidth < MOBILE_BREAKPOINT);
        };

        mediaQuery.addEventListener('change', handleChange);

        return () => mediaQuery.removeEventListener('change', handleChange);
    }, []);

    return Boolean(isMobile);
}
