# Kurulum

## Gereksinimler

| Zorunlu | Versiyon |
|---|---|
| PHP | ^8.5 |
| Laravel | ^13.0 |
| MySQL / MariaDB | 8.0+ |
| Redis (önerilir) | 6.0+ |

---

## 1. Paketi Ekle

```bash
composer require snowsoft/nlktheme
```

---

## 2. Yayınla ve Migrate Et

```bash
# Konfigürasyon
php artisan vendor:publish --provider="Nlk\Theme\ThemeServiceProvider" --tag=config

# Migration dosyaları
php artisan vendor:publish --tag=theme-migrations

# Tabloları oluştur
php artisan migrate
```

Bu komutlar iki tablo oluşturur:

| Tablo | Açıklama |
|---|---|
| `theme_page_settings` | Sayfa yapılandırması (JSON) |
| `theme_sections` | Section satırları ve ayarları |

---

## 3. .env Yapılandırması

```env
# ── Tema ──────────────────────────────────────────
APP_THEME=default
APP_THEME_LAYOUT=layout
APP_THEME_DIR=themes
APP_THEME_URL=themes

# ── PageBuilder ───────────────────────────────────
THEME_BUILDER_STORAGE=database     # 'database' (önerilir) | 'file'
THEME_BUILDER_TTL=300              # cache süresi (saniye)
THEME_DEFAULT_TENANT=default       # multi-tenant yoksa 'default'

# ── Hibrit API ────────────────────────────────────
THEME_API_URL=https://api.example.com
THEME_API_KEY=your_secret_key
THEME_API_TIMEOUT=5

# ── Marketing ─────────────────────────────────────
THEME_TRACKING_ENABLED=true
THEME_GTM_ID=GTM-XXXXXXX
THEME_GA4_ID=G-XXXXXXXXXX
THEME_GADS_ID=AW-XXXXXXXXXX
THEME_FB_PIXEL_ID=1234567890
THEME_CONSENT_MODE=default         # GDPR: 'default' | non-GDPR: 'granted'
```

---

## 4. ServiceProvider

Laravel auto-discovery sayesinde paketi **manuel eklemene gerek yok**. Eğer auto-discovery kapalıysa:

```php
// config/app.php
'providers' => [
    Nlk\Theme\ThemeServiceProvider::class,
],
'aliases' => [
    'Theme'       => Nlk\Theme\Facades\Theme::class,
    'PageBuilder' => Nlk\Theme\Facades\PageBuilder::class,
    'Seo'         => Nlk\Theme\Facades\Seo::class,
    'Tracking'    => Nlk\Theme\Facades\Tracking::class,
],
```

---

## 5. Tema Klasörü

```bash
# Yeni tema oluştur
php artisan theme:create my-store

# Oluşturulan yapı:
# themes/my-store/
# ├── config.php
# ├── theme.json
# ├── views/
# │   ├── layout.blade.php
# │   └── index.blade.php
# └── assets/
```

---

## 6. İlk Sayfa Layout'u

```blade
{{-- themes/my-store/views/layout.blade.php --}}
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @seo_head         {{-- Meta, OG, JSON-LD --}}
    @tracking_head    {{-- GTM/GA4/Pixel init --}}

    @styles
</head>
<body>
    @tracking_body    {{-- GTM noscript --}}

    @partial('header')

    <main>
        @yield('content')
    </main>

    @partial('footer')

    @scripts
    @tracking_events  {{-- E-commerce eventleri --}}
</body>
</html>
```

```blade
{{-- themes/my-store/views/index.blade.php --}}
@extends('theme::layout')

@section('content')
    @page_render('home', $tenantId)
@endsection
```

---

## 7. İlk Sayfa Verisi (JSON Import)

```bash
php artisan theme:import src/templates/home.json --tenant=default
```

Veya programatik:

```php
// routes/web.php veya seeder
use Nlk\Theme\Facades\PageBuilder;

PageBuilder::savePage('home', 'default', [
    'template'       => 'index',
    'sections_order' => ['hero', 'products'],
], [
    'hero' => [
        'type'     => 'hero',
        'settings' => ['height' => 'large', 'autoplay' => true],
    ],
    'products' => [
        'type'     => 'featured-products',
        'settings' => ['products_to_show' => 8, 'source' => 'mysql'],
    ],
]);
```
