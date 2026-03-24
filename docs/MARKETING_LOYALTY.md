# Pazarlama & Sadakat Sections (Faz 7)

`EmailCaptureSection`, `LoyaltyWidgetSection`, `ReferralSection`, `WebPushSection`

---

## EmailCaptureSection (`email-capture`)

### Tetikleyiciler

| `trigger` | Davranış |
|---|---|
| `exit_intent` | Fare sayfadan çıkarken tetiklenir |
| `scroll` | Sayfanın %50'si kaydırılınca |
| `delay` | Sayfa yüklendikten `delay_secs` saniye sonra |
| `always` | Her zaman gösterilir |

### Schema Ayarları

| ID | Varsayılan | Açıklama |
|---|---|---|
| `title` | `Özel Teklifler İçin Abone Ol` | Popup başlık |
| `subtitle` | `İlk siparişinde %10 indirim kazan!` | Alt başlık |
| `button_label` | `Abone Ol` | Submit butonu |
| `api_url` | `/api/v1/newsletter/subscribe` | Subscribe endpoint |
| `trigger` | `exit_intent` | Tetikleyici |
| `delay_secs` | `5` | Gecikme süresi |
| `show_image` | `true` | Görsel panel |
| `cookie_days` | `30` | Tekrar göster (gün) |

### Subscribe API

```
POST /api/v1/newsletter/subscribe
Content-Type: application/json

{
  "email": "user@example.com",
  "tenant_id": "tenant_1",
  "source": "email-capture-popup"
}

Response: { "success": true, "message": "Onay e-postası gönderildi" }
```

### FlexPage JSON

```json
{
  "type": "email-capture",
  "settings": {
    "title": "Özel Teklifler İçin Abone Ol",
    "subtitle": "İlk siparişinde %10 indirim!",
    "button_label": "Abone Ol",
    "api_url": "/api/v1/newsletter/subscribe",
    "trigger": "exit_intent",
    "delay_secs": 5,
    "cookie_days": 30,
    "show_image": true,
    "image": "img-promo-banner"
  }
}
```

### JavaScript Exit Intent

```blade
{{-- sections/email-capture.blade.php --}}
<div id="nlk-email-popup" class="nlk-popup" hidden>
  <div class="nlk-popup__inner">
    @if($show_image && $image)
      @cdn_img_lazy($image, ['w'=>400,'format'=>'auto'], ['alt' => $title])
    @endif
    <div class="nlk-popup__content">
      <h2>{{ $title }}</h2>
      <p>{{ $subtitle }}</p>
      <form id="nlk-subscribe-form">
        <input type="email" name="email" placeholder="{{ $placeholder }}" required>
        <button type="submit">{{ $button_label }}</button>
      </form>
      <p id="nlk-subscribe-msg" hidden>{{ $success_msg }}</p>
    </div>
    <button class="nlk-popup__close" onclick="this.closest('.nlk-popup').hidden=true">&times;</button>
  </div>
</div>

<script>
(function(){
  var popup      = document.getElementById('nlk-email-popup');
  var cookieName = 'nlk_email_popup';
  var cookieDays = {{ $cookie_days }};

  // Cookie kontrolü
  if (document.cookie.indexOf(cookieName) !== -1) return;

  function showPopup() {
    popup.hidden = false;
    var exp = new Date(); exp.setDate(exp.getDate() + cookieDays);
    document.cookie = cookieName + '=1; expires=' + exp.toUTCString() + '; path=/';
  }

  @if($trigger === 'exit_intent')
  document.addEventListener('mouseleave', function(e){ if(e.clientY < 10) showPopup(); });
  @elseif($trigger === 'scroll')
  window.addEventListener('scroll', function onScroll(){
    if(window.scrollY / document.body.scrollHeight > 0.5){ showPopup(); window.removeEventListener('scroll', onScroll); }
  });
  @elseif($trigger === 'delay')
  setTimeout(showPopup, {{ $delay_secs * 1000 }});
  @else
  showPopup();
  @endif

  document.getElementById('nlk-subscribe-form').addEventListener('submit', function(e){
    e.preventDefault();
    fetch('{{ $api_url }}', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content},
      body: JSON.stringify({ email: this.email.value })
    }).then(function(){
      document.getElementById('nlk-subscribe-msg').hidden = false;
      document.getElementById('nlk-subscribe-form').hidden = true;
    });
  });
})();
</script>
```

---

## LoyaltyWidgetSection (`loyalty-widget`)

### API Format

```
GET /api/v1/loyalty/balance
Authorization: Bearer {user_token}

Response:
{
  "data": {
    "points": 1250,
    "tier": "Gold",
    "tier_min": 1000,
    "tier_max": 5000,
    "next_tier": "Platinum",
    "expiry_date": "2026-12-31",
    "recent_transactions": [
      { "date": "2025-03-20", "desc": "Sipariş #4521", "points": +50 }
    ]
  }
}
```

### Görünüm Modları

| `display_mode` | Açıklama |
|---|---|
| `card` | Puan + tier kartı |
| `badge` | Kompakt rozet |
| `bar` | İlerleme çubuğu (tier'a kaç puan kaldı) |

### FlexPage JSON

```json
{
  "type": "loyalty-widget",
  "settings": {
    "title": "Puan Durumunuz",
    "points_label": "Puan",
    "api_url": "/api/v1/loyalty/balance",
    "redeem_url": "/profil/puanlar",
    "earn_rate_msg": "Her 100₺ harcamana 10 puan",
    "show_tier": true,
    "show_history": true,
    "display_mode": "bar"
  }
}
```

### Graceful Degradation

Kullanıcı giriş yapmamışsa `$balance` null olur — view şablonu giriş teşvik mesajı gösterir:

```blade
@if($balance)
  {{-- Puan göster --}}
@else
  <p><a href="/giris">Giriş yap</a> ve puanlarını gör</p>
@endif
```

---

## ReferralSection (`referral`)

### Sosyal Paylaşım URL Şablonları

| Platform | URL Yapısı |
|---|---|
| WhatsApp | `https://wa.me/?text={encoded_message}` |
| Telegram | `https://t.me/share/url?url={url}&text={text}` |
| Twitter/X | `https://twitter.com/intent/tweet?text={text}` |
| Facebook | `https://www.facebook.com/sharer/sharer.php?u={url}` |

### API Format

```
GET /api/v1/referral/code
Authorization: Bearer {user_token}

Response:
{
  "code": "MEHMET20",
  "share_url": "https://magazan.com/davet/MEHMET20",
  "earned_count": 3,
  "pending_count": 1
}
```

### FlexPage JSON

```json
{
  "type": "referral",
  "settings": {
    "title": "Arkadaşını Davet Et!",
    "subtitle": "Her başarılı davet için 50₺ kazan",
    "api_url": "/api/v1/referral/code",
    "referral_url_base": "https://magazan.com/davet/",
    "share_message": "Bu kodu kullan ve ilk siparişinde 50₺ indirim kazan: {code}",
    "show_whatsapp": true,
    "show_telegram": true,
    "show_twitter": false,
    "show_copy_link": true
  }
}
```

---

## WebPushSection (`web-push`)

### VAPID Kurulum

```bash
# VAPID anahtar çifti oluştur (bir kez)
composer require minishlink/web-push
php artisan webpush:vapid
```

```env
VAPID_PUBLIC_KEY=BExamplePublicKeyHere...
VAPID_PRIVATE_KEY=ExamplePrivateKeyHere...
VAPID_SUBJECT=mailto:admin@magazan.com
```

### Service Worker (`public/sw.js`)

```js
// PwaManifestGenerator::swContent() ile üretilir
// veya manuel olarak public/sw.js'e kopyalanır:
php artisan theme:pwa-sw > public/sw.js
```

### Subscribe API

```
POST /api/v1/push/subscribe
Content-Type: application/json

{
  "endpoint": "https://fcm.googleapis.com/...",
  "keys": {
    "p256dh": "...",
    "auth": "..."
  }
}
```

### Tetikleyiciler

| `trigger` | Davranış |
|---|---|
| `button` | Sadece butona tıklayınca izin ister |
| `auto` | Sayfa yüklendikten 5 saniye sonra |
| `scroll` | Scroll %50'de |

### FlexPage JSON

```json
{
  "type": "web-push",
  "settings": {
    "title": "Kampanyaları Kaçırma!",
    "description": "Özel indirim ve fırsatlardan ilk siz haberdar olun",
    "button_label": "Bildirimlere İzin Ver",
    "subscribe_url": "/api/v1/push/subscribe",
    "trigger": "button",
    "hide_if_subscribed": true
  }
}
```

> **Tarayıcı Desteği:** Chrome, Edge, Firefox, Safari 16.4+ (iOS)  
> **Gereklilik:** HTTPS zorunludur.
