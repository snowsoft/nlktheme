# Artisan Komutları Dokümantasyonu

NLK Theme paketi için kullanılabilir tüm Artisan komutları ve kullanımları.

## İçindekiler

1. [Tema Oluşturma](#tema-oluşturma)
2. [Tema Listeleme](#tema-listeleme)
3. [Tema Kopyalama](#tema-kopyalama)
4. [Tema Silme](#tema-silme)
5. [Widget Oluşturma](#widget-oluşturma)

---

## Tema Oluşturma

### Komut

```bash
php artisan theme:create <theme-name>
```

### Açıklama

Yeni bir tema yapısı oluşturur. Tema adı küçük harfle yazılmalıdır.

### Kullanım

```bash
# Basit tema oluşturma
php artisan theme:create mytheme

# Özel path ile tema oluşturma
php artisan theme:create mytheme --path=/custom/path/to/themes

# Facade adı belirterek tema oluşturma
php artisan theme:create mytheme --facade=Theme
```

### Oluşturulan Yapı

Komut çalıştırıldığında aşağıdaki klasör yapısı oluşturulur:

```
themes/
└── mytheme/
    ├── assets/
    │   ├── css/
    │   │   └── style.css
    │   ├── js/
    │   │   └── script.js
    │   └── img/
    ├── layouts/
    │   └── layout.blade.php
    ├── partials/
    │   ├── header.blade.php
    │   ├── footer.blade.php
    │   └── sections/
    │       └── main.blade.php
    ├── views/
    │   └── index.blade.php
    ├── widgets/
    ├── config.php
    ├── theme.json
    └── gulpfile.js
```

### Seçenekler

- `--path`: Tema dizininin yolu (varsayılan: `base_path('themes')`)
- `--facade`: Facade adı (varsayılan: `Theme`)

### Örnekler

```bash
# E-ticaret teması oluştur
php artisan theme:create ecommerce

# Kurumsal site teması oluştur
php artisan theme:create corporate

# Admin panel teması oluştur
php artisan theme:create admin --path=/app/themes
```

---

## Tema Listeleme

### Komut

```bash
php artisan theme:list
```

### Açıklama

Mevcut tüm temaları listeler.

### Kullanım

```bash
# Tüm temaları listele
php artisan theme:list

# Özel path'ten temaları listele
php artisan theme:list --path=/custom/path/to/themes
```

### Çıktı Örneği

```
+---+------------+
| # | Theme name |
+---+------------+
| 1 | default    |
| 2 | mytheme    |
| 3 | ecommerce  |
+---+------------+
```

### Seçenekler

- `--path`: Tema dizininin yolu (varsayılan: `base_path('themes')`)

---

## Tema Kopyalama

### Komut

```bash
php artisan theme:duplicate <source-theme> <new-theme-name>
```

### Açıklama

Mevcut bir temayı kopyalayarak yeni bir tema oluşturur. Tüm dosyalar ve yapı kopyalanır.

### Kullanım

```bash
# Temayı kopyala
php artisan theme:duplicate default newtheme

# Özel path ile kopyala
php artisan theme:duplicate default newtheme --path=/custom/path
```

### Örnekler

```bash
# Default temayı kopyala ve admin teması oluştur
php artisan theme:duplicate default admin

# E-ticaret temasını kopyala ve yeni versiyonu oluştur
php artisan theme:duplicate ecommerce ecommerce-v2
```

### Seçenekler

- `--path`: Tema dizininin yolu (varsayılan: `base_path('themes')`)

### Notlar

- Kaynak tema mevcut olmalıdır
- Yeni tema adı zaten kullanılıyorsa hata verecektir
- Tüm dosyalar, klasörler ve konfigürasyonlar kopyalanır

---

## Tema Silme

### Komut

```bash
php artisan theme:destroy <theme-name>
```

### Açıklama

Bir temayı kalıcı olarak siler. **Dikkat: Bu işlem geri alınamaz!**

### Kullanım

```bash
# Temayı sil
php artisan theme:destroy oldtheme

# Özel path'ten temayı sil
php artisan theme:destroy oldtheme --path=/custom/path
```

### Örnekler

```bash
# Eski temayı sil
php artisan theme:destroy oldtheme

# Test temasını sil
php artisan theme:destroy test-theme
```

### Seçenekler

- `--path`: Tema dizininin yolu (varsayılan: `base_path('themes')`)

### Güvenlik

Komut çalıştırıldığında onay istenir:

```
Are you sure you want to permanently delete? (yes/no) [no]:
```

Güvenlik için `yes` yazmanız gerekir.

---

## Widget Oluşturma

### Komut

```bash
php artisan theme:widget <widget-name>
```

### Açıklama

Yeni bir widget sınıfı ve görünüm dosyası oluşturur.

### Kullanım

```bash
# Widget oluştur
php artisan theme:widget ProductSlider

# Özel path ile widget oluştur
php artisan theme:widget ProductSlider --path=/custom/path
```

### Oluşturulan Dosyalar

Widget oluşturulduğunda:

1. **Widget Sınıfı**: `app/Widgets/ProductSlider.php`
2. **Widget Görünümü**: Temanızın `widgets/` klasöründe `productslider.blade.php`

### Örnekler

```bash
# Ürün slider widget'ı
php artisan theme:widget ProductSlider

# Kategori menü widget'ı
php artisan theme:widget CategoryMenu

# Banner widget'ı
php artisan theme:widget Banner
```

---

## Kullanım Senaryoları

### Senaryo 1: Yeni Proje Başlangıcı

```bash
# 1. Default tema oluştur
php artisan theme:create default

# 2. E-ticaret teması oluştur
php artisan theme:create ecommerce

# 3. Admin teması oluştur
php artisan theme:create admin

# 4. Temaları kontrol et
php artisan theme:list
```

### Senaryo 2: Tema Geliştirme ve Test

```bash
# 1. Mevcut temayı kopyala (test için)
php artisan theme:duplicate default default-test

# 2. Test temasında değişiklik yap
# ...

# 3. Test başarılı ise test temasını sil
php artisan theme:destroy default-test
```

### Senaryo 3: Widget Geliştirme

```bash
# 1. Widget oluştur
php artisan theme:widget HeroSlider

# 2. Widget kodunu düzenle
# app/Widgets/HeroSlider.php

# 3. Widget görünümünü düzenle
# themes/your-theme/widgets/heroslider.blade.php
```

---

## Konfigürasyon

Tema dizini konfigürasyonu `config/theme.php` dosyasında ayarlanabilir:

```php
'themeDir' => env('APP_THEME_DIR', 'themes'),
```

Environment değişkeni ile de ayarlanabilir:

```env
APP_THEME_DIR=themes
```

---

## Sorun Giderme

### Tema Bulunamıyor

```bash
# Tema dizininin var olduğundan emin olun
ls -la themes/

# Path'i kontrol edin
php artisan theme:list --path=/full/path/to/themes
```

### İzin Hatası

```bash
# Klasör izinlerini kontrol edin
chmod -R 755 themes/

# Storage klasörü için
chmod -R 775 storage/
```

### Tema Zaten Mevcut

```bash
# Önce mevcut temayı silin veya farklı isim kullanın
php artisan theme:destroy oldname
php artisan theme:create newname
```

---

## Best Practices

1. **Tema İsimlendirme**: Küçük harf, tire veya alt çizgi kullanın
   - ✅ `ecommerce`, `corporate-site`, `admin_panel`
   - ❌ `Ecommerce`, `Corporate Site`, `Admin Panel`

2. **Tema Kopyalama**: Geliştirme için her zaman kopya kullanın
   ```bash
   php artisan theme:duplicate production production-backup
   ```

3. **Widget İsimlendirme**: PascalCase kullanın
   - ✅ `ProductSlider`, `CategoryMenu`, `BannerWidget`
   - ❌ `product_slider`, `category-menu`

4. **Version Control**: Tema dosyalarını Git'e ekleyin
   ```bash
   git add themes/
   git commit -m "Add theme files"
   ```

---

Detaylı bilgi için [Ana Dokümantasyon](README.md) dosyasına bakın.

