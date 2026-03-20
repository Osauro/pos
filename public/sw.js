const CACHE_NAME = 'tpv-cache-v1';

// Archivos que se cachean en la instalación del SW
const PRECACHE_URLS = [
    '/',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_URLS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Estrategia: Network First — siempre intenta la red; si falla, usa caché
self.addEventListener('fetch', event => {
    // Solo interceptar requests GET del mismo origen
    if (event.request.method !== 'GET') return;
    if (!event.request.url.startsWith(self.location.origin)) return;

    // Evitar requests de Livewire y rutas dinámicas de la app
    const url = new URL(event.request.url);
    if (url.pathname.startsWith('/livewire') || url.pathname.startsWith('/sanctum')) return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Cachear solo respuestas exitosas de recursos estáticos
                if (response.ok && isStaticAsset(url.pathname)) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});

function isStaticAsset(pathname) {
    return /\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/.test(pathname);
}
