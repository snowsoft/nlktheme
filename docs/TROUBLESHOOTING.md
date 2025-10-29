# Sorun Giderme Kılavuzu

## View Bulunamama Hataları

### Hata: "View [odeme.odeme] not found"

Bu hata genellikle view dosyasının yanlış konumda olmasından veya namespace'in doğru ayarlanmamış olmasından kaynaklanır.

#### Çözüm 1: Dosya Konumunu Kontrol Edin

View dosyanızın doğru konumda olduğundan emin olun:

**Tema yapısı:**
```
themes/
└── tema2/
    └── views/
        └── odeme/
            └── odeme.blade.php
```

Veya:

```
public/
└── themes/
    └── tema2/
        └── views/
            └── odeme/
                └── odeme.blade.php
```

#### Çözüm 2: Config Kontrolü

`config/theme.php` dosyasını kontrol edin:

```php
'themeDir' => env('APP_THEME_DIR', 'themes'),
```

Eğer temalarınız `public/themes` içindeyse:

```php
'themeDir' => 'public/themes',
```

Ya da `.env` dosyasında:

```env
APP_THEME_DIR=public/themes
```

#### Çözüm 3: Scope Kullanımı

View'ı namespace ile çağırın:

```php
// ❌ Yanlış
Theme::view('odeme.odeme');

// ✅ Doğru
Theme::scope('odeme.odeme');

// Veya tam namespace ile
Theme::view('theme.tema2::odeme/odeme');
```

#### Çözüm 4: Cache Temizleme

View cache'ini temizleyin:

```bash
php artisan view:clear
php artisan cache:clear
```

### Debug İçin

Hangi path'lerin arandığını görmek için:

```php
// Theme debug
$theme = theme('tema2');
$path = $theme->getThemeNamespace('odeme/odeme');
// Output: theme.tema2::odeme/odeme

// View exists kontrolü
if ($theme->view->exists('theme.tema2::odeme/odeme')) {
    echo "View exists!";
} else {
    echo "View not found!";
}
```

---

## Tema Dizin Sorunları

### Hata: "Theme [tema2] not found"

#### Çözüm

1. Tema dizininin var oldu kondan emin olun:
```bash
ls -la themes/tema2/
# veya
ls -la public/themes/tema2/
```

2. Config'de doğru path'i kullanın:
```php
'themeDir' => 'themes', // base_path('themes')
// veya
'themeDir' => 'public/themes', // base_path('public/themes')
```

---

## Namespace Sorunları

### Hata: View namespace çalışmıyor

Namespace'in doğru kayıtlı olduğundan emin olun:

```php
// Theme set edildiğinde namespace otomatik kaydedilir
Theme::uses('tema2');

// Namespace'i kontrol edin
$namespaces = app('view')->getFinder()->getHints();
dd($namespaces['theme.tema2']);
```

---

## View Path Formatları

### Doğru Kullanımlar

```php
// 1. Scope ile (önerilen)
Theme::scope('odeme.odeme');          // odeme.odeme → theme.tema2::odeme/odeme
Theme::scope('odeme/odeme');          // odeme/odeme → theme.tema2::odeme/odeme

// 2. Doğrudan namespace ile
Theme::view('theme.tema2::odeme/odeme');

// 3. Watch ile (önce scope, sonra normal view)
Theme::watch('odeme.odeme');
```

### Yanlış Kullanımlar

```php
// ❌ Namespace olmadan
Theme::view('odeme.odeme'); // Normal Laravel view olarak arar

// ❌ Yanlış namespace formatı
Theme::view('theme::odeme.odeme'); // Tema adı eksik
```

---

## Dosya Yapısı Kontrolü

Temanızın doğru yapıda olduğundan emin olun:

```
themes/
└── tema2/
    ├── views/
    │   ├── home/
    │   │   └── index.blade.php
    │   └── odeme/
    │       └── odeme.blade.php
    ├── layouts/
    │   └── layout.blade.php
    ├── partials/
    │   ├── header.blade.php
    │   └── footer.blade.php
    ├── assets/
    ├── config.php
    └── theme.json
```

---

## Sorun Giderme Checklist

- [ ] Tema dizini mevcut mu?
- [ ] View dosyası `views/` klasörü içinde mi?
- [ ] Dosya uzantısı `.blade.php` mi?
- [ ] Config'de `themeDir` doğru mu?
- [ ] Tema kullanımı doğru mu? (`Theme::uses('tema2')`)
- [ ] Cache temizlendi mi?
- [ ] View path formatı doğru mu? (`scope()` kullanıldı mı?)

---

Daha fazla yardım için issue açabilirsiniz.

