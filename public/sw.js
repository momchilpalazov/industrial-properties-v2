// Service Worker за кеширане на ресурси
const CACHE_NAME = 'industrial-properties-v2.9';
const STATIC_CACHE = [
    '/',
    '/favicon.svg',
    '/favicon.ico'
    // Добавете други реални файлове тук, след като ги проверите
];

// Инсталиране на Service Worker
self.addEventListener('install', (event) => {
    console.log('Service Worker: Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Service Worker: Caching basic resources');
                // Кешираме само основните файлове, които със сигурност съществуват
                return cache.addAll(STATIC_CACHE.filter(url => {
                    // Филтрираме само основните файлове
                    return url === '/' || url.includes('favicon');
                }));
            })
            .catch((error) => {
                console.error('Service Worker: Cache addAll failed:', error);
            })
    );
});

// Активиране на Service Worker
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Изтриване на стар кеш:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fetch стратегия - Network First за HTML, Cache First за статични ресурси
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // За HTML файлове използваме Network First
    if (request.destination === 'document') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME)
                        .then((cache) => cache.put(request, responseClone));
                    return response;
                })
                .catch(() => caches.match(request))
        );
        return;
    }

    // За статични ресурси използваме Cache First
    if (request.destination === 'style' || 
        request.destination === 'script' || 
        request.destination === 'image') {
        event.respondWith(
            caches.match(request)
                .then((response) => {
                    if (response) {
                        return response;
                    }
                    return fetch(request)
                        .then((response) => {
                            const responseClone = response.clone();
                            caches.open(CACHE_NAME)
                                .then((cache) => cache.put(request, responseClone));
                            return response;
                        });
                })
        );
        return;
    }

    // За всички останали заявки използваме мрежата
    event.respondWith(fetch(request));
});
