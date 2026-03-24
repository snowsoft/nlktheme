# Tüm Sections Referansı

`snowsoft/nlktheme` tarafından sağlanan 27 FlexPage section.

---

## Temel (7)

| Type | Sınıf | Kaynak | Açıklama |
|---|---|---|---|
| `hero` | HeroSection | static | Tam ekran hero banner |
| `announcement-bar` | AnnouncementBarSection | static | Üst duyuru çubuğu |
| `featured-products` | FeaturedProductsSection | mysql/api | Öne çıkan ürünler |
| `image-banner` | BannerSection | static | Görsel banner |
| `collection-list` | CollectionListSection | mysql/api | Koleksiyon listesi |
| `rich-text` | RichTextSection | static | Zengin metin editör |
| `custom-html` | CustomHtmlSection | static | Özel HTML bloğu |

---

## Dönüşüm / E-Ticaret (6)

| Type | Sınıf | Kaynak | Açıklama |
|---|---|---|---|
| `social-proof` | SocialProofSection | static | İzleyici + satış sayısı FOMO |
| `flash-sale` | FlashSaleSection | mysql/api | Geri sayımlı flash indirim |
| `mini-cart` | MiniCartSection | static | AJAX sepet drawer |
| `urgency-badge` | UrgencyBadgeSection | static | "Son X adet kaldı!" |
| `upsell` | UpsellSection | mysql/api | Tamamlayıcı ürün önerileri |
| `recently-viewed` | RecentlyViewedSection | static | localStorage geçmiş |

---

## Müşteri Güveni (3)

| Type | Sınıf | Kaynak | Açıklama |
|---|---|---|---|
| `customer-reviews` | CustomerReviewSection | mysql/api | JSON-LD AggregateRating |
| `trust-badges` | TrustBadgeSection | static | SSL/ödeme/iade rozetleri |
| `faq-accordion` | FaqAccordionSection | static/api | JSON-LD FAQPage SSS |

---

## Arama & Keşif (2)

| Type | Sınıf | Kaynak | Açıklama |
|---|---|---|---|
| `ajax-search` | SearchSection | static | Debounce AJAX instant search |
| `filter-sidebar` | FilterSidebarSection | mysql/api | Fiyat/marka/renk/beden filtre |

---

## Medya & İçerik (4)

| Type | Sınıf | Kaynak | Açıklama |
|---|---|---|---|
| `video-commerce` | VideoCommerceSection | static | YouTube/Vimeo/CDN HLS-DASH |
| `blog-list` | BlogListSection | mysql/api | JSON-LD Article, grid/slider |
| `instagram-feed` | InstagramFeedSection | api | Instagram feed, hover overlay |
| `shoppable-image` | ShoppableImageSection | static | Tıklanabilir hotspot ürün görseli |

---

## Pazarlama & Sadakat (4)

| Type | Sınıf | Kaynak | Açıklama |
|---|---|---|---|
| `email-capture` | EmailCaptureSection | static | Exit intent newsletter popup |
| `loyalty-widget` | LoyaltyWidgetSection | api | Puan bakiyesi ve tier |
| `referral` | ReferralSection | api | Davet kodu, sosyal paylaşım |
| `web-push` | WebPushSection | static | VAPID web push abonelik |

---

## i18n & Market (1)

| Type | Sınıf | Kaynak | Açıklama |
|---|---|---|---|
| `market-switcher` | MarketSwitcherSection | static | Dil + para birimi seçici |

---

## Section JSON Yapısı

Her section şu formatta tanımlanır:

```json
{
  "type": "section-type",
  "id": "unique-section-id",
  "settings": {
    "key": "value"
  },
  "blocks": [
    {
      "type": "block-type",
      "id": "unique-block-id",
      "settings": {
        "key": "value"
      }
    }
  ]
}
```

---

## Yeni Section Oluşturma

```php
namespace App\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

class MyCustomSection extends AbstractSection
{
    public function type(): string { return 'my-custom'; }
    public function dataSource(): string { return 'static'; } // static | mysql | api

    public function schema(): array
    {
        return [
            'name' => 'Özel Section',
            'settings' => [
                ['type' => 'text', 'id' => 'title', 'label' => 'Başlık', 'default' => 'Başlık'],
            ],
            'presets' => [['name' => 'Özel Section', 'category' => 'Custom']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return ['title' => $settings['title'] ?? ''];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('my-custom', $data);
    }
}
```

Ardından `AppServiceProvider` veya bir `boot()` metodunda kaydet:

```php
app('nlk.sections')->register('my-custom', \App\Sections\MyCustomSection::class);
```

View dosyası: `resources/views/theme/sections/my-custom.blade.php`

---

## Kaynak Tiplerine Göre Beklenen Tablo Yapıları

### MySQL — Ürünler

```sql
CREATE TABLE urunler (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id VARCHAR(100) NOT NULL,
  baslik VARCHAR(255),
  fiyat DECIMAL(12,2),
  indirimli_fiyat DECIMAL(12,2),
  marka VARCHAR(100),
  cdn_image_id VARCHAR(100),
  stok INT DEFAULT 0,
  aktif TINYINT(1) DEFAULT 0,
  featured TINYINT(1) DEFAULT 0,
  flash_sale TINYINT(1) DEFAULT 0,
  bestseller TINYINT(1) DEFAULT 0,
  url VARCHAR(500),
  INDEX (tenant_id, aktif),
  INDEX (tenant_id, flash_sale)
);
```

### MySQL — Yorumlar

```sql
CREATE TABLE yorumlar (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id VARCHAR(100) NOT NULL,
  urun_id BIGINT,
  musteri_adi VARCHAR(100),
  yorum TEXT,
  puan TINYINT(1),
  avatar_url VARCHAR(500),
  tarih DATE,
  aktif TINYINT(1) DEFAULT 0,
  INDEX (tenant_id, aktif, puan)
);
```
