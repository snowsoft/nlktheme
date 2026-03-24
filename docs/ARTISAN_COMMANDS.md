# Artisan Komutları

---

## `theme:create`

Yeni tema oluşturur.

```bash
php artisan theme:create {name}
```

```bash
php artisan theme:create my-store
# themes/my-store/ oluşturulur:
# ├── config.php
# ├── theme.json
# ├── views/
# │   ├── layout.blade.php
# │   └── index.blade.php
# └── assets/
```

---

## `theme:duplicate`

Var olan temayı kopyalar.

```bash
php artisan theme:duplicate {source} {destination}

php artisan theme:duplicate default my-store
```

---

## `theme:list`

Yüklü temaları listeler.

```bash
php artisan theme:list

# Çıktı:
# ┌────────────┬──────────┬──────────────┐
# │ Theme      │ Active   │ Path         │
# ├────────────┼──────────┼──────────────┤
# │ default    │ ✓        │ themes/default│
# │ my-store   │          │ themes/my-store│
# └────────────┴──────────┴──────────────┘
```

---

## `theme:destroy`

Temayı siler (geri alınamaz).

```bash
php artisan theme:destroy {name}

php artisan theme:destroy old-theme
```

---

## `theme:widget`

Yeni widget sınıfı oluşturur.

```bash
php artisan theme:widget {WidgetName}

php artisan theme:widget MiniCart
# app/Widgets/MiniCart.php oluşturulur
```

---

## `theme:export`

Bir sayfanın yapılandırmasını FlexPage JSON formatında dışa aktarır.

```bash
php artisan theme:export {page} [--tenant=] [--out=]
```

### Parametreler

| Parametre | Açıklama | Örnek |
|---|---|---|
| `{page}` | Sayfa anahtarı | `home`, `category` |
| `--tenant=` | Tenant ID (varsayılan: `THEME_DEFAULT_TENANT`) | `--tenant=tenant_1` |
| `--out=` | Dosya yolu (atlanırsa stdout'a yazar) | `--out=home.json` |

### Örnekler

```bash
# Konsola yaz
php artisan theme:export home

# Dosyaya kaydet
php artisan theme:export home --tenant=default --out=exports/home.json

# Farklı tenant
php artisan theme:export category --tenant=shop_42 --out=exports/shop42_category.json
```

### Çıktı Formatı

```json
{
    "page_key": "home",
    "template": "index",
    "settings": {...},
    "sections": {
        "hero_main": { "type": "hero", "settings": {...}, "disabled": false }
    },
    "order": ["hero_main", "featured", ...]
}
```

---

## `theme:import`

JSON dosyasından sayfa yapılandırmasını içe aktarır ve veritabanına kaydeder.

```bash
php artisan theme:import {file} [--tenant=]
```

### Parametreler

| Parametre | Açıklama | Örnek |
|---|---|---|
| `{file}` | JSON dosya yolu | `exports/home.json` |
| `--tenant=` | Tenant ID | `--tenant=tenant_1` |

### Örnekler

```bash
# Varsayılan tenant
php artisan theme:import exports/home.json

# Belirli tenant
php artisan theme:import exports/home.json --tenant=shop_42

# Paket içi örnek tema
php artisan theme:import src/templates/home.json --tenant=default
```

---

## Toplu Import (Seeder)

```php
// database/seeders/ThemeSeeder.php

use Nlk\Theme\Facades\PageBuilder;

class ThemeSeeder extends \Illuminate\Database\Seeder
{
    public function run(): void
    {
        $tenants = ['default', 'shop_1', 'shop_2'];

        foreach ($tenants as $tenantId) {
            $json = file_get_contents(base_path('src/templates/home.json'));
            PageBuilder::importJson($json, $tenantId);
        }
    }
}
```

```bash
php artisan db:seed --class=ThemeSeeder
```
