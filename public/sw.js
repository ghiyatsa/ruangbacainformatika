const CACHE_NAME = 'ruangbaca-cache-v2';
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
    
    // Do not intercept Inertia partials, internal tooling, or API requests
    const url = new URL(event.request.url);
    if (
        event.request.headers.get('X-Inertia') ||
        url.pathname.startsWith('/api') ||
        url.pathname.startsWith('/sanctum') ||
        url.pathname.startsWith('/_boost')
    ) {
        return;
    }

    // Network-first: the app is server-rendered, so stale HTML/JS would
    // hide new deployments. Always try the network, fall back to cache
    // only when offline.
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response && response.status === 200 && response.type === 'basic') {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return response;
            })
            .catch(() =>
                caches.match(event.request).then(
                    (cachedResponse) =>
                        cachedResponse ??
                        new Response('', { status: 504, statusText: 'Gateway Timeout' }),
                ),
            )
    );
});