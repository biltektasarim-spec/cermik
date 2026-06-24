const CACHE_NAME = 'rehber-v1';
const ASSETS = [
  '/',
  '/index.php',
  '/assets/css/style.css',
  '/assets/js/app.js',
  '/rotarehber2.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
});

// Fetch event with Network-First strategy
self.addEventListener('fetch', event => {
  // We only want to cache GET requests
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // If network request matches, clone and put it in cache
        const resClone = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, resClone);
        });
        return response;
      })
      .catch(() => {
        // If network fails (offline), try to return from cache
        return caches.match(event.request);
      })
  );
});
