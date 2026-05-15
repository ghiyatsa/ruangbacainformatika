import { useSyncExternalStore } from 'react';

const subscribe = () => () => {};

const getSnapshot = () => true;

const getServerSnapshot = () => false;

/**
 * Hook to detect if the component has mounted on the client.
 * Uses useSyncExternalStore for safe hydration and to avoid ESLint warnings
 * about calling setState in useEffect.
 */
export function useHasMounted() {
    return useSyncExternalStore(subscribe, getSnapshot, getServerSnapshot);
}
