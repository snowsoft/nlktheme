# SEO Yöneticisi ve Rich Snippet

`SeoManager`, `<head>` içindeki tüm meta etiketlerini, Open Graph, Twitter Card verilerini ve JSON-LD yapısal verilerini tek noktadan yönetir.

---

## Temel Kullanım

### Controller İçinde

```php
use Nlk\Theme\Facades\Seo;

// --- Ürün Detay Sayfası ---
Seo::title($product->baslik, config('app.name'))
   ->description($product->aciklama)
   ->keywords('telefon, iphone, apple')
   ->robots('index,follow')
   ->canonical(url()->current())
   ->ogType('product')
   ->ogImage($product->foto_url, 1200, 630)
   ->ogProduct($product->fiyat, 'TRY', 'InStock')
   ->twitterCard('summary_large_image')
   ->twitterSite('@magazaadi');
```

### Layout İçinde

```blade
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @seo_head
</head>
```

---

## API Referansı

### Meta Etiketleri

```php
Seo::title('Sayfa Başlığı', 'Site Adı');
// → <title>Sayfa Başlığı | Site Adı</title>

Seo::description('160 karaktere kadar açıklama...');
// → <meta name="description" ...>

Seo::keywords('anahtar,kelime,listesi');
// → <meta name="keywords" ...>

Seo::robots('index,follow');
// Veya kısaca:
Seo::noindex(); // → 'noindex,nofollow'
```

### Open Graph

```php
Seo::og('type', 'website');
Seo::ogType('product');
Seo::ogImage('/img/product.jpg', 1200, 630);
Seo::ogProduct(299.99, 'TRY', 'InStock');
// → <meta property="product:price:amount" ...>
```

### Twitter Card

```php
Seo::twitterCard('summary_large_image');
Seo::twitterSite('@handle');
Seo::twitter('creator', '@author');
```

### Bağlantı Etiketleri

```php
// Canonical URL
Seo::canonical(url()->current());

// Çok dil (hreflang)
Seo::hreflang('tr', 'https://example.com/tr/urun/123')
   ->hreflang('en', 'https://example.com/en/product/123')
   ->hreflang('x-default', 'https://example.com/urun/123');

// Performans (Core Web Vitals)
Seo::preconnect('https://fonts.googleapis.com')
   ->preconnect('https://cdn.example.com', crossorigin: true)
   ->dnsPrefetch('//analytics.example.com')
   ->preload('/fonts/inter.woff2', 'font', 'font/woff2', crossorigin: true);
```

---

## Rich Snippet / JSON-LD Yapısal Veri

### Ürün Şeması (`Product`)

```php
Seo::addProductSchema([
    'name'        => $product->baslik,
    'description' => $product->aciklama,
    'image'       => [$product->foto_url, $product->foto2_url],
    'sku'         => $product->barkod,
    'mpn'         => $product->model,
    'brand'       => $product->marka,
    'category'    => $product->kategori_adi,
    'offers' => [
        'price'           => $product->fiyat,
        'currency'        => 'TRY',
        'availability'    => 'InStock',  // veya 'OutOfStock'
        'url'             => url()->current(),
        'priceValidUntil' => '2026-12-31',
        'seller'          => config('app.name'),
    ],
    'aggregateRating' => [
        'ratingValue' => $product->puan_ortalama,
        'reviewCount' => $product->yorum_sayisi,
        'bestRating'  => 5,
        'worstRating' => 1,
    ],
    'review' => $product->yorumlar->map(fn($y) => [
        'author'        => $y->kullanici_adi,
        'datePublished' => $y->tarih,
        'reviewBody'    => $y->yorum,
        'reviewRating'  => ['ratingValue' => $y->puan, 'bestRating' => 5],
    ])->all(),
]);
```

### Breadcrumb Şeması (`BreadcrumbList`)

```php
Seo::addBreadcrumb([
    ['name' => 'Ana Sayfa',          'url' => '/'],
    ['name' => 'Elektronik',         'url' => '/kategori/elektronik'],
    ['name' => 'Telefonlar',         'url' => '/kategori/telefonlar'],
    ['name' => $product->baslik,     'url' => url()->current()],
]);
```

### WebSite + Google Sitelinks Arama

```php
// Anasayfa controller:
Seo::addWebSiteSchema(
    name:      config('app.name'),
    url:       url('/'),
    searchUrl: url('/') . 'arama?q={search_term_string}'
);
```

Google, `SearchAction` şemasını okuyarak arama kutusunu doğrudan Google sonuçlarında gösterebilir.

### SSS Şeması (`FAQPage`)

```php
Seo::addFaqSchema([
    ['question' => 'Kargo ücreti nedir?',          'answer' => '150 TL üzeri kargo bedava.'],
    ['question' => 'Kaç günde teslim alırım?',     'answer' => 'Şehir içi 1, şehirlerarası 3 iş günü.'],
    ['question' => 'İade süresi kaç gündür?',      'answer' => '14 gün içinde iade yapabilirsiniz.'],
]);
```

### Ürün Listesi Şeması (`ItemList`)

```php
// Kategori sayfası:
Seo::addItemListSchema(
    items: $products->map(fn($p) => [
        'name'  => $p->baslik,
        'url'   => route('urundetay', $p->slug),
        'image' => $p->foto_url,
    ])->all(),
    listName: 'Öne Çıkan Ürünler'
);
```

### Özel JSON-LD

```php
// Herhangi bir schema.org şeması ekle
Seo::addJsonLd([
    '@context' => 'https://schema.org',
    '@type'    => 'LocalBusiness',
    'name'     => 'Mağaza Adı',
    'address'  => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'Atatürk Cad. No:1',
        'addressLocality' => 'İstanbul',
        'addressCountry'  => 'TR',
    ],
    'telephone' => '+90 212 000 00 00',
    'openingHours' => ['Mo-Fr 09:00-18:00'],
]);
```

---

## Sayfa Tipi Örnekleri

### Anasayfa

```php
Seo::title(config('app.name'))
   ->description('En iyi ürünler en uygun fiyatlarla.')
   ->ogType('website')
   ->ogImage(url('/img/og-home.jpg'), 1200, 630)
   ->canonical(url('/'))
   ->addWebSiteSchema(config('app.name'), url('/'), url('/') . 'ara?q={search_term_string}');
```

### Kategori Sayfası

```php
Seo::title($category->baslik)
   ->description($category->meta_aciklama ?? "En iyi {$category->baslik} ürünleri")
   ->canonical(route('kategorilist', $category->slug))
   ->addBreadcrumb([...])
   ->addItemListSchema($products->take(10)->map(...)->all(), $category->baslik);
```

### Ürün Detay Sayfası

```php
Seo::title($product->baslik, config('app.name'))
   ->description($product->meta_aciklama ?? $product->kisa_aciklama)
   ->ogType('product')
   ->ogImage($product->foto_url, 1200, 630)
   ->ogProduct($product->fiyat, 'TRY')
   ->canonical(route('urundetay', $product->slug))
   ->addProductSchema([...])
   ->addBreadcrumb([...]);
```

---

## `SchemaGenerator` Doğrudan Kullanım

```php
use Nlk\Theme\SEO\SchemaGenerator;

// JSON-LD script tag
$html = SchemaGenerator::jsonLd(
    SchemaGenerator::organization([
        'name'  => 'Şirket A.Ş.',
        'url'   => 'https://example.com',
        'logo'  => 'https://example.com/logo.png',
        'phone' => '+90 212 000 00 00',
        'email' => 'info@example.com',
        'social'=> ['https://twitter.com/example', 'https://instagram.com/example'],
    ])
);
```
