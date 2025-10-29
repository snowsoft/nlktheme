# SEO & Rich Snippets Dokümantasyonu

Bu dokümantasyon, NLK Theme paketinin SEO (Search Engine Optimization) ve Rich Snippets (Schema.org) özelliklerini açıklar.

## İçindekiler

1. [Schema.org Nedir?](#schemaorg-nedir)
2. [Kurulum ve Kullanım](#kurulum-ve-kullanım)
3. [Schema Türleri](#schema-türleri)
   - [Product Schema](#product-schema)
   - [Organization Schema](#organization-schema)
   - [BreadcrumbList Schema](#breadcrumblist-schema)
   - [Article Schema](#article-schema)
   - [Video Schema](#video-schema)
4. [JSON-LD Formatı](#json-ld-formatı)
5. [Blade'de Kullanım](#bladede-kullanım)
6. [Örnekler](#örnekler)
7. [İleri Seviye Kullanım](#ileri-seviye-kullanım)

---

## Schema.org Nedir?

Schema.org, arama motorlarının web içeriğini daha iyi anlaması için standart bir yapı sağlayan bir işaretleme formatıdır. Rich Snippets, arama sonuçlarında yıldız puanları, fiyatlar, resimler gibi ek bilgilerin görüntülenmesini sağlar.

---

## Kurulum ve Kullanım

SEO özellikleri paketle birlikte gelir, ek bir kurulum gerekmez.

```php
use Nlk\Theme\SEO\SchemaGenerator;
use Nlk\Theme\SEO\ProductSchema;
```

---

## Schema Türleri

### Product Schema

E-ticaret sitelerinde ürün bilgilerini arama motorlarına iletmek için kullanılır.

#### Basit Kullanım

```php
use Nlk\Theme\SEO\ProductSchema;
use Nlk\Theme\SEO\SchemaGenerator;

$productData = [
    'name' => 'Laptop Bilgisayar',
    'description' => 'Yüksek performanslı laptop bilgisayar',
    'image' => 'https://example.com/laptop.jpg',
    'sku' => 'LT-001',
    'brand' => 'TechBrand',
    'category' => 'Elektronik',
    'offers' => [
        'price' => 15000,
        'currency' => 'TRY',
        'availability' => 'InStock',
        'url' => 'https://example.com/products/laptop'
    ]
];

$schema = ProductSchema::generate($productData);
$jsonLd = SchemaGenerator::jsonLd($schema);

// Output: <script type="application/ld+json">...</script>
```

#### Gelişmiş Kullanım (Çoklu Fiyat, Rating, Review)

```php
$productData = [
    'name' => 'Akıllı Telefon',
    'description' => 'En yeni akıllı telefon modeli',
    'image' => [
        'https://example.com/phone-1.jpg',
        'https://example.com/phone-2.jpg'
    ],
    'sku' => 'PH-001',
    'mpn' => 'MANUFACTURER-123',
    'brand' => [
        '@type' => 'Brand',
        'name' => 'PhoneBrand'
    ],
    'category' => 'Elektronik > Telefon',
    'offers' => [
        [
            'price' => 8000,
            'currency' => 'TRY',
            'availability' => 'InStock',
            'url' => 'https://example.com/products/phone-64gb',
            'priceValidUntil' => '2024-12-31'
        ],
        [
            'price' => 9500,
            'currency' => 'TRY',
            'availability' => 'InStock',
            'url' => 'https://example.com/products/phone-128gb'
        ]
    ],
    'aggregateRating' => [
        'ratingValue' => 4.5,
        'reviewCount' => 120,
        'bestRating' => 5,
        'worstRating' => 1
    ],
    'review' => [
        [
            'author' => 'Ahmet Yılmaz',
            'datePublished' => '2024-01-15',
            'reviewBody' => 'Harika bir telefon, çok memnun kaldım.',
            'reviewRating' => [
                'ratingValue' => 5,
                'bestRating' => 5
            ]
        ],
        [
            'author' => [
                '@type' => 'Person',
                'name' => 'Ayşe Demir'
            ],
            'datePublished' => '2024-01-20',
            'reviewBody' => 'Fiyat performans açısından iyi.',
            'reviewRating' => [
                'ratingValue' => 4,
                'bestRating' => 5
            ]
        ]
    ]
];

$schema = ProductSchema::generate($productData);
$jsonLd = SchemaGenerator::jsonLd($schema);
```

#### Offer (Teklif) Parametreleri

- `price` (gerekli): Ürün fiyatı
- `currency` veya `priceCurrency`: Para birimi (TRY, USD, EUR vb.)
- `availability`: Stok durumu
  - `InStock`: Stokta var
  - `OutOfStock`: Stokta yok
  - `PreOrder`: Ön sipariş
  - `InStoreOnly`: Sadece mağazada
  - `OnlineOnly`: Sadece online
- `url`: Ürün sayfası URL'si
- `priceValidUntil`: Fiyat geçerlilik tarihi (opsiyonel)
- `seller`: Satıcı bilgisi (opsiyonel)

---

### Organization Schema

Şirket veya organizasyon bilgilerini tanımlamak için kullanılır.

```php
use Nlk\Theme\SEO\SchemaGenerator;

$orgData = [
    'name' => 'Örnek Şirket A.Ş.',
    'url' => 'https://example.com',
    'logo' => 'https://example.com/logo.png',
    'phone' => '+90 555 123 4567',
    'email' => 'info@example.com',
    'contactType' => 'customer support',
    'social' => [
        'https://facebook.com/example',
        'https://twitter.com/example',
        'https://linkedin.com/company/example'
    ]
];

$schema = SchemaGenerator::organization($orgData);
$jsonLd = SchemaGenerator::jsonLd($schema);
```

#### Organization Parametreleri

- `name` (gerekli): Organizasyon adı
- `url`: Web sitesi URL'si
- `logo`: Logo URL'si
- `phone`: Telefon numarası
- `email`: E-posta adresi
- `contactType`: İletişim türü (varsayılan: 'customer support')
- `social`: Sosyal medya profil URL'leri dizisi

---

### BreadcrumbList Schema

Sayfa navigasyonunu arama motorlarına iletmek için kullanılır.

```php
use Nlk\Theme\SEO\SchemaGenerator;

$breadcrumbs = [
    ['name' => 'Ana Sayfa', 'url' => 'https://example.com'],
    ['name' => 'Kategori', 'url' => 'https://example.com/kategori'],
    ['name' => 'Alt Kategori', 'url' => 'https://example.com/kategori/alt'],
    ['name' => 'Ürün', 'url' => 'https://example.com/kategori/alt/urun']
];

$schema = SchemaGenerator::breadcrumbList($breadcrumbs);
$jsonLd = SchemaGenerator::jsonLd($schema);
```

#### BreadcrumbList ile Breadcrumb Entegrasyonu

```php
// Breadcrumb helper ile kullanım
theme()->breadcrumb()->add('Ana Sayfa', '/');
theme()->breadcrumb()->add('Kategori', '/kategori');
theme()->breadcrumb()->add('Ürün', '/kategori/urun');

$items = theme()->breadcrumb()->get();
$breadcrumbArray = [];

foreach ($items as $item) {
    $breadcrumbArray[] = [
        'name' => $item['label'],
        'url' => url($item['url'])
    ];
}

$schema = SchemaGenerator::breadcrumbList($breadcrumbArray);
$jsonLd = SchemaGenerator::jsonLd($schema);
```

---

### Article Schema

Blog yazıları, haberler ve makaleler için kullanılır.

```php
use Nlk\Theme\SEO\SchemaGenerator;

$articleData = [
    'title' => 'Yazı Başlığı',
    'description' => 'Yazı açıklaması',
    'image' => [
        'https://example.com/article-image.jpg'
    ],
    'published' => '2024-01-15',
    'modified' => '2024-01-20',
    'author' => 'Yazar Adı',
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Site Adı',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => 'https://example.com/logo.png'
        ]
    ]
];

$schema = SchemaGenerator::article($articleData);
$jsonLd = SchemaGenerator::jsonLd($schema);
```

#### Article Parametreleri

- `title`: Yazı başlığı
- `description`: Yazı açıklaması
- `image`: Görsel URL'leri (dizi veya tek değer)
- `published`: Yayın tarihi (ISO 8601 formatı)
- `modified`: Son güncelleme tarihi (opsiyonel)
- `author`: Yazar adı
- `publisher`: Yayıncı bilgisi (opsiyonel)

---

### Video Schema

Video içeriğini tanımlamak için kullanılır.

```php
use Nlk\Theme\SEO\SchemaGenerator;

$videoData = [
    'title' => 'Video Başlığı',
    'description' => 'Video açıklaması',
    'thumbnail' => 'https://example.com/video-thumb.jpg',
    'uploadDate' => '2024-01-15',
    'duration' => 'PT10M30S', // ISO 8601 formatı (10 dakika 30 saniye)
    'url' => 'https://example.com/video.mp4',
    'embedUrl' => 'https://youtube.com/embed/xxxxx'
];

$schema = SchemaGenerator::video($videoData);
$jsonLd = SchemaGenerator::jsonLd($schema);
```

#### Video Parametreleri

- `title`: Video başlığı
- `description`: Video açıklaması
- `thumbnail`: Thumbnail görsel URL'si
- `uploadDate`: Yüklenme tarihi (ISO 8601)
- `duration`: Video süresi (ISO 8601 formatı: PT10M30S)
- `url`: Video dosyası URL'si
- `embedUrl`: Embed URL'si (YouTube, Vimeo vb.)

---

## JSON-LD Formatı

`SchemaGenerator::jsonLd()` metodu, schema dizisini JSON-LD formatına çevirir ve `<script type="application/ld+json">` tag'leri içine alır.

```php
$schema = [
    '@context' => 'https://schema.org/',
    '@type' => 'Product',
    'name' => 'Ürün Adı'
];

$output = SchemaGenerator::jsonLd($schema);
// Output:
// <script type="application/ld+json">
// {
//     "@context": "https://schema.org/",
//     "@type": "Product",
//     "name": "Ürün Adı"
// }
// </script>
```

---

## Blade'de Kullanım

### Helper Fonksiyonları ile Kullanım (Önerilen)

Paket, SEO schema'ları için kolay kullanım sağlayan helper fonksiyonları içerir:

```php
// Product Schema
{!! theme_product_schema([
    'name' => 'Ürün Adı',
    'price' => 1000,
    'currency' => 'TRY'
]) !!}

// Organization Schema
{!! theme_organization_schema([
    'name' => 'Şirket Adı',
    'url' => 'https://example.com'
]) !!}

// Breadcrumb Schema
{!! theme_breadcrumb_schema([
    ['name' => 'Ana Sayfa', 'url' => '/'],
    ['name' => 'Kategori', 'url' => '/category']
]) !!}

// Article Schema
{!! theme_article_schema([
    'title' => 'Yazı Başlığı',
    'description' => 'Yazı açıklaması'
]) !!}

// Video Schema
{!! theme_video_schema([
    'title' => 'Video Başlığı',
    'url' => 'https://example.com/video.mp4'
]) !!}

// Generic Schema (kendi schema'ınızı oluşturun)
{!! theme_schema([
    '@context' => 'https://schema.org/',
    '@type' => 'CustomType',
    'property' => 'value'
]) !!}
```

### Layout Dosyasında

`layouts/main.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'Site Adı' }}</title>
    
    {{-- Organization Schema (Helper ile) --}}
    {!! theme_organization_schema([
        'name' => config('app.name'),
        'url' => config('app.url'),
        'logo' => asset('images/logo.png')
    ]) !!}
    
    {{-- Page Specific Schema --}}
    @if(isset($pageSchema))
        {!! $pageSchema !!}
    @endif
</head>
<body>
    @yield('content')
</body>
</html>
```

### Controller'da Schema Oluşturma

```php
use Nlk\Theme\SEO\ProductSchema;
use Nlk\Theme\SEO\SchemaGenerator;

public function product($id)
{
    $product = Product::find($id);
    
    // Product Schema
    $productData = [
        'name' => $product->name,
        'description' => $product->description,
        'image' => $product->images->pluck('url')->toArray(),
        'sku' => $product->sku,
        'brand' => $product->brand->name,
        'offers' => [
            'price' => $product->price,
            'currency' => 'TRY',
            'availability' => $product->stock > 0 ? 'InStock' : 'OutOfStock',
            'url' => route('product.show', $product->id)
        ]
    ];
    
    $schema = ProductSchema::generate($productData);
    $jsonLd = SchemaGenerator::jsonLd($schema);
    
    return theme()->view('product.show', [
        'product' => $product,
        'pageSchema' => $jsonLd
    ]);
}
```

### Helper Fonksiyon ile Kullanım (Önerilen)

Blade template'inde helper fonksiyonları kullanarak:

```blade
{{-- Product Schema (Helper ile) --}}
{!! theme_product_schema([
    'name' => $product->name,
    'description' => $product->description,
    'image' => $product->image,
    'offers' => [
        'price' => $product->price,
        'currency' => 'TRY',
        'availability' => 'InStock'
    ]
]) !!}
```

Veya class'ları doğrudan kullanarak:

```blade
@php
    use Nlk\Theme\SEO\ProductSchema;
    use Nlk\Theme\SEO\SchemaGenerator;
    
    $productSchema = SchemaGenerator::jsonLd(
        ProductSchema::generate([
            'name' => $product->name,
            'description' => $product->description,
            'image' => $product->image,
            'offers' => [
                'price' => $product->price,
                'currency' => 'TRY',
                'availability' => 'InStock'
            ]
        ])
    );
@endphp

{!! $productSchema !!}
```

---

## Örnekler

### E-Ticaret Ürün Sayfası

```php
// ProductController.php
public function show($id)
{
    $product = Product::with('reviews', 'brand')->find($id);
    
    // Aggregate Rating hesapla
    $aggregateRating = null;
    if ($product->reviews->count() > 0) {
        $aggregateRating = [
            'ratingValue' => $product->reviews->avg('rating'),
            'reviewCount' => $product->reviews->count(),
            'bestRating' => 5,
            'worstRating' => 1
        ];
    }
    
    // Review array oluştur
    $reviews = $product->reviews->map(function($review) {
        return [
            'author' => $review->user->name,
            'datePublished' => $review->created_at->format('Y-m-d'),
            'reviewBody' => $review->comment,
            'reviewRating' => [
                'ratingValue' => $review->rating,
                'bestRating' => 5
            ]
        ];
    })->toArray();
    
    // Product Schema
    $productData = [
        'name' => $product->name,
        'description' => strip_tags($product->description),
        'image' => $product->images->pluck('url')->toArray(),
        'sku' => $product->sku,
        'brand' => $product->brand->name,
        'category' => $product->category->name,
        'offers' => [
            'price' => $product->sale_price ?? $product->price,
            'currency' => 'TRY',
            'availability' => $product->stock > 0 ? 'InStock' : 'OutOfStock',
            'url' => route('product.show', $product->id)
        ]
    ];
    
    if ($aggregateRating) {
        $productData['aggregateRating'] = $aggregateRating;
    }
    
    if (!empty($reviews)) {
        $productData['review'] = $reviews;
    }
    
    $schema = ProductSchema::generate($productData);
    $jsonLd = SchemaGenerator::jsonLd($schema);
    
    return theme()->view('products.show', [
        'product' => $product,
        'pageSchema' => $jsonLd
    ]);
}
```

### Blog Yazı Sayfası

```php
// BlogController.php
public function show($slug)
{
    $article = Article::where('slug', $slug)->first();
    
    // Article Schema
    $articleData = [
        'title' => $article->title,
        'description' => $article->excerpt,
        'image' => [$article->featured_image],
        'published' => $article->published_at->format('Y-m-d'),
        'modified' => $article->updated_at->format('Y-m-d'),
        'author' => $article->author->name,
        'publisher' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('images/logo.png')
            ]
        ]
    ];
    
    // Organization Schema (sayfa başında)
    $orgData = [
        'name' => config('app.name'),
        'url' => config('app.url'),
        'logo' => asset('images/logo.png'),
        'social' => [
            config('social.facebook'),
            config('social.twitter')
        ]
    ];
    
    $orgSchema = SchemaGenerator::jsonLd(
        SchemaGenerator::organization($orgData)
    );
    
    $articleSchema = SchemaGenerator::jsonLd(
        SchemaGenerator::article($articleData)
    );
    
    return theme()->view('blog.show', [
        'article' => $article,
        'organizationSchema' => $orgSchema,
        'pageSchema' => $articleSchema
    ]);
}
```

### Çoklu Schema Kullanımı

```php
// Aynı sayfada birden fazla schema kullanımı
$schemas = [];

// Organization
$schemas[] = SchemaGenerator::organization([
    'name' => config('app.name'),
    'url' => config('app.url')
]);

// Breadcrumb
$breadcrumbs = [
    ['name' => 'Ana Sayfa', 'url' => url('/')],
    ['name' => 'Sprite', 'url' => url('/products')],
    ['name' => 'Ürün', 'url' => url()->current()]
];
$schemas[] = SchemaGenerator::breadcrumbList($breadcrumbs);

// Product
$schemas[] = ProductSchema::generate($productData);

// Tüm schema'ları birleştir
$allSchemas = array_map(function($schema) {
    return SchemaGenerator::jsonLd($schema);
}, $schemas);

$combinedSchemas = implode("\n", $allSchemas);

return theme()->view('product.show', [
    'product' => $product,
    'pageSchema' => $combinedSchemas
]);
```

---

## İleri Seviye Kullanım

### Custom Schema Oluşturma

SchemaGenerator sınıfını genişleterek özel schema türleri oluşturabilirsiniz:

```php
namespace App\SEO;

use Nlk\Theme\SEO\SchemaGenerator;

class CustomSchemaGenerator extends SchemaGenerator
{
    /**
     * Generate LocalBusiness schema.
     */
    public static function localBusiness(array $data)
    {
        return [
            '@context' => 'https://schema.org/',
            '@type' => 'LocalBusiness',
            'name' => $data['name'] ?? '',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $data['address']['street'] ?? '',
                'addressLocality' => $data['address']['city'] ?? '',
                'addressRegion' => $data['address']['region'] ?? '',
                'postalCode' => $data['address']['postalCode'] ?? '',
                'addressCountry' => $data['address']['country'] ?? ''
            ],
            'telephone' => $data['phone'] ?? '',
            'priceRange' => $data['priceRange'] ?? ''
        ];
    }
}
```

### Service Provider ile Global Schema

```php
// AppServiceProvider.php veya ThemeServiceProvider.php
public function boot()
{
    // Her sayfaya Organization schema ekle
    view()->composer('*', function($view) {
        $orgSchema = SchemaGenerator::jsonLd(
            SchemaGenerator::organization([
                'name' => config('app.name'),
                'url' => config('app.url'),
                'logo' => asset('images/logo.png')
            ])
        );
        
        $view->with('organizationSchema', $orgSchema);
    });
}
```

### Cache ile Performans

```php
// Schema cache'leme
$cacheKey = "schema_product_{$productId}";
$jsonLd = Cache::remember($cacheKey, 3600, function() use ($product) {
    $schema = ProductSchema::generate([
        // ...
    ]);
    return SchemaGenerator::jsonLd($schema);
});
```

---

## Test Etme

Google'ın Rich Results Test aracını kullanarak schema'larınızı test edebilirsiniz:

- **Rich Results Test**: https://search.google.com/test/rich-results
- **Schema Markup Validator**: https://validator.schema.org/

---

## Best Practices

1. **Gerekli Alanlar**: Schema türüne göre gerekli alanları mutlaka doldurun
2. **Doğru Formatlar**: Tarihler için ISO 8601, para birimi için standart kodlar (TRY, USD) kullanın
3. **Görsel URL'leri**: Mutlak URL'ler kullanın (https://example.com/image.jpg)
4. **Çoklu Schema**: Aynı sayfada farklı schema türlerini birlikte kullanabilirsiniz
5. **Güncel Veriler**: Stok, fiyat gibi değişken verileri güncel tutun
6. **Validation**: Schema'ları Google'ın test araçlarıyla doğrulayın

---

## Yardım ve Destek

Sorunlarınız için:
- [Ana Dokümantasyon](README.md)
- [Sorun Giderme](docs/TROUBLESHOOTING.md)
- GitHub Issues

---

## Referanslar

- [Schema.org Dokümantasyonu](https://schema.org/)
- [Google Rich Results](https://developers.google.com/search/docs/appearance/structured-data)
- [JSON-LD Playground](https://json-ld.org/playground/)

