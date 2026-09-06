import { useEffect, useSyncExternalStore } from 'react';
import type { BookCollectionViewMode } from '@/features/welcome/components/BookCollectionViewToggle';

const STORAGE_KEY = 'ruangbaca:book_view_mode';
const listeners = new Set<() => void>();

function getStoredViewMode(): BookCollectionViewMode {
    if (typeof window === 'undefined') {
        return 'grid';
    }

    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored === 'list' ? 'list' : 'grid';
    } catch {
        return 'grid';
    }
}

let currentViewMode: BookCollectionViewMode = getStoredViewMode();

const subscribe = (callback: () => void) => {
    listeners.add(callback);
    return () => listeners.delete(callback);
};

export function useBookCollectionViewMode(): readonly [
    BookCollectionViewMode,
    (mode: BookCollectionViewMode) => void,
] {
    const viewMode = useSyncExternalStore(
        subscribe,
        () => currentViewMode,
        () => 'grid' as BookCollectionViewMode,
    );

    const setViewMode = (nextMode: BookCollectionViewMode) => {
        if (currentViewMode === nextMode) {
            return;
        }

        currentViewMode = nextMode;
        try {
            localStorage.setItem(STORAGE_KEY, nextMode);
        } catch {
            // Abaikan jika localStorage tidak diizinkan di browser/private tab
        }

        listeners.forEach((listener) => listener());
    };

    useEffect(() => {
        const handleStorage = (event: StorageEvent) => {
            if (event.key === STORAGE_KEY && event.newValue) {
                const nextMode: BookCollectionViewMode = event.newValue === 'list' ? 'list' : 'grid';
                if (currentViewMode !== nextMode) {
                    currentViewMode = nextMode;
                    listeners.forEach((listener) => listener());
                }
            }
        };

        window.addEventListener('storage', handleStorage);
        return () => window.removeEventListener('storage', handleStorage);
    }, []);

    return [viewMode, setViewMode];
}
