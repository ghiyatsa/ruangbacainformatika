import { useMemo, useSyncExternalStore } from 'react';
import * as CatalogBookmarkController from '@/actions/App/Http/Controllers/CatalogBookmarkController';
import { getCsrfToken } from '@/lib/csrf';

const BOOKMARK_STORAGE_KEY = 'ruangbaca:bookmarks';
const BOOKMARK_EVENT_NAME = 'ruangbaca:bookmarks:changed';

export type CatalogBookmarkType =
    | 'book'
    | 'skripsi'
    | 'thesis'
    | 'internship_report'
    | 'post';

export interface CatalogBookmarkRecord {
    catalogType: CatalogBookmarkType;
    id: number;
    href: string;
    title: string;
    subtitle: string | null;
    meta: string | null;
    year: number | null;
    coverImageUrl: string | null;
    kindLabel: string;
    statusLabel: string | null;
}

const EMPTY_BOOKMARKS: CatalogBookmarkRecord[] = [];

let cachedBookmarksRaw: string | null = null;
let cachedBookmarksSnapshot: CatalogBookmarkRecord[] = EMPTY_BOOKMARKS;

function getBookmarkKey(
    bookmark: Pick<CatalogBookmarkRecord, 'catalogType' | 'id'>,
): string {
    return `${bookmark.catalogType}:${bookmark.id}`;
}

function normalizeBookmarkHref(value: unknown): string | null {
    if (typeof value === 'string') {
        return value;
    }

    if (
        typeof value === 'object' &&
        value !== null &&
        'url' in value &&
        typeof value.url === 'string'
    ) {
        return value.url;
    }

    return null;
}

function normalizeBookmarkRecord(value: unknown): CatalogBookmarkRecord | null {
    if (typeof value !== 'object' || value === null) {
        return null;
    }

    const v = value as Record<string, unknown>;
    const href = normalizeBookmarkHref(v.href);

    if (
        typeof v.catalogType !== 'string' ||
        typeof v.id !== 'number' ||
        !Number.isInteger(v.id) ||
        href === null ||
        typeof v.title !== 'string' ||
        (typeof v.subtitle !== 'string' && v.subtitle !== null) ||
        (typeof v.meta !== 'string' && v.meta !== null) ||
        (typeof v.year !== 'number' && v.year !== null) ||
        (typeof v.coverImageUrl !== 'string' && v.coverImageUrl !== null) ||
        typeof v.kindLabel !== 'string' ||
        (typeof v.statusLabel !== 'string' && v.statusLabel !== null)
    ) {
        return null;
    }

    return {
        catalogType: v.catalogType as CatalogBookmarkType,
        id: v.id,
        href,
        title: v.title,
        subtitle: v.subtitle,
        meta: v.meta,
        year: v.year,
        coverImageUrl: v.coverImageUrl,
        kindLabel: v.kindLabel,
        statusLabel: v.statusLabel,
    };
}

function parseBookmarks(value: string | null): CatalogBookmarkRecord[] {
    if (!value) {
        return [];
    }

    try {
        const parsed = JSON.parse(value);

        if (!Array.isArray(parsed)) {
            return [];
        }

        const bookmarks = parsed
            .map((bookmark) => normalizeBookmarkRecord(bookmark))
            .filter(
                (bookmark): bookmark is CatalogBookmarkRecord =>
                    bookmark !== null,
            );

        return Array.from(
            new Map(
                bookmarks.map((bookmark) => [
                    getBookmarkKey(bookmark),
                    bookmark,
                ]),
            ).values(),
        );
    } catch {
        return [];
    }
}

function readBookmarks(): CatalogBookmarkRecord[] {
    if (typeof window === 'undefined') {
        return EMPTY_BOOKMARKS;
    }

    const rawValue = window.localStorage.getItem(BOOKMARK_STORAGE_KEY);

    if (rawValue === cachedBookmarksRaw) {
        return cachedBookmarksSnapshot;
    }

    const parsedBookmarks = parseBookmarks(rawValue);

    cachedBookmarksRaw = rawValue;
    cachedBookmarksSnapshot =
        parsedBookmarks.length > 0 ? parsedBookmarks : EMPTY_BOOKMARKS;

    return cachedBookmarksSnapshot;
}

function notifySubscribers(): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.dispatchEvent(new Event(BOOKMARK_EVENT_NAME));
}

function writeBookmarks(bookmarks: CatalogBookmarkRecord[]): void {
    if (typeof window === 'undefined') {
        return;
    }

    const normalizedBookmarks = Array.from(
        new Map(
            bookmarks
                .map((bookmark) => normalizeBookmarkRecord(bookmark))
                .filter(
                    (bookmark): bookmark is CatalogBookmarkRecord =>
                        bookmark !== null,
                )
                .map((bookmark) => [getBookmarkKey(bookmark), bookmark]),
        ).values(),
    );
    const nextRawValue = JSON.stringify(normalizedBookmarks);

    cachedBookmarksRaw = nextRawValue;
    cachedBookmarksSnapshot =
        normalizedBookmarks.length > 0 ? normalizedBookmarks : EMPTY_BOOKMARKS;

    window.localStorage.setItem(BOOKMARK_STORAGE_KEY, nextRawValue);

    notifySubscribers();
}

function subscribe(onStoreChange: () => void): () => void {
    if (typeof window === 'undefined') {
        return () => undefined;
    }

    const handleStorage = (event: StorageEvent): void => {
        if (event.key === BOOKMARK_STORAGE_KEY) {
            onStoreChange();
        }
    };

    window.addEventListener('storage', handleStorage);
    window.addEventListener(BOOKMARK_EVENT_NAME, onStoreChange);

    return () => {
        window.removeEventListener('storage', handleStorage);
        window.removeEventListener(BOOKMARK_EVENT_NAME, onStoreChange);
    };
}

export interface UseCatalogBookmarksReturn {
    bookmarks: CatalogBookmarkRecord[];
    bookmarkedCount: number;
    isBookmarked: (
        bookmark: Pick<CatalogBookmarkRecord, 'catalogType' | 'id'>,
    ) => boolean;
    toggleBookmark: (bookmark: CatalogBookmarkRecord) => void;
    removeBookmark: (
        bookmark: Pick<CatalogBookmarkRecord, 'catalogType' | 'id'>,
    ) => void;
    clearBookmarks: () => void;
}

export function useCatalogBookmarks(): UseCatalogBookmarksReturn {
    const bookmarks = useSyncExternalStore(
        subscribe,
        readBookmarks,
        () => EMPTY_BOOKMARKS,
    );

    const bookmarkedKeySet = useMemo(
        () => new Set(bookmarks.map((bookmark) => getBookmarkKey(bookmark))),
        [bookmarks],
    );

    const removeBookmark = (
        bookmark: Pick<CatalogBookmarkRecord, 'catalogType' | 'id'>,
    ): void => {
        const bookmarkKey = getBookmarkKey(bookmark);

        writeBookmarks(
            bookmarks.filter(
                (currentBookmark) =>
                    getBookmarkKey(currentBookmark) !== bookmarkKey,
            ),
        );
    };

    const toggleBookmark = (bookmark: CatalogBookmarkRecord): void => {
        const bookmarkKey = getBookmarkKey(bookmark);
        const nextBookmarks = bookmarkedKeySet.has(bookmarkKey)
            ? bookmarks.filter(
                  (currentBookmark) =>
                      getBookmarkKey(currentBookmark) !== bookmarkKey,
              )
            : [bookmark, ...bookmarks];

        writeBookmarks(nextBookmarks);
    };

    return {
        bookmarks,
        bookmarkedCount: bookmarks.length,
        isBookmarked: (
            bookmark: Pick<CatalogBookmarkRecord, 'catalogType' | 'id'>,
        ) => bookmarkedKeySet.has(getBookmarkKey(bookmark)),
        toggleBookmark,
        removeBookmark,
        clearBookmarks: () => writeBookmarks([]),
    };
}

export async function hydrateBookmarksFromServer(): Promise<void> {
    const server = await fetchServerBookmarks();

    if (server === null) {
        return;
    }

    const local = readBookmarks();

    if (local.length === 0 && server.length > 0) {
        writeBookmarks(server);
    }
}

export function pushServerBookmarks(
    bookmarks: CatalogBookmarkRecord[],
): Promise<boolean> {
    if (typeof window === 'undefined') {
        return Promise.resolve(false);
    }

    const csrfToken = getCsrfToken();

    return window
        .fetch(CatalogBookmarkController.replace.url(), {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: JSON.stringify({ bookmarks }),
        })
        .then((response) => response.ok)
        .catch(() => false);
}

async function fetchServerBookmarks(): Promise<CatalogBookmarkRecord[] | null> {
    try {
        const response = await window.fetch(
            CatalogBookmarkController.index.url(),
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        );

        if (!response.ok) {
            return null;
        }

        const payload = (await response.json()) as {
            bookmarks?: unknown;
        };

        return parseBookmarks(JSON.stringify(payload.bookmarks ?? []));
    } catch {
        return null;
    }
}
