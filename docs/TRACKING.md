# Tracking (GTM · GA4 · Google Ads · Facebook Pixel)

`TrackingManager` tüm marketing pixel'larını tek noktadan yönetir. Platform başına ayrı entegrasyon gerekmez.

---

## Yapılandırma

```env
THEME_TRACKING_ENABLED=true
THEME_GTM_ID=GTM-XXXXXXX           # Google Tag Manager
THEME_GA4_ID=G-XXXXXXXXXX          # GA4 (GTM olmadan)
THEME_GADS_ID=AW-XXXXXXXXXX        # Google Ads Conversion
THEME_FB_PIXEL_ID=1234567890       # Facebook / Meta Pixel
THEME_CONSENT_MODE=default         # 'default' (GDPR) | 'granted'
```

---

## Layout'a Yerleştirme

```blade
<head>
    {{-- ① Script init: GTM/GA4/Pixel --}}
    @tracking_head
</head>
<body>
    {{-- ② GTM noscript iframe --}}
    @tracking_body

    {{-- ... sayfa içeriği ... --}}

    {{-- ③ E-commerce event'leri (satın alma, sepet vb.) --}}
    @tracking_events
</body>
```

**Sıra önemlidir:** `@tracking_head` → `@tracking_body` → `@tracking_events`

---

## Google Consent Mode v2 (GDPR)

`THEME_CONSENT_MODE=default` seçildiğinde tüm storage'lar başlangıçta `denied` gelir:

```javascript
gtag('consent', 'default', {
  'ad_storage':          'denied',
  'ad_user_data':        'denied',
  'ad_personalization':  'denied',
  'analytics_storage':   'denied',
  'wait_for_update':     500
});
```

Kullanıcı kabul ettiğinde, GTM üzerinden `consent update` trigger'ı ateşlenir (cookie popup entegrasyonu).

`THEME_CONSENT_MODE=granted` ise (GDPR dışı bölgeler) bu blok eklenmez.

---

## E-Commerce Event'leri

### Ürün Görüntüleme (`ViewContent` / `view_item`)

```php
use Nlk\Theme\Facades\Tracking;

// Ürün detay controller:
Tracking::viewContent([
    'id'       => $product->id,
    'name'     => $product->baslik,
    'price'    => $product->fiyat,
    'currency' => 'TRY',
    'category' => $product->kategori_adi,
    'brand'    => $product->marka,
]);
```

**GA4 event:** `view_item`  
**FB Pixel event:** `ViewContent`

---

### Sepete Ekle (`AddToCart` / `add_to_cart`)

```php
Tracking::addToCart(
    item: [
        'id'    => $product->id,
        'name'  => $product->baslik,
        'price' => $product->fiyat,
    ],
    value:    $product->fiyat,
    currency: 'TRY'
);
```

**GA4:** `add_to_cart`  
**FB Pixel:** `AddToCart`

---

### Ödeme Başlat (`InitiateCheckout` / `begin_checkout`)

```php
Tracking::initiateCheckout(
    value:    $cart->toplam,
    currency: 'TRY',
    numItems: $cart->items->sum('adet'),
    contents: $cart->items->map(fn($i) => [
        'id'       => $i->urun_id,
        'quantity' => $i->adet,
    ])->all()
);
```

**GA4:** `begin_checkout`  
**FB Pixel:** `InitiateCheckout`

---

### Satın Alma (`Purchase` / `purchase`)

```php
// Sipariş tamamlama sayfası:
Tracking::purchase([
    'id'       => $order->siparis_no,
    'value'    => $order->toplam_tutar,
    'currency' => 'TRY',
    'tax'      => $order->kdv_tutari,
    'shipping' => $order->kargo_ucreti,
    'items'    => $order->urunler->map(fn($i) => [
        'item_id'   => $i->urun_id,
        'item_name' => $i->baslik,
        'price'     => $i->birim_fiyat,
        'quantity'  => $i->adet,
    ])->all(),
]);
```

**GA4:** `purchase` (transaction_id, revenue, tax, shipping, items)  
**FB Pixel:** `Purchase` (value, currency, content_ids, order_id)

---

### Arama (`Search` / `search`)

```php
Tracking::search($request->input('q'));
```

**GA4:** `search` (search_term)  
**FB Pixel:** `Search` (search_string)

---

## GTM DataLayer Özel Push

```php
// Kullanıcı bilgisi (oturum açıksa)
Tracking::push('user_id',    auth()->id())
         ->push('tenant_id', $tenantId)
         ->push('page_type', 'product');
```

Oluşturulan script:

```html
<script>
window.dataLayer = window.dataLayer || [];
dataLayer.push({"user_id":123,"tenant_id":"t1","page_type":"product"});
</script>
```

---

## Platforma Göre Üretilen Çıktı

### `@tracking_head` (GTM ile)

```html
<!-- Google Consent Mode v2 -->
<script>window.dataLayer=...;gtag('consent','default',{...})</script>
<script>dataLayer.push({...custom pushes...})</script>

<!-- GTM -->
<script>(function(w,d,s,l,i){...})(window,document,'script','dataLayer','GTM-XXXXX');</script>

<!-- Meta Pixel -->
<script>!function(f,b,e,v,...){...}fbq('init','PIXEL_ID');fbq('track','PageView');</script>
```

### `@tracking_body`

```html
<noscript>
  <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXXX"
    height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<noscript>
  <img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=PIXEL_ID&ev=PageView&noscript=1"/>
</noscript>
```

### `@tracking_events` (purchase örneği)

```html
<script>
if(typeof gtag==='function'){
  gtag('event','purchase',{transaction_id:"1001",value:599.99,...})
}
</script>
<script>
if(typeof fbq==='function'){
  fbq('track','Purchase',{value:599.99,currency:"TRY",...})
}
</script>
```

---

## Tenant Başına Tracking

Multi-tenant sistemde her tenant farklı ID'lere sahip olabilir. `TrackingManager`'ı request sırasında yeniden configure edin:

```php
// Middleware veya ServiceProvider:
$tracking = app(\Nlk\Theme\Tracking\TrackingManager::class);
$tracking->configure([
    'gtm_id'       => $tenant->gtm_id,
    'fb_pixel_id'  => $tenant->fb_pixel_id,
    'ga4_id'       => $tenant->ga4_id,
    'enabled'      => $tenant->tracking_enabled,
]);
```

---

## Tracking'i Devre Dışı Bırakma

```env
THEME_TRACKING_ENABLED=false
```

Veya programatik:

```php
// Admin önizleme modunda tracking iptal:
app(\Nlk\Theme\Tracking\TrackingManager::class)
    ->configure(['enabled' => false]);
```
