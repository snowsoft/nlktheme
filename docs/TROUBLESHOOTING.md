# Sorun Giderme

---

## Section Render Edilmiyor

### Belirti
`@page_render()` boş çıktı döndürüyor.

### Kontrol Listesi

```bash
# 1. Tablolar var mı?
php artisan migrate:status | grep theme

# 2. Sayfa kaydı var mı?
php artisan tinker
>>> \Nlk\Theme\Database\Models\ThemePageSetting::where('page_key','home')->first()

# 3. Section kayıtları var mı?
>>> \Nlk\Theme\Database\Models\ThemeSectionRow::where('tenant_id', 'default')->get()

# 4. Registry'de tip var mı?
>>> app(\Nlk\Theme\FlexPage\SectionRegistry::class)->has('hero')
```

### Çözüm

```php
// Sayfa verisi yoksa import et
php artisan theme:import src/templates/home.json --tenant=default
```

---

## "Section type [x] not registered" Hatası

Section tipi `SectionRegistry`'ye kayıt edilmemiş.

```php
// AppServiceProvider::boot() içine ekle:
app(\Nlk\Theme\FlexPage\SectionRegistry::class)
    ->register('my-type', MySection::class);
```

---

## View Bulunamıyor

### Belirti

```
InvalidArgumentException: View [theme::sections.hero] not found.
```

### Çözüm

```bash
# Tema klasörünü kontrol et
ls themes/my-store/sections/

# Vendor view path'ini kontrol et
php artisan view:clear
php artisan cache:clear
```

Section view arama sırası:
1. `themes/{active-theme}/sections/{type}.blade.php`
2. Paket içi varsayılan view

---

## API Çağrısı Boş Dönüyor

### Belirti
`ApiAdapter::fetchProducts()` → `[]` döndürüyor.

### Kontrol

```bash
# .env kontrolü
cat .env | grep THEME_API

# Direkt test
php artisan tinker
>>> app(\Nlk\Theme\FlexPage\DataAdapters\ApiAdapter::class)
...   ->withTenant('default')
...   ->get('v1/products', ['limit' => 1])
```

API URL ulaşılabilir mi?

```bash
curl -H "Authorization: Bearer YOUR_KEY" https://api.example.com/v1/products?limit=1
```

### Log Kontrolü

```bash
tail -f storage/logs/laravel.log | grep "ThemeEngine"
```

---

## Cache Sorunu — Eski Veri Gösteriyor

```bash
# Tüm cache temizle
php artisan cache:clear

# Belirli sayfa cache'ini temizle
php artisan tinker
>>> app(\Nlk\Theme\PageBuilder\PageBuilder::class)->flushCache('home', 'default')
```

---

## Tracking Scriptleri Çıkmıyor

### Kontrol Listesi

1. `.env`'de `THEME_TRACKING_ENABLED=true` mi?
2. GTM ID formatı doğru mu? (`GTM-XXXXX`)
3. FB Pixel ID sadece rakam mı?
4. `@tracking_head` layout'ta `<head>` içinde mi?
5. `@tracking_events` `</body>` öncesinde mi?

```php
// Tinker ile kontrol:
>>> app('nlk.tracking')->isEnabled()
>>> app('nlk.tracking')->hasGtm()
>>> app('nlk.tracking')->hasFbPixel()
```

---

## JSON-LD / Rich Snippet Çıkmıyor

```php
// SeoManager'ın render() çıktısını kontrol et:
php artisan tinker
>>> $seo = app('nlk.seo');
>>> $seo->addProductSchema(['name' => 'Test', 'offers' => ['price' => 99, 'currency' => 'TRY']]);
>>> echo $seo->render();
```

Schema doğruluğunu test etmek için:  
→ [Google Rich Results Test](https://search.google.com/test/rich-results)

---

## Migration Hataları

### "Table already exists"

```bash
php artisan migrate:rollback --step=2
php artisan migrate
```

### Migration sırası sorunu

```bash
php artisan migrate:status
# 2026_03_24_000001 önce, 000002 sonra çalışmalı
```

---

## Geliştirici Araçları

```bash
# Kayıtlı section tiplerini listele
php artisan tinker
>>> app(\Nlk\Theme\FlexPage\SectionRegistry::class)->all()

# Aktif temayı kontrol et
>>> config('theme.themeDefault')

# PageBuilder cache durumu
>>> app(\Nlk\Theme\PageBuilder\PageBuilder::class)->loadPage('home', 'default')

# Tüm section şemalarını al (admin editör için)
>>> app(\Nlk\Theme\PageBuilder\PageBuilder::class)->getSectionSchemas()
```

---

## Performans İpuçları

| Sorun | Çözüm |
|---|---|
| Yavaş sayfa yükleme | `THEME_BUILDER_TTL` artır (600+), Redis kullan |
| Çok fazla DB sorgusu | `MysqlAdapter` cache'ini kontrol et, `THEME_BUILDER_TTL` artır |
| API timeout | `THEME_API_TIMEOUT` düşür, API hızına bak |
| View compile yavaş | Production'da `php artisan view:cache` çalıştır |
| Cache hit rate düşük | Redis kullan (`CACHE_DRIVER=redis`) |
