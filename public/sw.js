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

// Estrategia: Network First — solo cachea assets estáticos; las páginas nunca se interceptan
self.addEventListener('fetch', event => {
    // Solo interceptar requests GET del mismo origen
    if (event.request.method !== 'GET') return;
    if (!event.request.url.startsWith(self.location.origin)) return;

    const url = new URL(event.request.url);

    // Solo actuar sobre assets estáticos (CSS, JS, imágenes, fuentes)
    // Dejar pasar libremente las páginas HTML y rutas de la app
    if (!isStaticAsset(url.pathname)) return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(async () => {
                const cached = await caches.match(event.request);
                if (cached) return cached;
                // Sin caché y sin red: respuesta de error explícita
                return new Response('Sin conexión', { status: 503, statusText: 'Service Unavailable' });
            })
    );
});

function isStaticAsset(pathname) {
    return /\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/.test(pathname);
}
