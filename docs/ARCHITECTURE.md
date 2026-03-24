# Sistem Mimarisi

## Genel Bakış

`snowsoft/nlktheme` bağımsız bir Laravel paketidir. Uygulamanın veritabanına doğrudan erişir (tenant-aware) ve harici REST API'lerle iletişim kurabilir. BFF (Backend-for-Frontend) katmanı yoktur.

---

## Katmanlar

```
┌─────────────────────────────────────────────────────────────────┐
│               Uygulama (Laravel 13 / PHP 8.5)                   │
│  Controller → Facade → ThemeServiceProvider → Engine            │
└──────────────┬──────────────────────────────────────────────────┘
               │
┌──────────────▼──────────────────────────────────────────────────┐
│                    snowsoft/nlktheme                             │
│                                                                  │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────────────┐   │
│  │  FlexPage   │  │  PageBuilder │  │    SEO + Tracking     │   │
│  │  Engine     │  │  Engine      │  │    Engine             │   │
│  └──────┬──────┘  └──────┬───────┘  └──────────────────────┘   │
│         │                │                                       │
│  ┌──────▼──────────────────────────────────┐                    │
│  │          Hibrit Veri Katmanı            │                    │
│  │  MysqlAdapter  ApiAdapter  HybridAdapter│                    │
│  └──────────────────┬──────────────────────┘                    │
└─────────────────────┼───────────────────────────────────────────┘
                      │
        ┌─────────────┴──────────────┐
        │                            │
   ┌────▼─────┐                ┌─────▼──────┐
   │  MySQL   │                │  REST API  │
   │ (tenant) │                │ (harici)   │
   └──────────┘                └────────────┘
```

---

## Bileşenler

### 1. FlexPage Engine (`src/FlexPage/`)

JSON tabanlı, section odaklı sayfa yapısı motoru.

| Sınıf | Görev |
|---|---|
| `SectionRegistry` | Section tiplerini kayıt eder ve çözer |
| `AbstractSection` | Section temel sınıfı (implements `Section`) |
| `Contracts/Section` | Section interface'i |
| `DataAdapters/MysqlAdapter` | Doğrudan tenant DB sorguları |
| `DataAdapters/ApiAdapter` | Harici REST API çağrıları |
| `DataAdapters/HybridAdapter` | Çift kaynaktan veri birleştirme |
| `Sections/HeroSection` | Hero / slider section |
| `Sections/AnnouncementBarSection` | Duyuru barı |
| `Sections/FeaturedProductsSection` | Öne çıkan ürünler |
| `Sections/BannerSection` | Görsel banner (afisler tablosu) |
| `Sections/CollectionListSection` | Kategori listesi |
| `Sections/RichTextSection` | Zengin metin bloğu |
| `Sections/CustomHtmlSection` | Serbest HTML bloğu |

### 2. PageBuilder Engine (`src/PageBuilder/`)

Sayfa yapılandırmalarını DB'de saklar ve render eder.

| Sınıf | Görev |
|---|---|
| `PageBuilder` | Sayfa CRUD, JSON import/export, render orkestratörü |
| `PageRenderer` | Tekil section render yardımcısı |

### 3. SEO Engine (`src/SEO/`)

| Sınıf | Görev |
|---|---|
| `SeoManager` | Meta, OG, Twitter Card, canonical, preload, JSON-LD |
| `SchemaGenerator` | Organization, BreadcrumbList, Article, Video şemaları |
| `ProductSchema` | Product + Offer + AggregateRating şeması |

### 4. Tracking Engine (`src/Tracking/`)

| Sınıf | Görev |
|---|---|
| `TrackingManager` | GTM Consent Mode v2, GA4, Google Ads, Facebook Pixel |

### 5. Database (`src/Database/`)

| Dosya | Görev |
|---|---|
| `Migrations/..._theme_page_settings_table` | Sayfa config tablosu |
| `Migrations/..._theme_sections_table` | Section satırları tablosu |
| `Models/ThemePageSetting` | Eloquent model (tenant-aware) |
| `Models/ThemeSectionRow` | Eloquent model (section veri) |

### 6. Facades (`src/Facades/`)

| Facade | Alias | Hedef sınıf |
|---|---|---|
| `Theme` | `nlk.theme` | `Nlk\Theme\Theme` |
| `PageBuilder` | `nlk.pagebuilder` | `Nlk\Theme\PageBuilder\PageBuilder` |
| `Seo` | `nlk.seo` | `Nlk\Theme\SEO\SeoManager` |
| `Tracking` | `nlk.tracking` | `Nlk\Theme\Tracking\TrackingManager` |

### 7. Artisan Commands (`src/Commands/`)

`theme:create`, `theme:duplicate`, `theme:list`, `theme:destroy`, `theme:widget`, `theme:export`, `theme:import`

---

## Request Lifecycle

```
HTTP Request
    │
    ▼
Controller (isteğe bağlı)
    │  Seo::title()->ogType()->addProductSchema()
    │  Tracking::viewContent($product)
    ▼
Blade Template
    │  @seo_head          → SeoManager::render()
    │  @tracking_head     → TrackingManager::renderHead()
    │  @tracking_body     → TrackingManager::renderBody()
    │  @page_render(...)  → PageBuilder::renderPage()
    │                         │
    │                         ├─ ThemePageSetting (DB, cache)
    │                         ├─ ThemeSectionRow (DB, cache)
    │                         └─ SectionRegistry::resolve(type)
    │                               │
    │                               ├─ Section::fetchData()
    │                               │    ├─ MysqlAdapter (tenant DB)
    │                               │    └─ ApiAdapter (REST)
    │                               └─ Section::render() → Blade view
    │  @tracking_events   → TrackingManager::renderEvents()
    ▼
HTTP Response (HTML)
```

---

## Veri Tabanı Şeması

### `theme_page_settings`

| Kolon | Tip | Açıklama |
|---|---|---|
| `id` | bigint PK | — |
| `tenant_id` | varchar | Tenant kimliği |
| `page_key` | varchar | `home`, `category`, `product` vb. |
| `template` | varchar | Blade template adı (`index`) |
| `sections_order` | JSON | Section ID sırası: `["hero", "products"]` |
| `settings` | JSON | Sayfa geneli ayarlar (renkler, font vb.) |
| `is_published` | boolean | Yayın durumu |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

### `theme_sections`

| Kolon | Tip | Açıklama |
|---|---|---|
| `id` | bigint PK | — |
| `page_settings_id` | FK → theme_page_settings | — |
| `tenant_id` | varchar | Tenant kimliği |
| `section_id` | varchar | Benzersiz section kimliği (`hero_main`) |
| `type` | varchar | Section tipi (`hero`, `featured-products`) |
| `settings` | JSON | Section'a özel ayarlar |
| `block_order` | JSON | İç blok sırası |
| `position` | int | Sıra numarası |
| `disabled` | boolean | Gizli mi? |
