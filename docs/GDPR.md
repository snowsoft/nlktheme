# GDPR & Cookie Consent (Faz 3)

`CookieConsentManager` — GDPR uyumlu çerez izni yönetimi.

---

## Genel Bakış

| Özellik | Detay |
|---|---|
| Cookie kategorileri | necessary, analytics, marketing, preferences |
| GTM Consent Mode | v2 (ad_user_data, ad_personalization dahil) |
| Storage | Cookie (`nlk_cookie_consent`, base64 JSON) |
| Süre | 365 gün |
| Blade | `@cookie_banner` |

---

## Blade Direktifi

```blade
{{-- layout.blade.php → </body> öncesi --}}
@cookie_banner
```

Bu direktif:
1. Kullanıcı henüz seçim yapmadıysa → Banner HTML + Modal HTML + `NlkConsent` JS nesnesi döndürür
2. Kullanıcı zaten seçim yaptıysa → boş string döndürür

---

## PHP API

```php
$consent = app('nlk.consent');

// Kategori sorgula
$consent->isGranted('analytics');     // → bool
$consent->analyticsGranted();          // → bool
$consent->marketingGranted();          // → bool
$consent->preferencesGranted();        // → bool
$consent->allGranted();                // → bool (analytics + marketing)
$consent->hasConsented();              // → bool (herhangi bir seçim yapıldı mı)

// İzin güncelle (server-side)
$consent->grant(['analytics', 'marketing']);
$consent->grantAll();
$consent->rejectAll();

// GTM Consent Mode v2 JSON string
$consent->gtmDefaultConsent();     // → '{"analytics_storage":"denied",...}'
$consent->gtmUpdateConsent();      // → '{"analytics_storage":"granted",...}'

// Banner HTML
$consent->renderBanner();
```

---

## GTM Consent Mode v2 Entegrasyonu

GTM Consent Mode için `<head>` içinde default denied olarak başlatın:

```blade
{{-- layout.blade.php <head> → GTM bloğundan ÖNCE --}}
@if(config('theme.tracking.gtm_id'))
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}

// Consent Mode v2 — default: denied (GDPR uyumlu)
gtag('consent', 'default', {!! app('nlk.consent')->gtmDefaultConsent() !!});

// Kullanıcı daha önce izin verdiyse update
@if(app('nlk.consent')->hasConsented())
gtag('consent', 'update', {!! app('nlk.consent')->gtmUpdateConsent() !!});
@endif
</script>
@endif

@tracking_head
```

### Blade `@gtm_consent_update`

```blade
{{-- head içinde, izin verdikten sonra update tetikler --}}
@if(app('nlk.consent')->hasConsented())
<script>
if(typeof gtag === 'function'){
  gtag('consent', 'update', {!! app('nlk.consent')->gtmUpdateConsent() !!});
}
</script>
@endif
```

---

## Cookie Consent API Route

```php
// routes/web.php
Route::post('/theme/consent', function (\Illuminate\Http\Request $request) {
    $categories = $request->input('categories', []);
    $manager    = app('nlk.consent');
    $manager->grant($categories);
    return response()->json(['ok' => true]);
})->name('theme.consent');
```

---

## JavaScript API (`NlkConsent`)

Banner HTML'i içine yerleşik gelir:

```js
// Tümünü kabul et
NlkConsent.acceptAll();

// Tümünü reddet (sadece zorunlu)
NlkConsent.rejectAll();

// Tercihler modalini aç
NlkConsent.settings();

// Seçili kategorileri kaydet
NlkConsent.saveSettings();
```

---

## Koşullu İzin Kontrolü

```blade
{{-- Sadece analytics izni varsa Hotjar yükle --}}
@if(app('nlk.consent')->analyticsGranted())
<script>
  (function(h,o,t,j,a,r){ /* Hotjar script */ })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
@endif

{{-- Sadece marketing izni varsa Facebook Pixel yükle --}}
@if(app('nlk.consent')->marketingGranted())
<script>
!function(f,b,e,v,n,t,s){ /* FB Pixel */ }
fbq('init', '{{ config("theme.tracking.fb_pixel_id") }}');
fbq('track', 'PageView');
</script>
@endif
```

---

## Config

```env
# Privacy URL'leri
THEME_GDPR_PRIVACY_URL=/gizlilik-politikasi
THEME_GDPR_COOKIE_URL=/cerez-politikasi
```

```php
// config/theme.php içine ekleyin
'gdpr' => [
    'privacy_url' => env('THEME_GDPR_PRIVACY_URL', '/gizlilik-politikasi'),
    'cookie_url'  => env('THEME_GDPR_COOKIE_URL',  '/cerez-politikasi'),
],
```

---

## Consent Kategorileri

| Kategori | Her Zaman Aktif | İçerik |
|---|---|---|
| `necessary` | ✅ | Session, CSRF, alışveriş sepeti |
| `analytics` | ❌ | GA4, Hotjar, Microsoft Clarity |
| `marketing` | ❌ | GTM Ads, FB Pixel, Google Ads |
| `preferences` | ❌ | Dil, tema, para birimi tercihleri |

---

## GDPR Uyumluluk Kontrol Listesi

- ✅ Kullanıcı aktif seçim yapmadan izin verilmez
- ✅ Kolay reddetme butonu (GDPR Art. 7)
- ✅ Granüler kategori seçimi
- ✅ Cookie süresi 365 gün (yenilenebilir)
- ✅ GTM Consent Mode v2 (veri toplama engellenir)
- ✅ Tercih sayfası linki (gizlilik + çerez politikası)
- ⚠️ Privacy policy ve Cookie policy sayfaları manuel oluşturulmalıdır
