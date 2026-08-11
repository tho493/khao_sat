const CACHE_NAME = 'sdu-survey-cache-v3';
const ASSETS_TO_CACHE = [
  '/',
  '/favicon.ico',
  '/image/logo.png',
  '/css/splash-screen.css',
  '/js/splash-screen.js'
];

const CDN_HOSTS = [
  'cdn.tailwindcss.com',
  'fonts.googleapis.com',
  'fonts.gstatic.com',
  'unpkg.com',
  'cdn.jsdelivr.net',
  'code.jquery.com'
];

// Install Service Worker and cache core static assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate and clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch events: Stale-While-Revalidate for pages, cache-first for static assets & CDNs
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') {
    return;
  }

  const url = new URL(event.request.url);
  const isSameOrigin = url.origin === self.location.origin;
  const isCDN = CDN_HOSTS.includes(url.hostname);

  if (!isSameOrigin && !isCDN) {
    return;
  }

  // For administrative or dynamic submission routes, always fetch from network (do not use cache)
  if (isSameOrigin && (url.pathname.startsWith('/admin') || url.pathname.startsWith('/khao-sat/submit') || url.pathname.includes('/api/'))) {
    event.respondWith(fetch(event.request));
    return;
  }

  // Check if it is a static asset (CSS, JS, images, fonts, CDN resources)
  const isStaticAsset = isCDN || (
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.js') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.jpg') ||
    url.pathname.endsWith('.jpeg') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.ico') ||
    url.pathname.endsWith('.woff') ||
    url.pathname.endsWith('.woff2') ||
    url.pathname.endsWith('.ttf')
  );

  if (isStaticAsset) {
    // Cache-first strategy for static assets & CDN assets
    event.respondWith(
      caches.match(event.request).then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(event.request).then(networkResponse => {
          if (networkResponse && (networkResponse.status === 200 || networkResponse.type === 'opaque')) {
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, responseToCache);
            });
          }
          return networkResponse;
        }).catch(() => {
          // Silent catch for network failure
        });
      })
    );
  } else {
    // Stale-While-Revalidate strategy for HTML pages:
    // Serve cached HTML immediately (instant Splash display), fetch fresh page in background
    event.respondWith(
      caches.open(CACHE_NAME).then(cache => {
        return cache.match(event.request).then(cachedResponse => {
          const fetchPromise = fetch(event.request).then(networkResponse => {
            if (networkResponse && networkResponse.status === 200) {
              cache.put(event.request, networkResponse.clone());
            }
            return networkResponse;
          }).catch(() => {
            // Network failed, fallback gracefully
          });

          return cachedResponse || fetchPromise;
        });
      })
    );
  }
});
