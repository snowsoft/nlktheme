# i18n & Market Switcher (Faz 9)

`SectionI18nResolver`, `CurrencyFormatter`, `MarketSwitcherSection`

---

## SectionI18nResolver

FlexPage section ayarlarında çok dil desteği sağlar.

### Nasıl Çalışır?

Section settings JSON'ında standart key yanında locale-specific key'ler tanımlanır:

```json
{
  "type": "hero",
  "settings": {
    "title":    "En İyi Ürünler",
    "title_en": "Best Products",
    "title_de": "Beste Produkte",
    "title_ar": "أفضل المنتجات",

    "subtitle":    "Her gün yeni fırsatlar",
    "subtitle_en": "New deals every day",

    "button_label":    "Alışverişe Başla",
    "button_label_en": "Start Shopping"
  }
}
```

`SectionI18nResolver::resolve($settings, 'en')` çağrısında:
- `title_en` bulunursa döner → `"Best Products"`
- `title_en` yoksa `title_tr` (fallback locale) aranır
- O da yoksa `title` döner

### PHP Kullanımı

```php
use Nlk\Theme\I18n\SectionI18nResolver;

$i18n = app('nlk.i18n');

// Mevcut locale'e göre section settings çöz
$resolved = $i18n->resolve($settings);

// Belirli locale ile çöz
$resolved = $i18n->resolve($settings, 'en');

// RTL sorgusu
$i18n->isRtl();        // → false (tr)
$i18n->isRtl('ar');    // → true

// HTML dir attribute
$i18n->htmlDir();      // → 'ltr'
$i18n->htmlDir('ar');  // → 'rtl'

// Desteklenen locale'ler
$i18n->supportedLocales();
// → ['tr', 'en', 'de', 'ar', 'ru']
```

### Blade Direktifleri

```blade
{{-- HTML lang + dir --}}
<html lang="{{ app()->getLocale() }}" dir="@html_dir">

{{-- Kaynak denetimiyle --}}
<html lang="{{ app()->getLocale() }}" dir="{{ app('nlk.i18n')->htmlDir() }}">
```

### Section View'da Kullanım

```blade
{{-- sections/hero.blade.php --}}
@php
  $data = app('nlk.i18n')->resolve($settings);
@endphp
<h1>{{ $data['title'] }}</h1>
<p>{{ $data['subtitle'] }}</p>
```

### AbstractSection Entegrasyonu

`fetchData()` metodunda otomatik i18n resolve eklenebilir:

```php
public function fetchData(array $settings, ?string $tenantId = null): array
{
    // Locale'e göre settings'i çöz
    $settings = app('nlk.i18n')->resolve($settings);

    return [
        'title'    => $settings['title'],
        'subtitle' => $settings['subtitle'],
        // ...
    ];
}
```

### Config

```env
THEME_LOCALES=tr,en,de,ar,ru
APP_LOCALE=tr
APP_FALLBACK_LOCALE=tr
```

```php
// config/theme.php
'i18n' => [
    'locales'     => explode(',', env('THEME_LOCALES', 'tr,en')),
    'rtl_locales' => ['ar', 'he', 'fa', 'ur'],
],
```

---

## CurrencyFormatter

PHP `intl` tabanlı çok para birimi formatlama.

### Blade Direktifleri

```blade
{{-- Tam format (PHP intl: locale'e göre) --}}
@currency(1234.50, 'TRY')     {{-- → ₺1.234,50 (tr) veya TRY 1,234.50 (en) --}}
@currency(1234.50, 'EUR', 'de') {{-- → 1.234,50 € --}}

{{-- Kısa format (sembol + number_format) --}}
@currency_short(1234.50, 'TRY')  {{-- → 1.234,50 ₺ --}}
@currency_short(99.90, 'USD')    {{-- → $99,90 --}}
```

### PHP Kullanımı

```php
$fmt = app('nlk.currency');

// Tam format (PHP intl)
$fmt->format(1234.5, 'TRY', 'tr');     // → "₺1.234,50"
$fmt->format(1234.5, 'EUR', 'de');     // → "1.234,50 €"
$fmt->format(1234.5, 'USD', 'en-US'); // → "$1,234.50"

// Kısa format
$fmt->short(1234.5, 'TRY');  // → "1.234,50 ₺"

// Büyük sayı kısaltma
$fmt->compact(1500, 'TRY');       // → "₺1.5K"
$fmt->compact(2500000, 'USD');    // → "$2.5M"

// Para birimi sembolü
$fmt->symbol('TRY');  // → "₺"
$fmt->symbol('EUR');  // → "€"

// Kur ayarla (TRY base)
$fmt->setRates(['USD' => 32.5, 'EUR' => 35.0]);

// Döviz çevirimi
$fmt->convert(100, 'TRY', 'USD');  // → 3.07...
$fmt->convert(100, 'TRY', 'EUR');  // → 2.86...
```

### Desteklenen Para Birimleri

| Kod | Sembol | Ad |
|---|---|---|
| TRY | ₺ | Türk Lirası |
| USD | $ | US Dollar |
| EUR | € | Euro |
| GBP | £ | British Pound |
| RUB | ₽ | Russian Ruble |
| AED | AED | UAE Dirham |
| SAR | SAR | Saudi Riyal |
| JPY | ¥ | Japanese Yen |

### Kullanıcı Tercih Para Birimi

```php
// Cookie'den kullanıcının tercih ettiği para birimini oku
$currency = request()->cookie('preferred_currency', 'TRY');

// Fiyatı kullanıcı para birimiyle göster
$fmt->setRates(config('app.exchange_rates', []));
echo $fmt->format($product->fiyat, $currency);
```

---

## MarketSwitcherSection (`market-switcher`)

### Schema Ayarları

| ID | Tip | Varsayılan | Açıklama |
|---|---|---|---|
| `show_language` | checkbox | `true` | Dil seçici |
| `show_currency` | checkbox | `true` | Para birimi seçici |
| `show_flags` | checkbox | `true` | Ülke bayrakları |
| `dropdown_style` | select | `dropdown` | `dropdown` / `modal` / `inline` |
| `lang_switch_url` | text | `/lang/{locale}` | Dil değiştirme URL şablonu |
| `currency_cookie` | text | `preferred_currency` | Cookie adı |

### Dil URL Şablonu

`{locale}` placeholder'ı seçilen locale ile değiştirilir:

```
/lang/{locale}    →  /lang/en, /lang/de, /lang/ar
/{locale}         →  /en, /de, /ar
https://en.site.com  →  Manuel URL
```

### Desteklenen Locale'ler

Config'de tanımlı locale'ler, aşağıdaki meta ile zenginleştirilir:

| Kod | Ad | Yerel Ad | Bayrak |
|---|---|---|---|
| `tr` | Türkçe | Türkçe | 🇹🇷 |
| `en` | English | English | 🇬🇧 |
| `de` | Deutsch | Deutsch | 🇩🇪 |
| `ar` | Arabic | العربية | 🇸🇦 |
| `ru` | Русский | Русский | 🇷🇺 |
| `fr` | Français | Français | 🇫🇷 |
| `es` | Español | Español | 🇪🇸 |

### FlexPage JSON

```json
{
  "type": "market-switcher",
  "settings": {
    "show_language": true,
    "show_currency": true,
    "show_flags": true,
    "dropdown_style": "dropdown",
    "lang_switch_url": "/lang/{locale}",
    "currency_cookie": "preferred_currency"
  }
}
```

### Blade View Örneği

```blade
{{-- sections/market-switcher.blade.php --}}
<div class="nlk-market-switcher">
  @if($show_language)
  <div class="nlk-market-switcher__lang">
    <button class="nlk-market-switcher__trigger">
      @if($show_flags)
        {{ collect($locales)->firstWhere('code', $current_locale)['flag'] ?? '🌐' }}
      @endif
      {{ strtoupper($current_locale) }}
    </button>
    <ul class="nlk-market-switcher__dropdown">
      @foreach($locales as $l)
      <li class="{{ $l['active'] ? 'active' : '' }}">
        <a href="{{ str_replace('{locale}', $l['code'], $lang_switch_url) }}">
          @if($show_flags) {{ $l['flag'] }} @endif
          {{ $l['native'] }}
        </a>
      </li>
      @endforeach
    </ul>
  </div>
  @endif

  @if($show_currency)
  <div class="nlk-market-switcher__currency">
    <button class="nlk-market-switcher__trigger">
      {{ collect($currencies)->firstWhere('code', $current_currency)['symbol'] ?? $current_currency }}
    </button>
    <ul class="nlk-market-switcher__dropdown">
      @foreach($currencies as $c)
      <li class="{{ $c['active'] ? 'active' : '' }}">
        <button onclick="NlkMarket.setCurrency('{{ $c['code'] }}', '{{ $currency_cookie }}')">
          {{ $c['symbol'] }} {{ $c['name'] }}
        </button>
      </li>
      @endforeach
    </ul>
  </div>
  @endif
</div>

<script>
window.NlkMarket = {
  setCurrency: function(code, cookieName) {
    var exp = new Date(); exp.setFullYear(exp.getFullYear() + 1);
    document.cookie = cookieName + '=' + code + '; expires=' + exp.toUTCString() + '; path=/';
    window.location.reload();
  }
};
</script>
```

### Laravel Dil Route'u

```php
// routes/web.php
Route::get('/lang/{locale}', function ($locale) {
    $supported = config('theme.i18n.locales', ['tr', 'en']);
    if (in_array($locale, $supported)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('lang.switch');
```

### Middleware Entegrasyonu

```php
// app/Http/Middleware/SetLocale.php
class SetLocale {
    public function handle($request, $next) {
        $locale = session('locale', config('app.locale'));
        if (in_array($locale, config('theme.i18n.locales', ['tr']))) {
            app()->setLocale($locale);
        }
        return $next($request);
    }
}
```
