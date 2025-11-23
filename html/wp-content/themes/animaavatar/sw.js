const CACHE_NAME = 'anima-cache-v2';
const ASSETS_TO_CACHE = [
    '/',
    '/wp-content/themes/animaavatar/style.css',
    '/wp-content/plugins/anima-engine/assets/css/matrix-chat.css',
    '/wp-includes/js/jquery/jquery.min.js'
];

// Install Event: Cache Core Assets
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

// Activate Event: Clean Old Caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fetch Event: Stale-While-Revalidate
self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests or non-GET
    if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // Network First for HTML (to get fresh content), Fallback to Cache
    if (event.request.headers.get('Accept').includes('text/html')) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request).then((response) => {
                        return response || caches.match('/'); // Fallback to home if offline
                    });
                })
        );
        return;
    }

    // Stale-While-Revalidate for Assets (CSS, JS, Images)
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            const fetchPromise = fetch(event.request).then((networkResponse) => {
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, networkResponse.clone());
                });
                return networkResponse;
            });
            return cachedResponse || fetchPromise;
        })
    );
});
