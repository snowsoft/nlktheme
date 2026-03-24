# CDN Image Engine

`CdnImageManager`, `cdn.nlkmenu.com` API'sini kullanarak on-the-fly görüntü transform, LQIP lazy loading ve AI özellikleri sunar.

---

## .env Yapılandırması

```env
CDN_URL=https://cdn.nlkmenu.com
CDN_API_KEY=your_api_key
CDN_TENANT=default
CDN_DEFAULT_FORMAT=auto       # auto = Accept header → AVIF > WebP > JPEG
CDN_DEFAULT_QUALITY=85
CDN_SMART_COMPRESS=true       # WebP/AVIF akıllı sıkıştırma
CDN_SIGNED_URLS=false         # true = private resimler için
CDN_CACHE_TTL=300
```

---

## Kullanım

### Fluent Builder (önerilen)

```php
use Nlk\Theme\Facades\CdnImage;

// Temel URL
$url = CdnImage::build('img-id')
    ->width(800)->height(600)
    ->format('webp')->fit('cover')
    ->quality(90)
    ->get();

// Responsive srcset
$srcset = CdnImage::build('img-id')
    ->format('auto')
    ->smartCompress()
    ->srcset([400, 800, 1200]);

// <img> etiketi
$img = CdnImage::build('img-id')
    ->width(800)->fit('cover')->format('auto')
    ->img(['alt' => 'Ürün', 'class' => 'product-img']);

// Lazy-load <img> (LQIP blur-up)
$lazy = CdnImage::build('img-id')
    ->width(800)->fit('cover')
    ->lazyImg(['alt' => 'Ürün']);

// AI arka plan kaldırma
$noBg = CdnImage::build('img-id')->noBg('png');

// AI upscale
$hd = CdnImage::build('img-id')->upscale(2);
```

### Doğrudan Metodlar

```php
// On-the-fly resize
CdnImage::url('img-id', ['w' => 800, 'format' => 'auto', 'fit' => 'cover']);

// Named variant (thumbnail | small | medium | large | webp | avif)
CdnImage::variantUrl('img-id', 'medium');

// LQIP placeholder
CdnImage::placeholderUrl('img-id');

// AI: arka plan kaldır (AR için)
CdnImage::noBgUrl('img-id', 'png');

// AI: süper çözünürlük
CdnImage::upscaleUrl('img-id', 2, 'webp');

// İmzalı URL (private görüntüler)
CdnImage::signedUrl('img-id', 3600);

// Metadata (suggestedSrcset, dominantColor, LQIP data URL)
CdnImage::info('img-id');
CdnImage::dominantColor('img-id'); // → '#f0a030'
CdnImage::lqipDataUrl('img-id');   // → 'data:image/webp;base64,...'

// URL'den CDN'e import
CdnImage::importFromUrl('https://dış-sunucu.com/ürün.jpg');
```

---

## Blade Direktifleri

```blade
{{-- Temel CDN img etiketi --}}
@cdn_img('img-id', ['w' => 800, 'format' => 'auto', 'fit' => 'cover'], ['alt' => 'Ürün'])

{{-- LQIP blur-up lazy load --}}
@cdn_img_lazy('img-id', ['w' => 800, 'fit' => 'cover'], ['alt' => 'Ürün'])

{{-- AI: arka plan kaldırılmış img (no-bg) --}}
@cdn_img_ar('img-id', ['alt' => 'Ürün', 'class' => 'product-ar'])

{{-- Srcset string --}}
<img src="{{ CdnImage::url('img-id', ['w'=>800]) }}"
     srcset="@cdn_srcset('img-id', [400, 800, 1200])"
     alt="Ürün">

{{-- LQIP URL --}}
<img src="@cdn_placeholder('img-id')" data-src="{{ CdnImage::url('img-id') }}" class="cdn-lazy">
```

---

## AR (Artırılmış Gerçeklik) Viewer

### Blade

```blade
{{-- AR görüntüleyici (iOS Quick Look + Android Scene Viewer) --}}
@ar_viewer(
    $product->cdn_image_id,
    $product->model_glb_url,   // .glb (Android)
    $product->model_usdz_url,  // .usdz (iOS)
    ['title' => $product->baslik, 'price' => $product->fiyat . ' TL']
)

{{-- AR JavaScript (layout'ta bir kez, </body> öncesi) --}}
@ar_script
```

### PHP

```php
use Nlk\Theme\Facades\ArImage;

// No-bg PNG URL
$noBgPng  = ArImage::noBgUrl('img-id', 'png');
$noBgWebp = ArImage::noBgWebp('img-id');

// No-bg img etiketi
$img = ArImage::noBgImg('img-id', ['alt' => 'Ürün', 'class' => 'product-clean']);

// Tam AR viewer HTML
$html = ArImage::arTag(
    imageId: 'img-id',
    glbUrl:  'https://example.com/product.glb',
    usdzUrl: 'https://example.com/product.usdz',
    opts:    ['title' => 'iPhone 15 Pro', 'price' => '59.999 TL']
);
```

---

## Lazy Load JS

```blade
{{-- Layout'ta scripts bölümüne ekle --}}
<script src="{{ asset('vendor/nlktheme/js/cdn-lazy.js') }}"></script>

{{-- veya manuel --}}
@push('scripts')
<script>
/* cdn-lazy inline */
(function(){...})();
</script>
@endpush
```

---

## URL Format Referansı

| Format | Örnek URL | Kullanım |
|---|---|---|
| On-the-fly | `/api/image/{id}?w=800&format=auto&fit=cover` | Esneklik |
| Sabit boyut | `/api/image/{id}/800x600/webp` | SEO, sosyal medya |
| Named variant | `/api/image/{id}/medium` | Hızlı listing |
| LQIP | `/api/image/{id}/placeholder` | Lazy loading |
| No-bg | `/api/image/{id}/no-bg?format=png` | Ürün AR, şeffaf arka plan |
| Upscale | `/api/image/{id}/upscale?scale=2` | Küçük görüntü büyütme |

---

## Crop / Fit Seçenekleri

| Parametre | Açıklama |
|---|---|
| `fit=cover` | Hedef boyutu doldur, taşanı kırp |
| `fit=inside` | Sığdır, büyütme yok |
| `fit=contain` | Letterbox (letterbox eklenir) |
| `position=attention` | Akıllı kırpma (parlaklık + ten rengi) |
| `position=entropy` | Shannon entropi ile ilgi odağı |
| `crop=salient` | AI: saliency map ile kırpma |
| `smartCompress=1` | WebP/AVIF akıllı boyut/kalite dengesi |
| `watermark=invisible` | LSB steganografi filigranı |
