# NLK Theme Package

Laravel için kapsamlı bir tema yönetim paketi. Birden fazla tema desteği, component sistemı, cache yönetimi ve daha fazlası.

## Özellikler

- ✅ Çoklu tema desteği
- ✅ Default theme fallback sistemi
- ✅ Component ve partial sistemi
- ✅ Section ve Stack yönetimi
- ✅ Asset yönetimi
- ✅ Cache yönetimi
- ✅ Widget sistemi
- ✅ Event sistemi
- ✅ Helper fonksiyonları
- ✅ **Güvenlik özellikleri** (XSS koruması, CSP, güvenlik header'ları)
- ✅ **Livewire desteği**
- ✅ **React + SSR desteği** (Vite entegrasyonu)
- ✅ **Inertia.js desteği**

## Kurulum

```bash
composer require nlk/theme
```

## Temel Kullanım

### View Render

```php
// Controller'da
use Theme;

public function index()
{
    return Theme::view('home');
}

// Veya helper ile
return theme()->view('home');
```

### Theme Ayarlama

```php
// Belirli tema kullan
Theme::uses('tema2')->layout('main')->view('home');

// Helper ile
theme('tema2', 'main')->view('home');
```

## Default Theme Fallback

Bir dosya mevcut temada bulunamazsa, otomatik olarak default temadan yüklenir.

### Config Ayarları

`config/theme.php`:
```php
'defaultTheme' => 'default',
'useDefaultThemeFallback' => true, // ✅ true yapın
```

### Örnek Kullanım

```php
// tema2 temasında header.blade.php yoksa
// otomatik olarak default temasından yükler
Theme::partial('header');

// tema2/views/home.blade.php yoksa
// default/views/home.blade.php arar
Theme::scope('home');
```

## Partial ve Component

### Partial

```php
// Partial render
Theme::partial('header', ['title' => 'Welcome']);

// Watch partial (önce temada, sonra app'te ara)
Theme::watchPartial('footer');

// Helper
{!! theme_partial('header') !!}
```

### Component

```php
// Component kaydet
theme()->component('card', 'components.card');

// Component render
theme()->renderComponent('card', ['title' => 'Card Title', 'body' => 'Content']);

// Helper
{!! theme_component('card', ['title' => 'Test']) !!}
```

### Blade'de Kullanım

```blade
@partial('header')
{!! theme_component('card', ['title' => 'Hello']) !!}
```

## Section Sistemi

```php
// Section başlat
theme()->startSection('sidebar');
    echo "Sidebar content";
theme()->stopSection('sidebar');

// Section içeriğini al
$sidebar = theme()->getSection('sidebar');

// Section var mı kontrol et
if (theme()->hasSection('sidebar')) {
    echo theme_section('sidebar');
}
```

### Blade Helper

```blade
{!! theme_section('sidebar', 'Default content') !!}

@if(has_theme_section('sidebar'))
    {!! theme_section('sidebar') !!}
@endif
```

## Stack Sistemi

Birden fazla yerde içerik toplamak için:

```php
// Stack başlat
theme()->startStack('scripts');
echo '<script src="plugin1.js"></script>';
theme()->pushStack('scripts');

// Başka yerde
theme()->startStack('scripts');
echo '<script src="plugin2.js"></script>';
theme()->pushStack('scripts');

// Tüm scriptleri al
echo theme()->getStack('scripts');
```

### Blade Helper

```blade
{!! theme_stack('scripts') !!}
```

## Asset Yönetimi

### Helper Fonksiyonları

```php
// Asset URL
theme_asset('css/style.css');
// Output: /themes/default/assets/css/style.css

// CSS tag
theme_css('css/style.css');
// Output: <link rel='stylesheet' type='text/css' href='...' media='all'>

// JS tag
theme_js('js/app.js');
// Output: <script type='text/javascript' src='...'></script>

// Image tag
theme_image('images/logo.png', 'Logo', ['class' => 'logo']);
```

### Blade'de Kullanım

```blade
<!DOCTYPE html>
<html>
<head>
    {!! theme_css('style.css') !!}
</head>
<body>
    <img src="{{ theme_asset('images/logo.png') }}">
    
    {!! theme_js('app.js') !!}
</body>
</html>
```

## Theme Data

Global tema verilerini yönetme:

```php
// Veri set et
theme()->setData('user', $user);
theme()->setMultipleData(['key1' => 'val1', 'key2' => 'val2']);

// Veri al
$user = theme()->getData('user');
$allData = theme()->getAllData();

// Helper
theme_set('key', 'value');
$value = theme_get('key', 'default');
```

## Cache Yönetimi

```php
// Tüm cache'i temizle
theme()->clearCache();

// Belirli temanın cache'ini temizle
theme()->clearThemeCache('tema2');

// Temayı yeniden yükle
theme()->reload();

// Helper
theme_cache_clear();
theme_cache_clear('tema2');
```

## View Kontrolü

```php
// View var mı kontrol et
if (theme()->viewExists('home')) {
    return theme()->view('home');
}

// Theme'deki tüm view'ları listele
$views = theme()->getThemeViews();

// Helper
if (has_theme_view('partial.header')) {
    {!! theme_partial('partial.header') !!}
}

// Varsa render et
render_if_exists('partial.header', [], 'Default content');
```

## Helper Fonksiyonlar

### Metin İşlemleri

```php
// Metin kısaltma
theme_truncate('Long text here...', 50);
// Output: "Long text here..."

// Time ago
time_ago('2024-01-01 12:00:00');
// Output: "2 hours ago"

// Number format
number_format_short(1000);
// Output: "1K"
number_format_short(1500000);
// Output: "1.5M"
```

### Navigation Helper

```php
// Active class
active_class('home', 'active');
// Returns: 'active' if current route is 'home'

// Blade'de
<li class="{{ active_class('home') }}">Home</li>
```

## Widget Sistemi

```php
// Widget render
Theme::widget('Menu', ['items' => $menuItems]);

// Blade'de
@widget('Menu', ['items' => $items])
```

## Region (Alan) Yönetimi

```php
// Region set et
theme()->set('meta', '<meta name="keywords" content="...">');

// Region append
theme()->append('scripts', '<script>...</script>');

// Region prepend
theme()->prepend('styles', '<link>...</link>');

// Region al
$meta = theme()->get('meta', 'default');

// Region var mı kontrol et
if (theme()->has('meta')) {
    echo theme()->get('meta');
}

// Blade'de
@get('meta')
@getIfHas('meta')
```

## Event Sistemi

### Theme Event'leri

```php
// Theme yükleme öncesi
Theme::fire('before', $theme);
Theme::fire('asset', $asset);
Theme::fire('beforeRenderTheme', $theme);
Theme::fire('beforeRenderLayout.main', $theme);
Theme::fire('after', $theme);
```

### Config'de Event Tanımlama

`themes/tema2/config.php`:
```php
return [
    'events' => [
        'before' => function($theme) {
            $theme->set('title', 'My Site');
        },
        'beforeRenderLayout.main' => function($theme) {
            $theme->set('description', 'Main layout');
        },
    ],
];
```

## Event Usage Example

```php
// ThemeServiceProvider.php
public function boot()
{
    Theme::composer('*', function($view) {
        $view->with('currentUser', auth()->user());
    });
}
```

## Advanced Features

### Compile and Cache

```php
$content = theme()->compileAndCache(
    'Welcome {{ $name }}', 
    ['name' => 'John'],
    'welcome_cache',
    3600 // TTL
);
```

### String Compilation

```php
$html = theme()->blader('Hello {{ $name }}', ['name' => 'World']);
```

### Get Compiled Path

```php
$compiledPath = theme()->getCompiledPath('theme.tema2.views.home');
```

## Tema Yapısı

```
themes/
├── default/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   ├── views/
│   │   └── *.blade.php
│   ├── layouts/
│   │   ├── main.blade.php
│   │   └── layout.blade.php
│   ├── partials/
│   │   ├── header.blade.php
│   │   └── footer.blade.php
│   ├── components/
│   │   └── card.blade.php
│   ├── config.php
│   └── theme.json
│
└── tema2/
    ├── assets/
    ├── views/
    ├── layouts/
    └── ...
```

## Config Dosyası

`config/theme.php`:

```php
return [
    'assetUrl' => '/',
    'defaultTheme' => 'default',
    'useDefaultThemeFallback' => true,  // Default theme fallback
    'version' => '1.0.0',
    'themeDefault' => 'default',
    'layoutDefault' => 'layout',
    'themeDir' => 'themes',
    'themeURL' => 'themes',
    'templateCacheEnabled' => true,
    'autoReload' => false,
    'defaultEngine' => 'blade',
    'minify' => false,
    'namespaces' => [
        'widget' => 'App\Widgets'
    ],
    'events' => [
        // Global events
    ],
];
```

## Middleware Kullanımı

```php
// Route'da
Route::get('/home', [HomeController::class, 'index'])
    ->middleware('theme:tema2,main');

// Controller'da
public function index()
{
    return theme()->view('home');
}
```

## Helper Listesi

- `theme($theme, $layout)` - Theme instance al
- `theme_asset($path)` - Asset URL
- `theme_css($path, $media)` - CSS tag
- `theme_js($path, $defer)` - JS tag
- `theme_image($path, $alt, $attrs)` - Image tag
- `theme_partial($view, $args)` - Partial render
- `theme_component($name, $data)` - Component render
- `theme_section($section, $default)` - Section get
- `theme_stack($name)` - Stack get
- `active_class($path, $active)` - Active class
- `theme_truncate($text, $limit)` - Truncate
經歷 `time_ago($datetime)` - Time ago
- `number_format_short($number)` - Short number
- `theme_cache_clear($theme)` - Clear cache
- `has_theme_view($view)` - Check view exists
- `has_theme_section($section)` - Check section
- `render_if_exists($view, $args, $default)` - Render if exists
- `theme_set($key, $value)` - Set theme data
- `theme_get($key, $default)` - Get theme data

## Gelişmiş Özellikler

Paket artık modern frontend framework'leri ve gelişmiş güvenlik özellikleri ile birlikte geliyor:

### Güvenlik

- XSS koruması ve HTML sanitization
- Content Security Policy (CSP) desteği
- Güvenlik header'ları (X-Frame-Options, X-XSS-Protection, vb.)
- View path validation

### Modern Frontend Desteği

- **Livewire**: Server-side reactive component'ler
- **React + SSR**: Vite entegrasyonu ve server-side rendering
- **Inertia.js**: SPA deneyimi için tam destek

Detaylı dokümantasyon için:
- [Hızlı Başlangıç](docs/QUICK_START.md)
- [Gelişmiş Özellikler](docs/ADVANCED_FEATURES.md)

## License

[LICENSE](LICENSE)

## Support

Sorunlarınız için issue açabilirsiniz.