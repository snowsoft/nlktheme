# Konfigürasyon Referansı

Tüm ayarlar `config/theme.php` dosyasında, `.env` değişkenleriyle kontrol edilir.

---

## `themeDefault`

```php
'themeDefault' => env('APP_THEME', 'default'),
```

Aktif tema klasör adı. `themes/{themeDefault}/` konumunda aranır.

---

## `layoutDefault`

```php
'layoutDefault' => env('APP_THEME_LAYOUT', 'layout'),
```

Varsayılan Blade layout adı (`layout.blade.php`).

---

## `assetUrl` / `themeDir`

```php
'assetUrl'  => env('APP_THEME_URL', 'themes'),
'themeDir'  => env('APP_THEME_DIR', 'themes'),
```

Tema klasörlerinin fiziksel yolu ve URL prefix'i.

---

## `shopify` (FlexPage)

```php
'shopify' => [
    'sections_path'  => 'sections',   // section view alt dizini
    'templates_path' => 'templates',  // JSON template dizini
],
```

> Bu key adı `shopify` olsa da FlexPage engine'e aittir (legacy compat için korunuyor).

---

## `data_sources`

```php
'data_sources' => [
    'mysql'            => true,
    'mysql_connection' => env('DB_CONNECTION', 'mysql'),
    'api'              => env('THEME_API_URL', ''),
    'api_key'          => env('THEME_API_KEY', ''),
    'api_timeout'      => (int) env('THEME_API_TIMEOUT', 5),
],
```

| Key | .env | Açıklama |
|---|---|---|
| `mysql_connection` | `DB_CONNECTION` | Laravel DB bağlantı adı |
| `api` | `THEME_API_URL` | REST API base URL |
| `api_key` | `THEME_API_KEY` | API auth token |
| `api_timeout` | `THEME_API_TIMEOUT` | Saniye (default 5) |

---

## `builder`

```php
'builder' => [
    'storage'        => env('THEME_BUILDER_STORAGE', 'database'),
    'cache_ttl'      => (int) env('THEME_BUILDER_TTL', 300),
    'default_tenant' => env('THEME_DEFAULT_TENANT', 'default'),
],
```

| Key | .env | Açıklama |
|---|---|---|
| `storage` | `THEME_BUILDER_STORAGE` | `database` (önerilir) veya `file` |
| `cache_ttl` | `THEME_BUILDER_TTL` | Cache süresi (saniye) |
| `default_tenant` | `THEME_DEFAULT_TENANT` | Multi-tenant yoksa `default` |

---

## `tracking`

```php
'tracking' => [
    'enabled'       => env('THEME_TRACKING_ENABLED', true),
    'gtm_id'        => env('THEME_GTM_ID', ''),
    'ga4_id'        => env('THEME_GA4_ID', ''),
    'google_ads_id' => env('THEME_GADS_ID', ''),
    'fb_pixel_id'   => env('THEME_FB_PIXEL_ID', ''),
    'consent_mode'  => env('THEME_CONSENT_MODE', 'default'),
],
```

| Key | .env | Açıklama |
|---|---|---|
| `enabled` | `THEME_TRACKING_ENABLED` | Tüm tracking açma/kapama |
| `gtm_id` | `THEME_GTM_ID` | Google Tag Manager ID (`GTM-XXXXX`) |
| `ga4_id` | `THEME_GA4_ID` | GA4 Measurement ID (`G-XXXXXXXXXX`). GTM varsa GTM üzerinden yürütün. |
| `google_ads_id` | `THEME_GADS_ID` | Google Ads Conversion ID (`AW-XXXXXXXXX`) |
| `fb_pixel_id` | `THEME_FB_PIXEL_ID` | Facebook Meta Pixel ID |
| `consent_mode` | `THEME_CONSENT_MODE` | `default` (GDPR: denied/deferred) \| `granted` |

---

## `security`

```php
'security' => [
    'csp_header'    => env('THEME_CSP_HEADER', false),
    'x_frame'       => env('THEME_X_FRAME', 'SAMEORIGIN'),
    'xss_protection'=> env('THEME_XSS_PROTECTION', '1; mode=block'),
    'content_type'  => env('THEME_CONTENT_TYPE', 'nosniff'),
],
```

---

## `cache`

```php
'cache' => [
    'key'     => env('THEME_CACHE_KEY', 'theme'),
    'lifetime'=> env('THEME_CACHE_LIFETIME', 1440),
],
```

Tema asset manifest ve config cache'i için kullanılır.

---

## `frontend`

```php
'frontend' => [
    'livewire' => ['enabled' => env('THEME_LIVEWIRE_ENABLED', false)],
    'react'    => ['enabled' => env('THEME_REACT_ENABLED', false)],
    'inertia'  => [
        'enabled'     => env('THEME_INERTIA_ENABLED', false),
        'auto_detect' => true,
    ],
],
```

---

## Tam .env Referansı

```env
# ── Tema ──────────────────────────────────────────────────────
APP_THEME=default
APP_THEME_LAYOUT=layout
APP_THEME_DIR=themes
APP_THEME_URL=themes

# ── Veri Kaynakları ───────────────────────────────────────────
DB_CONNECTION=mysql
THEME_API_URL=https://api.example.com
THEME_API_KEY=secret
THEME_API_TIMEOUT=5

# ── PageBuilder ───────────────────────────────────────────────
THEME_BUILDER_STORAGE=database
THEME_BUILDER_TTL=300
THEME_DEFAULT_TENANT=default

# ── Tracking ──────────────────────────────────────────────────
THEME_TRACKING_ENABLED=true
THEME_GTM_ID=GTM-XXXXXXX
THEME_GA4_ID=G-XXXXXXXXXX
THEME_GADS_ID=AW-XXXXXXXXXX
THEME_FB_PIXEL_ID=1234567890
THEME_CONSENT_MODE=default

# ── Güvenlik ──────────────────────────────────────────────────
THEME_CSP_HEADER=false
THEME_X_FRAME=SAMEORIGIN
THEME_XSS_PROTECTION="1; mode=block"
THEME_CONTENT_TYPE=nosniff

# ── Cache ─────────────────────────────────────────────────────
THEME_CACHE_KEY=theme
THEME_CACHE_LIFETIME=1440

# ── Frontend ──────────────────────────────────────────────────
THEME_LIVEWIRE_ENABLED=false
THEME_REACT_ENABLED=false
THEME_INERTIA_ENABLED=false
```
