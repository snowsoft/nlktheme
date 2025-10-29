# View Path Debug Kılavuzu

## "View [odeme.odeme] not found" Hatası İçin Kontrol Listesi

### 1. Config Kontrolü

```php
// config/theme.php kontrolü
'themeDir' => env('APP_THEME_DIR', 'themes'),

// .env kontrolü
APP_THEME_DIR=themes
// veya
APP_THEME_DIR=public/themes
```

### 2. Dosya Konumu Kontrolü

View dosyanızın doğru konumda olduğundan emin olun:

**Eğer `themeDir` = `themes` ise:**
```
base_path/
└── themes/
    └── tema2/
        └── views/
            └── odeme/
                └── odeme.blade.php
```

**Eğer `themeDir` = `public/themes` ise:**
```
base_path/
└── public/
    └── themes/
        └── tema2/
            └── views/
                └── odeme/
                    └── odeme.blade.php
```

### 3. Namespace Kontrolü

```php
use Theme;

// Tema set edin
Theme::uses('tema2');

// Namespace'in kayıtlı olduğunu kontrol edin
$finder = app('view')->getFinder();
$hints = $finder->getHints();

if (isset($hints['theme.tema2'])) {
    echo "Namespace 'theme.tema2' kayıtlı\n";
    echo "Hint paths:\n";
        foreach ($hints['theme.tema2'] as $hint) {
        echo "  - $hint\n";
    }
} else {
    echo "Namespace kayıtlı değil! Theme::uses('tema2') çağrıldı mı?\n";
}
```

### 4. View Path Formatları

```php
// ✅ Doğru kullanımlar
Theme::scope('odeme.odeme');           // odeme.odeme → theme.tema2::odeme/odeme
Theme::scope('odeme/odeme');           // odeme/odeme → theme.tema2::odeme/odeme
Theme::view('theme.tema2::odeme/odeme'); // Direkt namespace

// ❌ Yanlış kullanımlar
Theme::view('odeme.odeme'); // Namespace yok, normal view olarak arar
```

### 5. Manuel Test

```php
// Test route
Route::get('/test-view', function() {
    $theme = Theme::uses('tema2');
    
    // 1. Namespace kontrolü
    $namespace = $theme->getThemeNamespace('odeme/odeme');
    echo "Namespace: $namespace\n";
    
    // 2. View exists kontrolü
    if ($theme->view->exists($namespace)) {
        echo "✓ View exists!\n";
        
        // 3. View path'i bul
        try {
            $finder = $theme->view->getFinder();
            $path = $finder->find($namespace);
            echo "✓ View path: $path\n";
        } catch (\Exception $e) {
            echo "✗ View path bulunamadı: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ View not found!\n";
        
        // Hangi path'lerin arandığını göster
        $hints = $theme->view->getFinder()->getHints();
        if (isset($hints['theme.tema2'])) {
            echo "\nAranan path'ler:\n";
            foreach ($hints['theme.tema2'] as $hint) {
                $path1 = $hint . '/odeme/odeme.blade.php';
                $path2 = $hint . '/views/odeme/odeme.blade.php';
                echo "  - $path1 " . (file_exists($path1) ? '✓' : '✗') . "\n";
                echo "  - $path2 " . (file_exists($path2) ? '✓' : '✗') . "\n";
            }
        }
    }
    
    return 'Check terminal output';
});
```

### 6. Cache Temizleme

```bash
# View cache temizle
php artisan view:clear

# Config cache temizle
php artisan config:clear

# Application cache temizle
php artisan cache:clear

# Tüm cache'leri temizle
php artisan optimize:clear
```

### 7. Dosya İzinleri

```bash
# Klasör izinlerini kontrol edin
chmod -R 755 themes/
# veya
chmod -R 755 public/themes/
```

### 8. Tema Yapısı Kontrolü

```php
// Tema dizininin var olduğunu kontrol edin
$themeDir = config('theme.themeDir');
$themePath = base_path($themeDir . '/tema2');

if (is_dir($themePath)) {
    echo "✓ Tema dizini mevcut: $themePath\n";
    
    $viewsPath = $themePath . '/views';
    if (is_dir($viewsPath)) {
        echo "✓ Views klasörü mevcut: $viewsPath\n";
        
        $targetView = $viewsPath . '/odeme/odeme.blade.php';
        if (file_exists($targetView)) {
            echo "✓ View dosyası mevcut: $targetView\n";
        } else {
            echo "✗ View dosyası bulunamadı: $targetView\n";
            
            // Mevcut view'ları listele
            $files = glob($viewsPath . '/**/*.blade.php');
            echo "\nMevcut view'lar:\n";
            foreach ($files as $file) {
                echo "  - " . str_replace($viewsPath . '/', '', $file) . "\n";
            }
        }
    } else {
        echo "✗ Views klasörü bulunamadı: $viewsPath\n";
    }
} else {
    echo "✗ Tema dizini bulunamadı: $themePath\n";
}
```

---

## Hızlı Çözümler

### Sorun 1: View dosyası views/ klasörü dışında

**Çözüm:** View dosyasını `themes/tema2/views/odeme/odeme.blade.php` konumuna taşıyın.

### Sorun 2: Config'de yanlış path

**Çözüm:** `config/theme.php` veya `.env` dosyasında `themeDir` değerini kontrol edin.

### Sorun 3: Tema namespace kayıtlı değil

**Çözüm:** View render etmeden önce `Theme::uses('tema2')` çağrıldığından emin olun.

### Sorun 4: Cache sorunu

**Çözüm:** Tüm cache'leri temizleyin: `php artisan optimize:clear`

---

## Örnek: Hata Ayıklama Scripti

```php
// routes/web.php veya bir controller'da
Route::get('/debug-view', function() {
    $debug = [];
    
    // 1. Config kontrolü
    $themeDir = config('theme.themeDir');
    $debug['themeDir'] = $themeDir;
    $debug['themePath'] = base_path($themeDir);
    
    // 2. Tema kontrolü
    $themeName = 'tema2';
    $themePath = base_path($themeDir . '/' . $themeName);
    $debug['themeExists'] = is_dir($themePath);
    $debug['themePath'] = $themePath;
    
    // 3. Views klasörü kontrolü
    $viewsPath = $themePath . '/views';
    $debug['viewsExists'] = is_dir($viewsPath);
    $debug['viewsPath'] = $viewsPath;
    
    // 4. View dosyası kontrolü
    $viewFile = $viewsPath . '/odeme/odeme.blade.php';
    $debug['viewFileExists'] = file_exists($viewFile);
    $debug['viewFilePath'] = $viewFile;
    
    // 5. Namespace kontrolü
    Theme::uses($themeName);
    $finder = app('view')->getFinder();
    $hints = $finder->getHints();
    $debug['namespaceRegistered'] = isset($hints['theme.' . $themeName]);
    $debug['namespaceHints'] = $hints['theme.' . $themeName] ?? [];
    
    // 6. View exists kontrolü
    $namespace = 'theme.' . $themeName . '::odeme/odeme';
    $debug['viewExists'] = Theme::view()->view->exists($namespace);
    $debug['namespace'] = $namespace;
    
    return response()->json($debug, JSON_PRETTY_PRINT);
});
```

Bu script'i motorsunuz ve sonucu kontrol edin. Hangi adımda sorun varsa onu çözün.

---

Daha fazla yardım için [Ana Dokümantasyon](README.md) dosyasına bakın.

