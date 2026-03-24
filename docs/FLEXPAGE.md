# FlexPage Engine

FlexPage, `snowsoft/nlktheme`'in JSON-driven, section tabanlı sayfa motoru adıdır. Shopify'ın tema mimarisinden ilham alır, ancak Laravel ekosistemi için tamamen özgün bir implementasyondur.

---

## Temel Kavramlar

| Kavram | Açıklama |
|---|---|
| **Page** | `home`, `category`, `product` gibi sayfa anahtar adı |
| **Section** | Sayfaya eklenebilen bağımsız içerik bloğu |
| **Section Type** | `hero`, `featured-products` gibi kayıtlı tip adı |
| **Section Schema** | Section ayarlarının şema tanımı (tip, label, default) |
| **Block** | Section içindeki tekrar eden alt öğeler |
| **Registry** | Tüm section tiplerinin merkezi kaydı |

---

## Section Tipleri

### built-in (hazır) tipler

| Type | Sınıf | Kaynak |
|---|---|---|
| `hero` | `HeroSection` | MySQL (sliders tablosu) |
| `announcement-bar` | `AnnouncementBarSection` | Static (ayarlardan) |
| `featured-products` | `FeaturedProductsSection` | MySQL veya API (seçilebilir) |
| `image-banner` | `BannerSection` | MySQL (afisler tablosu) |
| `collection-list` | `CollectionListSection` | MySQL (kategoriler tablosu) |
| `rich-text` | `RichTextSection` | Static |
| `custom-html` | `CustomHtmlSection` | Static (ham HTML) |

---

## SectionRegistry

Tüm section tiplerini saklar ve çözer.

```php
use Nlk\Theme\FlexPage\SectionRegistry;

$registry = app(SectionRegistry::class);

// Kayıt
$registry->register('countdown', \App\Sections\CountdownSection::class);

// Kontrol
$registry->has('countdown');    // true

// Çözme (instance döner)
$section = $registry->resolve('countdown');

// Tüm kayıtlı tipler
$registry->all();  // ['hero' => HeroSection::class, ...]
```

> `register()` sırasında class'ın `Section` interface'ini implement ettiği kontrol edilir. Etmiyorsa `InvalidArgumentException` fırlatılır.

---

## AbstractSection

Tüm section'ların miras aldığı temel sınıf.

### Override edilmesi gereken metodlar

```php
class MySection extends \Nlk\Theme\FlexPage\AbstractSection
{
    // [ZORUNLU] — section'ın type adı (registry anahtarı)
    public function type(): string
    {
        return 'my-section';
    }

    // [ZORUNLU] — section HTML çıktısı
    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('my-section', $data);
    }

    // [OPSİYONEL] — Shopify-benzeri schema tanımı
    public function schema(): array
    {
        return [
            'name'     => 'Benim Section\'ım',
            'settings' => [...],
            'presets'  => [...],
        ];
    }

    // [OPSİYONEL] — veri kaynağı: 'mysql' | 'api' | 'hybrid' | 'static'
    public function dataSource(): string
    {
        return 'mysql';
    }

    // [OPSİYONEL] — DB'den / API'den veri çek
    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return ['items' => $this->mysql()->fetchProducts(['tenant_id' => $tenantId])];
    }
}
```

### Yardımcı metodlar (AbstractSection'dan miras)

```php
// Veri adaptörleri
$this->mysql();    // MysqlAdapter
$this->api();      // ApiAdapter
$this->hybrid();   // HybridAdapter

// View render (önce tema klasörü, sonra vendor fallback)
$this->renderView('my-section', $data);
```

---

## Section Schema

Section ayarlarını tanımlayan JSON yapısı. Admin panel / page editor için kullanılır.

### Setting tipleri

| type | Açıklama | Örnek |
|---|---|---|
| `text` | Tek satır metin | Başlık, buton yazısı |
| `textarea` | Çok satır metin | Kısa açıklama |
| `richtext` | HTML editör | İçerik bloğu |
| `html` | Ham HTML girişi | Özel kod |
| `url` | URL girişi | Bağlantı |
| `select` | Açılır liste | Hizalama, kaynak seçimi |
| `checkbox` | Açma/kapama | Otomatik oynatma |
| `range` | Sayı aralığı | Ürün adedi, saniye |
| `color` | Renk seçici | Arka plan, metin rengi |
| `image_picker` | Görsel seçici | Banner görseli |

### Örnek schema

```php
public function schema(): array
{
    return [
        'name' => 'Kampanya Barı',
        'settings' => [
            [
                'type'    => 'text',
                'id'      => 'title',
                'label'   => 'Başlık',
                'default' => 'Bu haftaya özel!',
            ],
            [
                'type'    => 'select',
                'id'      => 'style',
                'label'   => 'Stil',
                'default' => 'dark',
                'options' => [
                    ['value' => 'dark',  'label' => 'Koyu'],
                    ['value' => 'light', 'label' => 'Açık'],
                ],
            ],
            [
                'type'    => 'range',
                'id'      => 'limit',
                'label'   => 'Ürün sayısı',
                'min'     => 2,
                'max'     => 24,
                'step'    => 2,
                'default' => 8,
            ],
            [
                'type'    => 'checkbox',
                'id'      => 'show_timer',
                'label'   => 'Geri sayım göster',
                'default' => true,
            ],
        ],
        'blocks'  => [],
        'presets' => [
            ['name' => 'Kampanya Barı', 'category' => 'Promotional'],
        ],
    ];
}
```

---

## Section View Dosyaları

### Arama önceliği

```
1. themes/{active-theme}/sections/{type}.blade.php   ← tema override
2. vendor/nlk/theme/resources/sections/{type}.blade.php ← vendor default
```

### Örnek view (`sections/hero.blade.php`)

```blade
<section class="hero hero--{{ $settings['height'] ?? 'medium' }}">
    @foreach($slides as $slide)
        <div class="hero__slide" data-id="{{ $slide['id'] }}">
            <img src="{{ $slide['fotograf'] }}" alt="{{ $slide['baslik'] }}">
            <div class="hero__content">
                <h2>{{ $slide['baslik'] }}</h2>
            </div>
        </div>
    @endforeach
</section>

@if(($settings['autoplay'] ?? true))
<script>
  initSlider('.hero', {{ $settings['interval'] ?? 5000 }});
</script>
@endif
```

---

## Section Kaydı — Yerleşim Yeri

```php
// AppServiceProvider.php → boot()
public function boot(): void
{
    app(\Nlk\Theme\FlexPage\SectionRegistry::class)
        ->register('my-hero',     \App\Sections\MyHeroSection::class)
        ->register('countdown',   \App\Sections\CountdownSection::class)
        ->register('testimonials',\App\Sections\TestimonialsSection::class);
}
```

---

## Veri Kaynağı Seçimi

`dataSource()` metodu, section'ın varsayılan veri kaynağını belirtir. `fetchData()` içinde adaptörleri serbest şekilde kullanabilirsiniz.

```php
public function fetchData(array $settings, ?string $tenantId = null): array
{
    $source = $settings['source'] ?? 'mysql';

    $products = match ($source) {
        'api'   => $this->api()->withTenant($tenantId)->fetchProducts(['per_page' => 8]),
        'mysql' => $this->mysql()->fetchProducts(['tenant_id' => $tenantId, 'limit' => 8]),
        default => [],
    };

    return ['products' => $products, 'settings' => $settings];
}
```
