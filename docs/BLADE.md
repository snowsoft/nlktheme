# Tüm Blade Direktifleri

Bu belge `snowsoft/nlktheme` tarafından kayıt edilen tüm özel Blade direktiflerini kapsar.

---

## FlexPage Engine

| Direktif | Açıklama |
|---|---|
| `@page_render('home', $tenantId)` | Tüm sayfa section'larını render et |
| `@section_render('hero', $tenantId)` | Tek bir section render et |

---

## SEO

| Direktif | Açıklama |
|---|---|
| `@seo_head` | `<title>`, meta description, OG, Twitter Card, JSON-LD |
| `@tracking_head` | GTM, GA4, Google Ads, FB Pixel `<head>` kodu |
| `@tracking_body` | GTM `<noscript>` iframe (`<body>` hemen sonrası) |
| `@tracking_events` | Kuyruktaki e-ticaret olaylarını aktar (`</body>` öncesi) |

---

## CDN Image Engine

| Direktif | İmza | Açıklama |
|---|---|---|
| `@cdn_img` | `($id, $opts, $attrs)` | Temel CDN `<img>` etiketi |
| `@cdn_img_lazy` | `($id, $opts, $attrs)` | LQIP blur-up lazy load |
| `@cdn_srcset` | `($id, $widths)` | Responsive `srcset` string |
| `@cdn_placeholder` | `($id)` | LQIP URL (32×32 blur WebP) |
| `@cdn_img_ar` | `($id, $attrs)` | AI no-bg `<img>` (arka plan kaldırılmış) |
| `@ar_viewer` | `($id, $glbUrl, $usdzUrl, $opts)` | AR Quick Look / Scene Viewer |
| `@ar_script` | — | AR JavaScript (layout'ta bir kez) |

### Örnekler

```blade
{{-- Temel CDN görsel --}}
@cdn_img('img-abc', ['w' => 800, 'format' => 'auto', 'fit' => 'cover'], ['alt' => 'Ürün'])

{{-- Lazy load (LQIP blur-up) --}}
@cdn_img_lazy('img-abc', ['w' => 800, 'fit' => 'cover'], ['alt' => 'Ürün', 'class' => 'hero-img'])

{{-- Responsive srcset --}}
<img src="{{ CdnImage::url('img-abc', ['w'=>800]) }}"
     srcset="@cdn_srcset('img-abc', [400, 800, 1200])"
     sizes="(max-width:640px) 400px, (max-width:1024px) 800px, 1200px"
     alt="Ürün">

{{-- AI no-bg (AR için) --}}
@cdn_img_ar('img-abc', ['alt' => 'Ürün saydamlı', 'class' => 'product-ar'])

{{-- AR Viewer (iOS Quick Look + Android Scene Viewer) --}}
@ar_viewer('img-abc', 'https://cdn.example.com/product.glb', 'https://cdn.example.com/product.usdz', ['title' => 'Ürün Adı'])

{{-- AR JS (layout'ta bir kez, </body> öncesi) --}}
@ar_script
```

---

## Performans

| Direktif | İmza | Açıklama |
|---|---|---|
| `@critical_css` | `('pageKey')` | Above-the-fold CSS inline enjekte |
| `@pwa_head` | — | PWA manifest + meta + SW kaydı |
| `@pwa_sw_script` | — | Sadece Service Worker kayıt scripti |

### Örnekler

```blade
{{-- layout.blade.php <head> içi --}}
@critical_css('home')
@pwa_head

{{-- veya ayrı ayrı --}}
<link rel="manifest" href="/manifest.json">
@pwa_sw_script
```

---

## GDPR & Cookie Consent

| Direktif | İmza | Açıklama |
|---|---|---|
| `@cookie_banner` | — | Cookie banner + modal HTML (seçim yoksa) |
| `@gtm_consent_update` | — | GTM Consent Mode v2 update JSON |

### Örnekler

```blade
{{-- layout.blade.php </body> öncesi --}}
@tracking_events
@cookie_banner

{{-- GTM Consent Mode v2 entegrasyonu --}}
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('consent', 'default', {'analytics_storage':'denied','ad_storage':'denied'});
@if(app('nlk.consent')->hasConsented())
gtag('consent', 'update', {!! app('nlk.consent')->gtmUpdateConsent() !!});
@endif
</script>
```

---

## i18n & Para Birimi

| Direktif | İmza | Açıklama |
|---|---|---|
| `@html_dir` | — | `'rtl'` veya `'ltr'` |
| `@currency` | `($amount, $currency)` | Tam format (PHP intl) |
| `@currency_short` | `($amount, $currency)` | Kısa format (sembol + sayı) |

### Örnekler

```blade
{{-- HTML lang + dir --}}
<html lang="{{ app()->getLocale() }}" dir="@html_dir">

{{-- Ürün fiyatı --}}
<span class="price">@currency($product->fiyat, 'TRY')</span>

{{-- Kısa format --}}
<span class="price-compact">@currency_short($product->fiyat, 'TRY')</span>

{{-- PHP ile --}}
{{ app('nlk.currency')->compact($product->fiyat, 'TRY') }}  {{-- → "₺1.2K" --}}
```

---

## Legacy Direktifler

| Direktif | Açıklama |
|---|---|
| `@dd($var)` | `dd($var)` |
| `@d($var)` | `dump($var)` |
| `@get('key')` | `Theme::get('key')` |
| `@partial('name')` | `Theme::partial('name')` |
| `@content` | `Theme::content()` |
| `@asset('file.css')` | `Theme::asset()->absUrl(...)` |
| `@styles` | Kayıtlı CSS asset'leri |
| `@scripts` | Kayıtlı JS asset'leri |
| `@widget('name')` | Widget render |

---

## Tüm Direktiflerin Özet Tablosu

| Kategori | Direktifler |
|---|---|
| FlexPage | `@page_render`, `@section_render` |
| SEO | `@seo_head`, `@tracking_head`, `@tracking_body`, `@tracking_events` |
| CDN | `@cdn_img`, `@cdn_img_lazy`, `@cdn_srcset`, `@cdn_placeholder` |
| AR | `@cdn_img_ar`, `@ar_viewer`, `@ar_script` |
| Performans | `@critical_css`, `@pwa_head`, `@pwa_sw_script` |
| GDPR | `@cookie_banner`, `@gtm_consent_update` |
| i18n | `@html_dir`, `@currency`, `@currency_short` |
| Legacy | `@dd`, `@d`, `@get`, `@partial`, `@content`, `@asset`, `@styles`, `@scripts`, `@widget` |
