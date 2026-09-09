const CACHE_NAME = 'stockflow-v2';
const urlsToCache = [
  '/',
  '/css/style.css',
  '/css/dark-mode.css',
  '/manifest.json'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  // Safari bloqueia e dá erro se tentarmos interceptar POST ou requisições de outras origens no SW básico
  if (event.request.method !== 'GET') return;

  // Ignorar requisições de navegação para evitar o erro "Response served by service worker has redirections" no Safari
  if (event.request.mode === 'navigate') {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        return response || fetch(event.request);
      })
      .catch(() => {
        // Fallback passivo para não quebrar a página no Safari se falhar a rede
        return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
      })
  );
});
