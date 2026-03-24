<?php

namespace Nlk\Theme\Performance;

/**
 * PwaManifestGenerator — PWA Web App Manifest + Service Worker hook.
 *
 * manifest.json üretir, <head> meta etiketleri ekler,
 * Service Worker kayıt scripti oluşturur.
 *
 * Blade:
 *   @pwa_head         → manifest link + meta + SW register
 *   @pwa_manifest     → manifest.json içeriği
 */
class PwaManifestGenerator
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('theme.pwa', []);
    }

    /**
     * Web App Manifest JSON içeriği.
     */
    public function manifest(): string
    {
        $name      = $this->config['name'] ?? config('app.name', 'My Store');
        $shortName = $this->config['short_name'] ?? substr($name, 0, 12);
        $themeColor= $this->config['theme_color'] ?? '#000000';
        $bgColor   = $this->config['background_color'] ?? '#ffffff';
        $startUrl  = $this->config['start_url'] ?? '/';
        $display   = $this->config['display'] ?? 'standalone';
        $icons     = $this->config['icons'] ?? [
            ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ];

        $categories = $this->config['categories'] ?? ['shopping', 'lifestyle'];

        return json_encode([
            'name'             => $name,
            'short_name'       => $shortName,
            'description'      => $this->config['description'] ?? '',
            'theme_color'      => $themeColor,
            'background_color' => $bgColor,
            'display'          => $display,
            'orientation'      => $this->config['orientation'] ?? 'any',
            'start_url'        => $startUrl,
            'scope'            => $this->config['scope'] ?? '/',
            'lang'             => $this->config['lang'] ?? 'tr',
            'dir'              => $this->config['dir'] ?? 'ltr',
            'categories'       => $categories,
            'icons'            => $icons,
            'screenshots'      => $this->config['screenshots'] ?? [],
            'shortcuts'        => $this->config['shortcuts'] ?? [],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * <head> içine yerleştirilecek PWA meta etiketleri + manifest link.
     */
    public function headTags(): string
    {
        $name       = $this->config['name'] ?? config('app.name', 'My Store');
        $themeColor = $this->config['theme_color'] ?? '#000000';
        $appleIcon  = $this->config['apple_icon'] ?? '/icons/apple-touch-icon.png';
        $manifestUrl= $this->config['manifest_url'] ?? '/manifest.json';
        $swPath     = $this->config['sw_path'] ?? '/sw.js';
        $swEnabled  = (bool)($this->config['service_worker'] ?? false);

        $html  = '<link rel="manifest" href="' . $manifestUrl . '">' . PHP_EOL;
        $html .= '<meta name="theme-color" content="' . htmlspecialchars($themeColor, ENT_QUOTES) . '">' . PHP_EOL;
        $html .= '<meta name="apple-mobile-web-app-capable" content="yes">' . PHP_EOL;
        $html .= '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . PHP_EOL;
        $html .= '<meta name="apple-mobile-web-app-title" content="' . htmlspecialchars($name, ENT_QUOTES) . '">' . PHP_EOL;
        $html .= '<link rel="apple-touch-icon" href="' . htmlspecialchars($appleIcon, ENT_QUOTES) . '">' . PHP_EOL;
        $html .= '<meta name="mobile-web-app-capable" content="yes">' . PHP_EOL;

        if ($swEnabled) {
            $html .= $this->swScript($swPath);
        }

        return $html;
    }

    /**
     * Service Worker kayıt scripti.
     */
    public function swScript(string $swPath = '/sw.js'): string
    {
        return <<<HTML
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('{$swPath}')
      .then(function(reg) { console.log('[SW] Registered:', reg.scope); })
      .catch(function(err) { console.warn('[SW] Registration failed:', err); });
  });
}
</script>
HTML;
    }

    /**
     * Çevrimdışı destek için temel Service Worker içeriği.
     * Bu public/sw.js veya resources/sw.js olarak kaydedilmeli.
     */
    public function swContent(): string
    {
        $name   = $this->config['name'] ?? 'my-store';
        $version = 'v1';
        $offline = $this->config['offline_page'] ?? '/offline';

        return <<<JS
const CACHE_NAME = '{$name}-cache-{$version}';
const OFFLINE_URL = '{$offline}';
const STATIC_ASSETS = [
  '/',
  OFFLINE_URL,
  '/css/app.css',
  '/js/app.js',
];

self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function(cache) {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', function(event) {
  event.waitUntil(
    caches.keys().then(function(keys) {
      return Promise.all(
        keys.filter(function(key) { return key !== CACHE_NAME; })
            .map(function(key) { return caches.delete(key); })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', function(event) {
  if (event.request.method !== 'GET') return;
  event.respondWith(
    fetch(event.request)
      .then(function(response) {
        var clone = response.clone();
        caches.open(CACHE_NAME).then(function(cache) { cache.put(event.request, clone); });
        return response;
      })
      .catch(function() {
        return caches.match(event.request)
          .then(function(cached) { return cached || caches.match(OFFLINE_URL); });
      })
  );
});
JS;
    }
}
