# Performans (Faz 8)

`CriticalCssExtractor`, `CacheTagManager`, `PwaManifestGenerator`

---

## CriticalCssExtractor

Above-the-fold CSS'i sayfa başına inline enjekte eder (LCP iyileştirmesi).

### Blade Direktifi

```blade
{{-- layout.blade.php <head> içi --}}
@critical_css('home')        {{-- Ana sayfa --}}
@critical_css('category')    {{-- Kategori sayfası --}}
@critical_css('product')     {{-- Ürün detay --}}
@critical_css('cart')        {{-- Sepet --}}
```

### PHP Kullanımı

```php
use Nlk\Theme\Performance\CriticalCssExtractor;

$css = app('nlk.critical_css');

// Sayfa için kritik CSS al (disk → cache → boş string)
$css->get('home');

// <style id="nlk-critical-home">...</style> döndür
$css->renderTag('home');

// CSS dosyasını disk'e yaz (build pipeline'dan veya Artisan komutu ile)
$css->store('home', file_get_contents('/tmp/critical-home.css'));

// Tüm cache'i temizle
$css->flush();
```

### Build Pipeline Entegrasyonu

Kritik CSS'i otomatik üretmek için [critical](https://github.com/addyosmani/critical) veya [penthouse](https://github.com/pocketjoso/penthouse) araçları kullanılabilir:

```bash
# Örnek: Node.js build scripti
npx critical https://magazan.com/ --base public/ --inline false \
  --css public/css/app.css --width 1300 --height 900 \
  --output storage/app/theme/critical-css/home.css
```

### Config

```env
THEME_CRITICAL_CSS_TTL=86400       # Cache süresi (saniye)
THEME_CRITICAL_CSS_PATH=storage/app/theme/critical-css
```

---

## CacheTagManager

Redis tag tabanlı granüler cache invalidation.

### Kullanım Örnekleri

```php
use Nlk\Theme\Performance\CacheTagManager;

$cache = app('nlk.cache_tags');

// Tag'lı cache store
$data = $cache->remember(
    key:      'hero_section_tenant_1',
    ttl:      300,
    callback: fn() => $sectionService->fetchData('hero', 'tenant_1'),
    tags:     ['theme', 'page:home', 'tenant:tenant_1']
);

// Bir tenant'ın tüm page cache'ini temizle
$cache->invalidateTenant('tenant_1');

// Belirli bir sayfayı temizle
$cache->invalidatePage('home', 'tenant_1');

// Belirli section tipini temizle
$cache->invalidateSection('flash-sale', 'tenant_1');

// Tüm theme cache'ini temizle
$cache->flushAll();

// Static factory: Sayfa bazlı tagged cache
CacheTagManager::forPage('home', 'tenant_1')
    ->remember('section_hero', 300, fn() => [...]);
```

### Desteklenen Sürücüler

| Sürücü | Tag Desteği | Notlar |
|---|---|---|
| `redis` | ✅ | Önerilen |
| `memcached` | ✅ | |
| `file` | ❌ | Prefix fallback |
| `array` | ❌ | Test ortamı |
| `database` | ❌ | Cache::forget fallback |

### Config

```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
THEME_CACHE_DRIVER=redis
```

---

## PwaManifestGenerator

PWA Web App Manifest ve Service Worker yönetimi.

### Blade Direktifleri

```blade
{{-- layout.blade.php <head> içi (hem manifest hem SW kaydı) --}}
@pwa_head

{{-- Sadece Service Worker kayıt scripti --}}
@pwa_sw_script
```

### Manifest Endpoint

`routes/web.php`'ye manifest route'u ekleyin:

```php
use Nlk\Theme\Performance\PwaManifestGenerator;

Route::get('/manifest.json', function () {
    return response(app('nlk.pwa')->manifest())
        ->header('Content-Type', 'application/manifest+json');
});

// Service Worker içeriği
Route::get('/sw.js', function () {
    return response(app('nlk.pwa')->swContent())
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/');
});
```

### PHP Kullanımı

```php
$pwa = app('nlk.pwa');

// manifest.json içeriği
$pwa->manifest();

// <head> meta etiketleri + manifest link
$pwa->headTags();

// SW kayıt scripti
$pwa->swScript('/sw.js');

// Temel offline-first SW içeriği
$pwa->swContent();
```

### Config (.env)

```env
PWA_NAME="Mağazam"
PWA_SHORT_NAME="Mağaza"
PWA_DESCRIPTION="En iyi ürünler en uygun fiyatlarla"
PWA_THEME_COLOR=#000000
PWA_BG_COLOR=#ffffff
PWA_DISPLAY=standalone         # standalone | fullscreen | browser
PWA_START_URL=/
PWA_LANG=tr
PWA_MANIFEST_URL=/manifest.json
PWA_SERVICE_WORKER=true        # false: headTags() SW scripti eklemez
PWA_SW_PATH=/sw.js
PWA_OFFLINE_PAGE=/offline
PWA_APPLE_ICON=/icons/apple-touch-icon.png
```

### İkon Yapısı

```
public/
  icons/
    icon-192.png           # 192×192 PNG
    icon-512.png           # 512×512 PNG (maskable)
    apple-touch-icon.png   # 180×180 PNG
  manifest.json            # veya route'a bırakın
  sw.js                    # veya route'a bırakın
  offline.html             # Çevrimdışı sayfası
```

### Lighthouse PWA Kontrol Listesi

- ✅ `manifest.json` varlığı (`@pwa_head`)
- ✅ `theme-color` meta (`@pwa_head`)
- ✅ `apple-touch-icon` (`@pwa_head`)
- ✅ Service Worker kayıt (`PWA_SERVICE_WORKER=true`)
- ✅ HTTPS (hosting tarafından sağlanır)
- ⚠️ İkonlar manuel oluşturulmalıdır (`public/icons/`)
- ⚠️ Offline sayfası oluşturulmalıdır (`/offline`)
