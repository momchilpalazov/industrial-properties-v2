// Service Worker за кеширане на ресурси
const CACHE_NAME = 'industrial-properties-v2.8';
const STATIC_CACHE = [
    '/',
    '/build/app.b34add55.css',
    '/build/layout.ea2b205e.css',
    '/build/app.30701032.js',
    '/build/layout.6589baba.js',
    '/build/runtime.806624b5.js',
    '/build/831.2819befb.js',
    '/build/11.c63a6796.js',
    '/build/392.9b9a3e98.js',
    '/build/987.fe0aeba8.js',
    '/build/505.c45fab96.js',
    '/favicon.svg',
    '/favicon.ico',
    '/images/hero-bg.jpg',
    '/images/logo.svg'
];

// Инсталиране на Service Worker
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Кеширане на основни ресурси');
                return cache.addAll(STATIC_CACHE);
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
