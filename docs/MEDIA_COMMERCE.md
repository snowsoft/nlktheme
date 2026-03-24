# Medya & Ticaret Sections (Faz 6)

`VideoCommerceSection`, `ShoppableImageSection`, `BlogListSection`, `InstagramFeedSection`

---

## VideoCommerceSection (`video-commerce`)

### Desteklenen Video Kaynakları

| `video_type` | Açıklama |
|---|---|
| `youtube` | YouTube embed (ID veya URL) |
| `vimeo` | Vimeo embed (ID veya URL) |
| `cdn` | CDN HLS/DASH stream |
| `direct` | Doğrudan MP4 URL |

### CDN Video URL Yapısı

```
HLS  : {CDN_URL}/api/video/{cdn_video_id}/hls
DASH : {CDN_URL}/api/video/{cdn_video_id}/dash
Thumb: {CDN_URL}/api/video/{cdn_video_id}/thumbnail
```

### Schema Ayarları

| ID | Tip | Varsayılan |
|---|---|---|
| `title` | text | `''` |
| `video_type` | select | `youtube` |
| `video_url` | text | `''` |
| `cdn_video_id` | text | `''` |
| `poster` | image_picker | `''` |
| `autoplay` | checkbox | `false` |
| `loop` | checkbox | `false` |
| `show_controls` | checkbox | `true` |
| `aspect_ratio` | select | `16:9` |
| `cta_label` | text | `Şimdi Al` |
| `cta_url` | text | `''` |

### FlexPage JSON Örneği

```json
{
  "type": "video-commerce",
  "settings": {
    "video_type": "cdn",
    "cdn_video_id": "vid-abc123",
    "aspect_ratio": "16:9",
    "autoplay": false,
    "cta_label": "Şimdi Satın Al",
    "cta_url": "/urun/iphone-15"
  }
}
```

### Blade View Örneği

```blade
{{-- sections/video-commerce.blade.php --}}
<div class="nlk-video-commerce">
  @if($title)<h2>{{ $title }}</h2>@endif

  <div class="nlk-video-wrap" style="aspect-ratio: {{ str_replace(':', '/', $aspect_ratio) }}">
    @if($video_type === 'cdn' && $hls_url)
      <video poster="{{ $poster }}" {{ $autoplay ? 'autoplay muted' : '' }}
             {{ $loop ? 'loop' : '' }} {{ $show_controls ? 'controls' : '' }}>
        <source src="{{ $hls_url }}" type="application/x-mpegURL">
        <source src="{{ $dash_url }}" type="application/dash+xml">
      </video>
    @elseif($video_type === 'youtube')
      <iframe src="https://www.youtube.com/embed/{{ $video_url }}{{ $autoplay ? '?autoplay=1&mute=1' : '' }}"
              allowfullscreen loading="lazy"></iframe>
    @elseif($video_type === 'vimeo')
      <iframe src="https://player.vimeo.com/video/{{ $video_url }}"
              allowfullscreen loading="lazy"></iframe>
    @else
      <video src="{{ $video_url }}" poster="{{ $poster }}"
             {{ $autoplay ? 'autoplay muted' : '' }} {{ $loop ? 'loop' : '' }}
             {{ $show_controls ? 'controls' : '' }}></video>
    @endif
  </div>

  @if($cta_label && $cta_url)
  <a class="nlk-video-commerce__cta" href="{{ $cta_url }}">{{ $cta_label }}</a>
  @endif
</div>
```

---

## ShoppableImageSection (`shoppable-image`)

### Blok Yapısı

Her `hotspot` bloğu şu ayarlara sahiptir:

| Ayar | Açıklama |
|---|---|
| `x_pos` | Yatay pozisyon (0-100%) |
| `y_pos` | Dikey pozisyon (0-100%) |
| `product_id` | Ürün ID |
| `product_url` | Ürün sayfası URL |
| `label` | Tooltip etiketi |

### FlexPage JSON Örneği

```json
{
  "type": "shoppable-image",
  "settings": {
    "cdn_image_id": "img-outfit-5",
    "title": "Bu Görünümü Satın Al",
    "hotspot_style": "pulse"
  },
  "blocks": [
    {
      "type": "hotspot",
      "settings": { "x_pos": 42, "y_pos": 30, "product_id": "101", "product_url": "/urun/gomlek", "label": "Gömlek" }
    },
    {
      "type": "hotspot",
      "settings": { "x_pos": 55, "y_pos": 70, "product_id": "202", "product_url": "/urun/pantolon", "label": "Pantolon" }
    }
  ]
}
```

### Hotspot Stilleri

| Stil | Açıklama |
|---|---|
| `pulse` | Nabız animasyonu (dikkat çekici) |
| `pin` | Harita pin ikonu |
| `dot` | Sade nokta |

### Blade View Örneği

```blade
{{-- sections/shoppable-image.blade.php --}}
<div class="nlk-shoppable">
  @if($title)<h2>{{ $title }}</h2>@endif
  <div class="nlk-shoppable__wrap">
    @cdn_img_lazy($cdn_image_id, ['format' => 'auto', 'fit' => 'cover'], ['alt' => $title])

    @foreach($hotspots as $spot)
    <button class="nlk-shoppable__dot nlk-shoppable__dot--{{ $hotspot_style }}"
            style="left:{{ $spot['x'] }}%;top:{{ $spot['y'] }}%"
            data-url="{{ $spot['product_url'] }}"
            aria-label="{{ $spot['label'] }}">
      <span class="nlk-shoppable__tooltip">{{ $spot['label'] }}</span>
    </button>
    @endforeach
  </div>
</div>
```

---

## BlogListSection (`blog-list`)

### JSON-LD Article Schema

Section `jsonld` özelliği yoktur; **otomatik JSON-LD** view şablonundan oluşturulur:

```blade
@if(!empty($posts))
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Blog",
  "blogPost": [
    @foreach($posts as $post)
    {
      "@type": "BlogPosting",
      "headline": "{{ $post['baslik'] }}",
      "datePublished": "{{ $post['yayinlanma_tarihi'] }}",
      "url": "{{ $post['url'] }}"
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endif
```

### MySQL Tablosu

```sql
CREATE TABLE blog_yazilari (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id VARCHAR(100) NOT NULL,
  baslik VARCHAR(255),
  ozet TEXT,
  icerik LONGTEXT,
  yazar VARCHAR(100),
  slug VARCHAR(255),
  resim_id VARCHAR(100),      -- CDN image ID
  kategori VARCHAR(100),
  yayinlanma_tarihi DATETIME,
  okuma_suresi INT DEFAULT 5, -- dakika
  yayinda TINYINT(1) DEFAULT 0,
  INDEX (tenant_id, yayinda, yayinlanma_tarihi)
);
```

---

## InstagramFeedSection (`instagram-feed`)

### API Format

```
GET /api/v1/instagram/feed?limit=8&tenant_id=xxx

Response:
{
  "data": [
    {
      "id": "123",
      "media_type": "IMAGE",
      "media_url": "https://...",
      "cdn_image_id": "ig-abc",   // CDN'e import edildiyse
      "permalink": "https://instagram.com/p/...",
      "caption": "...",
      "like_count": 142,
      "timestamp": "2025-03-01T12:00:00Z"
    }
  ]
}
```

### FlexPage JSON Örneği

```json
{
  "type": "instagram-feed",
  "settings": {
    "title": "Instagram'da Bizi Takip Edin",
    "username": "@magazan",
    "api_url": "/api/v1/instagram/feed",
    "limit": 8,
    "columns": 4,
    "show_hover_overlay": true,
    "follow_url": "https://instagram.com/magazan",
    "follow_label": "Takip Et"
  }
}
```

> **Not:** Instagram Basic Display API erişimi için token yönetimi uygulama tarafında yapılmalıdır. `cdn.nlkmenu.com/importFromUrl()` ile görseller CDN'e önbelleklenebilir.
