import type { HistoryItem } from './types';

const STORAGE_KEY = 'global_search_recent_items';
const MAX_HISTORY = 6;

export function loadSearchHistory(): HistoryItem[] {
    if (typeof window === 'undefined') {
        return [];
    }

    try {
        const stored = localStorage.getItem(STORAGE_KEY);

        if (!stored) {
            return [];
        }

        const parsed = JSON.parse(stored);

        if (Array.isArray(parsed)) {
            return parsed;
        }
    } catch {
        // Ignore parse errors
    }

    return [];
}

export function saveSearchHistoryItem(
    item: Omit<HistoryItem, 'timestamp'>,
): HistoryItem[] {
    if (typeof window === 'undefined') {
        return [];
    }

    const current = loadSearchHistory();
    const newItem: HistoryItem = {
        ...item,
        timestamp: Date.now(),
    };

    const filtered = current.filter(
        (h) => h.url !== item.url && h.id !== item.id,
    );
    const updated = [newItem, ...filtered].slice(0, MAX_HISTORY);

    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
    } catch {
        // Ignore storage errors
    }

    return updated;
}

export function removeSearchHistoryItem(idOrUrl: string): HistoryItem[] {
    if (typeof window === 'undefined') {
        return [];
    }

    const current = loadSearchHistory();
    const updated = current.filter(
        (h) => h.id !== idOrUrl && h.url !== idOrUrl,
    );

    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
    } catch {
        // Ignore storage errors
    }

    return updated;
}

export function clearAllSearchHistory(): void {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch {
        // Ignore storage errors
    }
}
