# PageBuilder

PageBuilder, sayfaların JSON tabanlı konfigürasyonlarını veritabanında saklar, yönetir ve render eder.

---

## Nasıl Çalışır?

```
1. savePage()  → theme_page_settings + theme_sections tablolarına yazar
2. loadPage()  → DB'den okur, Redis'e cache'ler (TTL: THEME_BUILDER_TTL)
3. renderPage()→ Her section için:
                   a. SectionRegistry'den tip çöz
                   b. fetchData() → MySQL/API
                   c. render()    → Blade HTML
                 Tüm section HTML'lerini birleştir
```

---

## API Referansı

### `savePage()`

```php
use Nlk\Theme\Facades\PageBuilder;

PageBuilder::savePage(
    pageKey:  'home',           // sayfa anahtarı
    tenantId: 'tenant_123',     // tenant kimliği
    pageData: [
        'template'       => 'index',
        'sections_order' => ['hero_1', 'prods_1', 'banner_1'],
        'settings'       => ['bg_color' => '#ffffff'],
        'is_published'   => true,
    ],
    sections: [
        'hero_1' => [
            'type'     => 'hero',
            'settings' => ['height' => 'large', 'autoplay' => true],
        ],
        'prods_1' => [
            'type'     => 'featured-products',
            'settings' => ['products_to_show' => 8, 'source' => 'mysql'],
            'disabled' => false,
        ],
        'banner_1' => [
            'type'     => 'image-banner',
            'settings' => ['zone' => 'ana', 'title' => 'Yeni Sezon'],
        ],
    ]
);
```

### `loadPage()`

```php
$page = PageBuilder::loadPage('home', 'tenant_123');

// Dönen yapı:
// [
//   'sections_order' => ['hero_1', 'prods_1'],
//   'settings'       => [...],
//   'template'       => 'index',
//   'is_published'   => true,
//   'sections'       => [
//     'hero_1' => ['type' => 'hero', 'settings' => [...], 'disabled' => false],
//     ...
//   ]
// ]
```

### `renderPage()`

```php
$html = PageBuilder::renderPage('home', 'tenant_123');
// Tüm section'ların HTML'ini döndürür
```

### `exportJson()`

```php
$json = PageBuilder::exportJson('home', 'tenant_123');
// FlexPage JSON formatında dışa aktarır
```

### `importJson()`

```php
$json = file_get_contents('home.json');
$page = PageBuilder::importJson($json, 'tenant_123');
// ThemePageSetting model döner
```

### `getSectionSchemas()`

```php
$schemas = PageBuilder::getSectionSchemas();
// Tüm kayıtlı section'ların schema() çıktısını döner
// Admin editör için kullanilir
```

### `flushCache()`

```php
PageBuilder::flushCache('home', 'tenant_123');
// Belirli sayfa cache'ini temizler
```

---

## Cache Stratejisi

```
loadPage() çağrıldığında:
  cache key: "theme:page:{tenantId}:{pageKey}"
  TTL: THEME_BUILDER_TTL (default 300 saniye)

savePage() çağrıldığında:
  → Cache otomatik invalidate edilir

Cache driver:
  → config('cache.default') ile belirlenir
  → Redis önerilir (prefix-based invalidation için)
```

---

## FlexPage JSON Formatı

`theme:export` ve `importJson()` bu formatı kullanır:

```json
{
    "page_key": "home",
    "template": "index",
    "settings": {
        "colors_accent": "#121212",
        "typography_font": "Inter"
    },
    "sections": {
        "hero_main": {
            "type": "hero",
            "settings": {
                "height": "large",
                "autoplay": true,
                "interval": 5000
            },
            "block_order": [],
            "disabled": false
        },
        "featured": {
            "type": "featured-products",
            "settings": {
                "title": "Öne Çıkanlar",
                "products_to_show": 8,
                "columns": "4",
                "source": "mysql"
            },
            "disabled": false
        }
    },
    "order": [
        "hero_main",
        "featured"
    ]
}
```

### Alanlar

| Alan | Açıklama |
|---|---|
| `page_key` | Sayfa anahtarı (`home`, `category`, `product`) |
| `template` | Blade template adı |
| `settings` | Sayfa geneli tema ayarları |
| `sections` | Section ID → section config haritası |
| `sections[].type` | Kayıtlı section tipi |
| `sections[].settings` | Section'a özel ayarlar (schema'ya uygun) |
| `sections[].block_order` | İç blok sırası (şimdilik boş) |
| `sections[].disabled` | `true` ise render edilmez |
| `order` | Section'ların render sırası |

---

## PageRenderer — Tekil Section Render

```php
use Nlk\Theme\PageBuilder\PageRenderer;

$renderer = app(PageRenderer::class);

// Tek section render
$html = $renderer->renderSection('home', 'tenant_123', 'hero_main');
```

Blade'de:

```blade
@section_render('hero_main')
```

---

## Çoklu Sayfa Desteği

Her `page_key` bağımsız bir yapılandırmaya sahip olabilir:

```
home          → anasayfa
category      → kategori sayfası
product       → ürün detay
cart          → sepet
checkout      → ödeme
landing-sb24  → özel landing page
```

```php
// Kategori sayfası için ayrı yapılandırma
PageBuilder::savePage('category', $tenantId, [
    'sections_order' => ['cat_banner', 'product_grid', 'pagination'],
], [...]);
```

---

## Tenant İzolasyonu

Her `tenant_id`, kendi bağımsız sayfa konfigürasyonuna sahiptir:

```php
// Tenant A'nın anasayfası
PageBuilder::renderPage('home', 'tenant_A');

// Tenant B'nin anasayfası (tamamen farklı yapı)
PageBuilder::renderPage('home', 'tenant_B');
```

DB sorgularında `WHERE tenant_id = ?` filtresi her zaman uygulanır.
