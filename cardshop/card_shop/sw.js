const CACHE_NAME = 'milky-card-v2';
const STATIC_ASSETS = [
  '/card_shop/assets/style.css',
  '/card_shop/assets/app-icon-192.png',
  '/card_shop/assets/app-icon-512.png',
  '/card_shop/manifest.webmanifest'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);
  const isSameOrigin = requestUrl.origin === self.location.origin;
  const isHtmlPage = isSameOrigin && requestUrl.pathname.endsWith('.php');

  if (isHtmlPage) {
    event.respondWith(
      fetch(event.request, { cache: 'no-store' }).catch(() => caches.match('/card_shop/signin.php'))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then(cached => {
      if (cached) {
        return cached;
      }

      return fetch(event.request).then(response => {
        if (!isSameOrigin || response.status !== 200) {
          return response;
        }

        const cloned = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, cloned));
        return response;
      });
    })
  );
});
