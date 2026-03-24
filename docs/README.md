# snowsoft/nlktheme — Dokümantasyon

**Versiyon:** 2.0  **PHP:** ^8.5  **Laravel:** ^13.0  
**Paket:** `composer require snowsoft/nlktheme`

---

## 📚 Bölümler

| # | Dosya | Konu |
|---|---|---|
| 1 | [INSTALLATION.md](./INSTALLATION.md) | Kurulum ve ilk yapılandırma |
| 2 | [ARCHITECTURE.md](./ARCHITECTURE.md) | Sistem mimarisi ve bileşenler |
| 3 | [FLEXPAGE.md](./FLEXPAGE.md) | FlexPage section motoru |
| 4 | [PAGEBUILDER.md](./PAGEBUILDER.md) | JSON tabanlı sayfa yöneticisi |
| 5 | [DATA_ADAPTERS.md](./DATA_ADAPTERS.md) | MySQL + REST API hibrit veri katmanı |
| 6 | [SEO.md](./SEO.md) | SEO yöneticisi ve Rich Snippet |
| 7 | [TRACKING.md](./TRACKING.md) | GTM · GA4 · Google Ads · Facebook Pixel |
| 8 | [BLADE.md](./BLADE.md) | Tüm Blade direktifleri |
| 9 | [CONFIGURATION.md](./CONFIGURATION.md) | config/theme.php referansı |
| 10 | [SECTIONS.md](./SECTIONS.md) | Özel section nasıl yazılır |
| 11 | [ARTISAN_COMMANDS.md](./ARTISAN_COMMANDS.md) | CLI komutları |
| 12 | [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) | Sık karşılaşılan sorunlar |

---

## Hızlı Başlangıç (30 saniye)

```bash
composer require snowsoft/nlktheme
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=theme-migrations && php artisan migrate
```

```env
THEME_GTM_ID=GTM-XXXXXXX
THEME_FB_PIXEL_ID=1234567890
THEME_API_URL=https://api.example.com
```

```blade
{{-- layout.blade.php --}}
<head>
    @seo_head
    @tracking_head
</head>
<body>
    @tracking_body
    @page_render('home', $tenantId)
    @tracking_events
</body>
```

---

## Mimari Özeti

```
snowsoft/nlktheme
│
├── FlexPage Engine       ← JSON-driven section sistemi (Nlk\Theme\FlexPage\*)
│   ├── SectionRegistry   ← section tip kaydı
│   ├── AbstractSection   ← temel section sınıfı
│   ├── DataAdapters/     ← MySQL + REST API + Hybrid
│   └── Sections/         ← 7 built-in section tipi
│
├── PageBuilder           ← DB tabanlı sayfa yöneticisi
│   ├── PageBuilder       ← load/save/render/import/export
│   └── PageRenderer      ← tekil section render
│
├── SEO Engine            ← meta, OG, Twitter Card, JSON-LD
│   ├── SeoManager        ← fluent API
│   ├── SchemaGenerator   ← Organization, Breadcrumb, Article, Video
│   └── ProductSchema     ← Product, Offer, AggregateRating
│
├── Tracking Engine       ← marketing pixel yönetimi
│   └── TrackingManager   ← GTM, GA4, Google Ads, Facebook Pixel
│
├── Facades/              ← PageBuilder, Seo, Tracking
├── Commands/             ← artisan theme:* komutları
└── Database/             ← migrations + Eloquent models
```
