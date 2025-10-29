# Hızlı Başlangıç Kılavuzu

NLK Theme paketinin yeni özelliklerini hızlıca kullanmaya başlamak için bu kılavuzu takip edin.

## Güvenlik Özellikleri

### Temel Güvenlik Kurulumu

1. **Middleware'i Route'a Ekleyin:**

```php
// routes/web.php
Route::middleware(['theme.security'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

2. **Helper Fonksiyonları ile XSS Koruması:**

```blade
{{-- Blade template'inde --}}
<div>
    {{-- Güvenli çıktı --}}
    <h1>{{ theme_security_escape($user->name) }}</h1>
    
    {{-- HTML içeriği temizle --}}
    {!! theme_security_sanitize($post->content, '<p><strong><em>') !!}
</div>
```

## Livewire Entegrasyonu

### Basit Kullanım

```php
// Controller'da
public function index()
{
    return Theme::view('home', [
        'counter' => theme_livewire('counter-component')
    ]);
}
```

```blade
{{-- Blade template'inde --}}
{!! theme_livewire('counter-component', ['initialCount' => 10]) !!}
```

## React Entegrasyonu

### Vite ile Kullanım

```blade
{{-- Layout dosyasında --}}
<!DOCTYPE html>
<html>
<head>
    {!! \Nlk\Theme\Support\ReactSSRSupport::viteAssets() !!}
</head>
<body>
    {!! theme_react('components/UserProfile', ['user' => $user]) !!}
</body>
</html>
```

## Inertia.js Entegrasyonu

### Basit Kullanım

```php
// Controller'da
public function index()
{
    return theme_inertia('Home/Index', [
        'posts' => Post::latest()->get()
    ]);
}
```

## Örnek: Tam Entegre Sayfa

```php
<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Nlk\Theme\Support\SecurityHelper;

class DashboardController extends Controller
{
    public function index()
    {
        // Güvenli veri hazırlama
        $safeData = [
            'user' => SecurityHelper::escape(auth()->user()->name),
            'posts' => Post::all()->map(function($post) {
                return [
                    'title' => SecurityHelper::escape($post->title),
                    'content' => SecurityHelper::sanitizeHtml($post->content),
                ];
            }),
        ];
        
        // Livewire component
        $counter = theme_livewire('dashboard-counter');
        
        // React component
        $chart = theme_react('components/StatsChart', ['data' => $stats]);
        
        return Theme::uses('admin')
            ->layout('dashboard')
            ->view('dashboard.index', array_merge($safeData, [
                'counter' => $counter,
                'chart' => $chart,
            ]));
    }
}
```

```blade
{{-- themes/admin/views/dashboard/index.blade.php --}}
<div class="dashboard">
    <h1>Hoşgeldiniz, {{ $user }}</h1>
    
    <div class="counter-section">
        {!! $counter !!}
    </div>
    
    <div class="chart-section">
        {!! $chart !!}
    </div>
    
    <div class="posts">
        @foreach($posts as $post)
            <article>
                <h2>{{ $post['title'] }}</h2>
                {!! $post['content'] !!}
            </article>
        @endforeach
    </div>
</div>
```

Bu örnekler ile hızlıca başlayabilirsiniz! Detaylı dokümantasyon için `docs/ADVANCED_FEATURES.md` dosyasına bakın.

