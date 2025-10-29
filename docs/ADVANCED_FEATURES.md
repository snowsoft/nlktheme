# Gelişmiş Özellikler Dokümantasyonu

Bu dokümantasyon, NLK Theme paketinin gelişmiş özelliklerini ve modern frontend framework entegrasyonlarını açıklar.

## İçindekiler

1. [Güvenlik Özellikleri](#güvenlik-özellikleri)
2. [Livewire Desteği](#livewire-desteği)
3. [React + SSR Desteği](#react--ssr-desteği)
4. [Inertia.js Desteği](#inertiajs-desteği)

---

## Güvenlik Özellikleri

### Kurulum

Güvenlik middleware'ini route'larınıza ekleyin:

```php
// routes/web.php
Route::middleware(['theme.security'])->group(function () {
    Route::get('/home', [HomeController::class, 'index']);
});
```

Ya da global olarak tüm route'lara uygulayın:

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... diğer middleware'ler
        \Nlk\Theme\Middleware\SecurityMiddleware::class,
    ],
];
```

### Güvenlik Headers

Güvenlik header'ları otomatik olarak eklenir:

```php
// config/theme.php
'security' => [
    'headers' => true, // Güvenlik header'larını etkinleştir
    
    'headers_config' => [
        'x_content_type' => true,
        'x_frame_options' => true,
        'x_frame_options_value' => 'DENY',
        'x_xss_protection' => true,
        'referrer_policy' => true,
        'referrer_policy_value' => 'strict-origin-when-cross-origin',
    ],
],
```

### Content Security Policy (CSP)

CSP'yi etkinleştirmek için:

```php
// config/theme.php
'security' => [
    'csp' => [
        'enabled' => true,
        'directives' => [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline'",
            'style-src' => "'self' 'unsafe-inline'",
            'img-src' => "'self' data: https:",
            'font-src' => "'self' data:",
        ],
    ],
],
```

### XSS Koruması

#### XSS'den Kaçınma (Escaping)

```php
// Controller'da
use Nlk\Theme\Security\SecurityHelper;

public function index()
{
    $userInput = request()->input('name');
    
    // Güvenli çıktı
    $safeOutput = SecurityHelper::escape($userInput);
    
    return view('home', ['name' => $safeOutput]);
}
```

```blade
{{-- Blade'de --}}
<div>{{ theme_security_escape($userInput) }}</div>
```

#### HTML Sanitization

```php
// Güvensiz HTML içeriği temizle
$unsafeHtml = '<script>alert("XSS")</script><p>Güvenli içerik</p>';

// Sadece belirli tag'lere izin ver
$safeHtml = SecurityHelper::sanitizeHtml($unsafeHtml, '<p><strong><em>');

// Helper fonksiyon ile
$safeHtml = theme_security_sanitize($unsafeHtml, '<p><strong><em>');
```

```blade
{{-- Blade'de --}}
{!! theme_security_sanitize($htmlContent) !!}
```

### Path Validation

View path'lerini doğrulama otomatik olarak yapılır:

```php
// Güvenli path
$view = 'home.index'; // ✅ Geçerli

// Güvensiz path
$view = '../../../etc/passwd'; // ❌ Engellenecek

// SecurityHelper ile manuel kontrol
if (SecurityHelper::isValidViewPath($view)) {
    return view($view);
} else {
    abort(403, 'Invalid view path');
}
```

### Örnek: Güvenli Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Nlk\Theme\Support\SecurityHelper;

class UserController extends Controller
{
    public function show(Request $request, $id)
    {
        // Kullanıcı girdisini güvenli hale getir
        $safeId = SecurityHelper::sanitizeFilename($id);
        
        $user = User::find($safeId);
        
        // XSS korumalı veri gönder
        return Theme::view('user.profile', [
            'name' => SecurityHelper::escape($user->name),
            'email' => SecurityHelper::escape($user->email),
            'bio' => SecurityHelper::sanitizeHtml($user->bio, '<p><br><strong>'),
        ]);
    }
}
```

---

## Livewire Desteği

### Kurulum

Livewire paketini yükleyin:

```bash
composer require livewire/livewire
```

Config'de etkinleştirin:

```php
// config/theme.php
'frontend' => [
    'livewire' => [
        'enabled' => true,
        'auto_detect' => true,
    ],
],
```

### Kullanım

#### Controller'da Livewire Component Render

```php
<?php

namespace App\Http\Controllers;

use Nlk\Theme\Support\LivewireSupport;

class HomeController extends Controller
{
    public function index()
    {
        // Livewire component'ini render et
        $livewireHtml = LivewireSupport::renderComponent(
            'counter-component',
            ['initialCount' => 10]
        );
        
        return Theme::view('home', [
            'livewireComponent' => $livewireHtml
        ]);
    }
}
```

#### Helper Fonksiyon ile

```php
// Controller'da
return Theme::view('home', [
    'counter' => theme_livewire('counter-component', ['count' => 5])
]);
```

```blade
{{-- Blade template'inde --}}
<div>
    {!! theme_livewire('counter-component', ['initialCount' => 10]) !!}
</div>
```

#### Livewire Request Kontrolü

```php
use Nlk\Theme\Support\LivewireSupport;

if (LivewireSupport::isLivewireRequest()) {
    // Livewire isteği için özel işlem
    return response()->json(['status' => 'ok']);
}
```

### Örnek: Tema Layout ile Livewire

```blade
{{-- themes/default/layouts/main.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'My App' }}</title>
    @livewireStyles
</head>
<body>
    <header>
        @partial('header')
    </header>
    
    <main>
        @content
    </main>
    
    <footer>
        @partial('footer')
    </footer>
    
    @livewireScripts
</body>
</html>
```

```php
// Controller
public function index()
{
    return Theme::uses('default')
        ->layout('main')
        ->view('home');
}
```

---

## React + SSR Desteği

### Kurulum

Vite ve React kurulumu:

```bash
npm install react react-dom
npm install --save-dev @vitejs/plugin-react
```

Vite config:

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
});
```

Theme config:

```php
// config/theme.php
'frontend' => [
    'react' => [
        'enabled' => true,
        'ssr_enabled' => false, // SSR için Node.js server gerekli
        'vite_entry' => 'resources/js/app.jsx',
    ],
],
```

### Kullanım

#### React Component Render

```php
<?php

namespace App\Http\Controllers;

use Nlk\Theme\Support\ReactSSRSupport;

class HomeController extends Controller
{
    public function index()
    {
        // React component render
        $reactHtml = ReactSSRSupport::renderComponent(
            'components/UserProfile',
            [
                'user' => auth()->user(),
                'posts' => Post::latest()->take(5)->get(),
            ]
        );
        
        return Theme::view('home', [
            'reactComponent' => $reactHtml
        ]);
    }
}
```

#### Helper Fonksiyon ile

```php
return Theme::view('home', [
    'profile' => theme_react('components/UserProfile', [
        'user' => auth()->user()
    ])
]);
```

```blade
{{-- Blade template'inde --}}
<div>
    {!! theme_react('components/UserProfile', ['user' => $user]) !!}
</div>
```

#### Vite Assets Ekleme

```blade
{{-- themes/default/layouts/main.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'My App' }}</title>
    
    {{-- Vite React assets --}}
    {!! \Nlk\Theme\Support\ReactSSRSupport::viteAssets() !!}
</head>
<body>
    <div id="app">
        @content
    </div>
</body>
</html>
```

#### React Component Örneği

```jsx
// resources/js/components/UserProfile.jsx
import React from 'react';

export default function UserProfile({ user, posts }) {
    return (
        <div className="user-profile">
            <h2>{user.name}</h2>
            <p>{user.email}</p>
            <div className="posts">
                {posts.map(post => (
                    <div key={post.id}>
                        <h3>{post.title}</h3>
                        <p>{post.excerpt}</p>
                    </div>
                ))}
            </div>
        </div>
    );
}
```

#### SSR ile Kullanım (Node.js Gerekli)

SSR için Node.js server kurulumu gereklidir. Örnek SSR entegrasyonu:

```php
// Controller'da
$ssrHtml = ReactSSRSupport::renderSSR(
    'components/UserProfile',
    ['user' => auth()->user()]
);
```

---

## Inertia.js Desteği

### Kurulum

Inertia.js paketlerini yükleyin:

```bash
composer require inertiajs/inertia-laravel
npm install @inertiajs/react @inertiajs/inertia @inertiajs/inertia-react
```

Config'de etkinleştirin:

```php
// config/theme.php
'frontend' => [
    'inertia' => [
        'enabled' => true,
        'auto_detect' => true,
    ],
],
```

### Kullanım

#### Controller'da Inertia Render

```php
<?php

namespace App\Http\Controllers;

use Nlk\Theme\Support\InertiaSupport;

class HomeController extends Controller
{
    public function index()
    {
        // Inertia page render (theme layout ile)
        return InertiaSupport::render('Home/Index', [
            'posts' => Post::latest()->get(),
            'users' => User::take(5)->get(),
        ]);
    }
}
```

#### Helper Fonksiyon ile

```php
return theme_inertia('Home/Index', [
    'posts' => Post::latest()->get(),
]);
```

#### Global Data Sharing

```php
// AppServiceProvider.php
use Nlk\Theme\Support\InertiaSupport;

public function boot()
{
    // Inertia ile global data paylaş
    InertiaSupport::share('auth', [
        'user' => auth()->user(),
    ]);
    
    InertiaSupport::share('flash', [
        'message' => session('message'),
    ]);
}
```

#### Inertia Request Kontrolü

```php
use Nlk\Theme\Support\InertiaSupport;

if (InertiaSupport::isInertiaRequest()) {
    // Inertia isteği için özel işlem
    return redirect()->back();
}
```

#### Theme Layout ile Kullanım

```blade
{{-- themes/default/layouts/app.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'My App' }}</title>
    @routes
    @vite(['resources/js/app.jsx', 'resources/css/app.css'])
    @inertiaHead
</head>
<body>
    <div id="app" data-page="{{ json_encode($page) }}"></div>
    
    @inertia
</body>
</html>
```

```php
// Controller
public function index()
{
    // Theme layout ile Inertia
    return InertiaSupport::render('Home/Index', [
        'posts' => Post::latest()->get(),
    ], 'theme.default::layouts.app'); // Özel root view
}
```

### React Component (Inertia)

```jsx
// resources/js/Pages/Home/Index.jsx
import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function Index({ posts, users }) {
    return (
        <div>
            <Head title="Home" />
            
            <h1>Welcome</h1>
            
            <div className="posts">
                {posts.map(post => (
                    <div key={post.id}>
                        <Link href={`/posts/${post.id}`}>
                            <h2>{post.title}</h2>
                        </Link>
                        <p>{post.excerpt}</p>
                    </div>
                ))}
            </div>
        </div>
    );
}
```

---

## Kombine Kullanım Örnekleri

### Livewire + React

```blade
{{-- templates/home.blade.php --}}
<div>
    {{-- Livewire component --}}
    {!! theme_livewire('counter-component') !!}
    
    {{-- React component --}}
    {!! theme_react('components/Chart', ['data' => $chartData]) !!}
</div>
```

### Inertia + React Components

```jsx
// Inertia page içinde React component kullanımı
import React from 'react';
import UserProfile from '@/components/UserProfile';

export default function Dashboard({ user, stats }) {
    return (
        <div>
            <UserProfile user={user} />
            <StatsChart data={stats} />
        </div>
    );
}
```

### Güvenlik ile Birlikte

```php
// Controller
public function dashboard()
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
    
    // Inertia ile render
    return theme_inertia('Dashboard', $safeData);
}
```

---

## Environment Variables

`.env` dosyanıza ekleyebileceğiniz değişkenler:

```env
# Güvenlik
THEME_SECURITY_HEADERS=true
THEME_CSP_ENABLED=false
THEME_VALIDATE_VIEW_PATHS=true

# Frontend Framework Support
THEME_LIVEWIRE_ENABLED=false
THEME_REACT_ENABLED=false
THEME_REACT_SSR_ENABLED=false
THEME_INERTIA_ENABLED=false
```

---

## Sorun Giderme

### Livewire Component Bulunamıyor

```php
// Component'in tam namespace'ini kullanın
theme_livewire('App\\Http\\Livewire\\CounterComponent');
```

### React Component Render Olmuyor

1. Vite'ın çalıştığından emin olun: `npm run dev`
2. Vite config'in doğru olduğunu kontrol edin
3. Browser console'da hata mesajlarını kontrol edin

### Inertia Sayfası Render Olmuyor

1. Root view'in (`app.blade.php`) doğru olduğundan emin olun
2. Inertia middleware'inin eklendiğinden emin olun
3. `@inertia` directive'inin layout'ta olduğundan emin olun

---

More examples and detailed documentation will be added soon.

