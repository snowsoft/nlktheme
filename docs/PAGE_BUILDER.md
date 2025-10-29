# Page Builder Dokümantasyonu

Ana sayfa ve içerik sayfaları için widget ve blade component'lerini sıralayarak oluşturabileceğiniz page builder sistemi.

## İçindekiler

1. [Genel Bakış](#genel-bakış)
2. [Hızlı Başlangıç](#hızlı-başlangıç)
3. [Temel Kullanım](#temel-kullanım)
4. [Gelişmiş Özellikler](#gelişmiş-özellikler)
5. [Controller Entegrasyonu](#controller-entegrasyonu)
6. [Admin Panel Entegrasyonu](#admin-panel-entegrasyonu)

---

## Genel Bakış

Page Builder sistemi, ana sayfa ve diğer içerik sayfalarınızı widget, component ve partial'ları sıralayarak oluşturmanıza olanak sağlar. Tüm konfigürasyon JSON formatında saklanır ve cache'lenir.

### Özellikler

- ✅ Widget sıralama ve konumlandırma
- ✅ Blade Component entegrasyonu
- ✅ Partial view desteği
- ✅ Widget desteği
- ✅ JSON tabanlı konfigürasyon
- ✅ Cache desteği
- ✅ Dinamik sayfa render

---

## Hızlı Başlangıç

### 1. Page Builder Instance Oluşturma

```php
use Nlk\Theme\PageBuilder\PageBuilder;

$builder = new PageBuilder();
```

Ya da helper fonksiyon ile:

```php
$builder = page_builder();
```

### 2. Basit Sayfa Oluşturma

```php
use Nlk\Theme\PageBuilder\PageBuilder;

$builder = new PageBuilder();

// Sayfa oluştur
$builder->page('home')
    ->setTheme('default')
    ->setLayout('main')
    ->addPartial('header', ['title' => 'Ana Sayfa'])
    ->addWidget('HeroSlider', ['slides' => $slides])
    ->addComponent('featured-products', ['count' => 8])
    ->addPartial('footer')
    ->save();
```

---

## Temel Kullanım

### Sayfa Oluşturma

```php
$builder = page_builder();

$builder->page('home')
    ->setTheme('default')      // Tema seç
    ->setLayout('main')         // Layout seç
    ->addWidget('HeroSlider')   // Widget ekle
    ->addComponent('products')  // Component ekle
    ->addPartial('newsletter')  // Partial ekle
    ->save();                   // Kaydet
```

### Widget Ekleme

```php
// Basit widget
$builder->page('home')
    ->addWidget('MenuWidget');

// Widget ile veri gönderme
$builder->page('home')
    ->addWidget('ProductGrid', [
        'products' => $products,
        'columns' => 4,
        'limit' => 12
    ]);
```

### Component Ekleme

```php
// Component ekleme
$builder->page('home')
    ->addComponent('featured-products', [
        'title' => 'Öne Çıkan Ürünler',
        'count' => 8
    ]);
```

### Partial Ekleme

```php
// Partial ekleme
$builder->page('home')
    ->addPartial('header', ['title' => 'Ana Sayfa'])
    ->addPartial('footer');
```

### Sıralama (Position)

```php
// Belirli pozisyona ekleme
$builder->page('home')
    ->addPartial('header', [], 0)        // İlk sıra
    ->addWidget('HeroSlider', [], 1)     // İkinci sıra
    ->addComponent('products', [], 2)    // Üçüncü sıra
    ->addPartial('footer', [], 10);      // Onuncu sıra
```

---

## Gelişmiş Özellikler

### Section Sıralama

```php
// Mevcut section'ları sırala
$builder->page('home')
    ->sortSections();

// Manuel sıralama değiştirme
$builder->page('home')
    ->updateSectionPosition(2, 0); // 2. pozisyondaki section'ı 0. pozisyona taşı

// Section silme
$builder->page('home')
    ->removeSection(3); // 3. pozisyondaki section'ı sil
```

### Sayfa Konfigürasyonu Yükleme

```php
// Mevcut sayfa konfigürasyonunu yükle
$config = $builder->page('home')->getConfig();

// Section'ları al
$sections = $builder->page('home')->getSections();
```

### Sayfa Render

```php
use Nlk\Theme\PageBuilder\PageRenderer;

$builder = page_builder()->page('home');
$renderer = new PageRenderer($builder);

// Sayfayı render et
return $renderer->render('home');
```

### Sayfa Silme

```php
// Sayfa konfigürasyonunu sil
$builder->delete('old-page');
```

---

## Controller Entegrasyonu

### Basit Controller Kullanımı

```php
<?php

namespace App\Http\Controllers;

use Nlk\Theme\PageBuilder\PageBuilder;
use Nlk\Theme\PageBuilder\PageRenderer;

class PageController extends Controller
{
    protected $builder;
    protected $renderer;

    public function __construct()
    {
        $this->builder = new PageBuilder();
        $this->renderer = new PageRenderer($this->builder);
    }

    public function home()
    {
        return $this->renderer->render('home');
    }

    public function about()
    {
        return $this->renderer->render('about');
    }
 можем}
```

### Dynamic Page Route

```php
// routes/web.php
Route::get('/pages/{page}', [PageController::class, 'show']);

// Controller
public function show($page)
{
    $builder = page_builder()->page($page);
    
    if (!$builder->getConfig()) {
        abort(404, 'Page not found');
    }
    
    return (new PageRenderer($builder))->render($page);
}
```

### Sayfa Oluşturma Endpoint'i

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Nlk\Theme\PageBuilder\PageBuilder;
use Illuminate\Http\Request;

class PageBuilderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'page_name' => 'required|string',
            'sections' => 'required|array',
        ]);

        $builder = new PageBuilder();
        
        $builder->page($request->page_name)
            ->setTheme($request->theme ?? 'default')
            ->setLayout($request->layout ?? 'main');

        // Sections ekle
        foreach ($request->sections as $section) {
            switch ($section['type']) {
                case 'widget':
                    $builder->addWidget($section['name'], $section['data'] ?? [], $section['position'] ?? null);
                    break;
                case 'component':
                    $builder->addComponent($section['name'], $section['data'] ?? [], $section['position'] ?? null);
                    break;
                case 'partial':
                    $builder->addPartial($section['name'], $section['data'] ?? [], $section['position'] ?? null);
                    break;
            }
        }

        $builder->save();

        return response()->json(['message' => 'Page created successfully']);
    }

    public function update(Request $request, $pageName)
    {
        $builder = new PageBuilder();
        $builder->page($pageName);

        // Section pozisyonlarını güncelle
        foreach ($request->sections as $section) {
            $builder->updateSectionPosition(
                $section['old_position'],
                $section['new_position']
            );
        }

        $builder->save();

        return response()->json(['message' => 'Page updated successfully']);
    }

    public function destroy($pageName)
    {
        $builder = new PageBuilder();
        $builder->delete($pageName);

        return response()->json(['message' => 'Page deleted successfully']);
    }
}
```

---

## Admin Panel Entegrasyonu

### Blade View Örneği

```blade
{{-- resources/views/admin/pagebuilder/create.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="page-builder">
    <div class="builder-toolbar">
        <h2>Sayfa Oluşturucu</h2>
        <button id="save-page" class="btn btn-primary">Kaydet</button>
    </div>

    <div class="builder-container">
        <div class="widgets-panel">
            <h3>Widget'lar</h3>
            <div class="widget-list" id="available-widgets">
                <div class="widget-item" data-type="widget" data-name="HeroSlider">
                    Hero Slider
                </div>
                <div class="widget-item" data-type="component" data-name="featured-products">
                    Featured Products
                </div>
                <div class="widget-item" data-type="partial" data-name="newsletter">
                    Newsletter
                </div>
            </div>
        </div>

        <div class="canvas-area" id="page-canvas">
            <div class="drop-zone">
                <p>Sürükle ve bırak ile widget ekleyin</p>
            </div>
        </div>
    </div>
</div>

<script>
// Drag and drop implementation
// Sortable.js veya benzeri kütüphane kullanılabilir
</script>
@endsection
```

### AJAX ile Sayfa Kaydetme

```javascript
// resources/js/admin/pagebuilder.js
document.getElementById('save-page').addEventListener('click', function() {
    const sections = collectSections(); // Canvas'tan section'ları topla
    
    fetch('/admin/pagebuilder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            page_name: 'home',
            theme: 'default',
            layout: 'main',
            sections: sections
        })
    })
    .then(response => response.json())
    .then(data => {
        alert('Sayfa kaydedildi!');
    });
});
```

---

## Örnek Kullanımlar

### Ana Sayfa Oluşturma

```php
$builder = page_builder();

$builder->page('home')
    ->setTheme('default')
    ->setLayout('main')
    
    // Header
    ->addPartial('header', ['title' => 'Ana Sayfa'], 0)
    
    // Hero Section
    ->addWidget('HeroSlider', [
        'slides' => [
            ['image' => '/img/slide1.jpg', 'title' => 'Slide 1'],
            ['image' => '/img/slide2.jpg', 'title' => 'Slide 2'],
        ]
    ], 1)
    
    // Featured Products
    ->addComponent('featured-products', [
        'title' => 'Öne Çıkan Ürünler',
        'count' => 8,
        'columns' => 4
    ], 2)
    
    // Categories
    ->addWidget('CategoryGrid', ['limit' => 6], 3)
    
    // Newsletter
    ->addPartial('newsletter', [], 4)
    
    // Footer
    ->addPartial('footer', [], 5)
    
    ->save();
```

### Dinamik İçerik Sayfası

```php
$builder = page_builder();

$builder->page('category-electronics')
    ->setTheme('default')
    ->setLayout('main')
    ->addPartial('header', ['title' => 'Elektronik'], 0)
    ->addWidget('CategoryProducts', [
        'category' => 'electronics',
        'limit' => 20,
        'sort' => 'price_asc'
    ], 1)
    ->addPartial('footer', [], 2)
    ->save();
```

---

## Helper Fonksiyonlar

```php
// Page builder instance al
$builder = page_builder();

// Hızlı sayfa render
page_render('home');

// Section ekle
page_add_widget('home', 'HeroSlider', ['data' => []]);
page_add_component('home', 'products', ['count' => 8]);
page_add_partial('home', 'header', ['title' => 'Home']);
```

---

## JSON Konfigürasyon Formatı

Sayfa konfigürasyonları `storage/app/pagebuilder/{page-name}.json` dosyasında saklanır:

```json
{
    "theme": "default",
    "layout": "main",
    "sections": [
        {
            "type": "partial",
            "name": "header",
            "data": {
                "title": "Ana Sayfa"
            },
            "position": 0
        },
        {
            "type": "widget",
            "name": "HeroSlider",
            "data": {
                "slides": []
            },
            "position": 1
        },
        {
            "type": "component",
            "name": "featured-products",
            "data": {
                "count": 8
            },
            "position": 2
        }
    ]
}
```

---

## Cache Yönetimi

Page builder otomatik olarak cache kullanır. Cache'i temizlemek için:

```php
// Belirli sayfa cache'ini temizle
Cache::forget('page_builder_home');

// Tüm page builder cache'lerini temizle
Cache::tags(['page_builder'])->flush();
```

---

## Sorun Giderme

### Section Render Edilmiyor

- Widget/Component/Partial'ın mevcut olduğundan emin olun
- Data formatının doğru olduğunu kontrol edin
- Cache'i temizleyin

### Pozisyon Sorunları

- `sortSections()` metodunu çağırarak sıralamayı yenileyin
- Position değerlerinin sıralı olduğundan emin olun

---

Detaylı örnekler ve gelişmiş kullanımlar için dokümantasyona devam edin.

