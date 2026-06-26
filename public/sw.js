const CACHE_NAME = 'ruangbaca-cache-v1';
const ASSETS_TO_CACHE = [
    '/',
    '/manifest.json',
    '/favicon-32x32.png',
    '/android-chrome-192x192.png',
    '/android-chrome-512x512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(event.request).then((response) => {
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                const responseToCache = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    // Only cache internal non-api requests
                    const url = new URL(event.request.url);
                    if (!url.pathname.startsWith('/api') && !url.pathname.startsWith('/sanctum') && !url.pathname.startsWith('/_boost')) {
                        cache.put(event.request, responseToCache);
                    }
                });
                return response;
            }).catch(() => {
                // Fail silently or handle fallback offline page
            });
        })
    );
});
