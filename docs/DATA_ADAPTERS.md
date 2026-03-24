# Hibrit Veri Katmanı

Tema motoru, verileri iki kaynaktan alır: doğrudan MySQL (tenant DB) ve harici REST API. Bu iki kaynağı `HybridAdapter` birleştirir.

---

## MysqlAdapter

Tenant'ın MySQL veritabanına doğrudan bağlanır. Tüm sorgular Redis cache'li (TTL: `THEME_BUILDER_TTL`).

### Kullanım

```php
use Nlk\Theme\FlexPage\DataAdapters\MysqlAdapter;

$mysql = app(MysqlAdapter::class);
```

### Metodlar

#### `fetchSliders(?string $tenantId)`

```php
$sliders = $mysql->fetchSliders('tenant_123');

// sliders tablosundan çeker:
// WHERE tenant_id = ? AND active = 1 ORDER BY position
```

**Beklenen tablo:** `sliders`  
**Kolonlar:** `id`, `tenant_id`, `active`, `position`, `fotograf`, `baslik`, `link`

---

#### `fetchBanners(?string $tenantId, ?string $zone)`

```php
$banners = $mysql->fetchBanners('tenant_123', 'ana');

// afisler tablosundan çeker:
// WHERE tenant_id = ? AND yer = ? AND aktif = 1 ORDER BY sira
```

**Beklenen tablo:** `afisler`  
**Kolonlar:** `id`, `tenant_id`, `aktif`, `sira`, `yer`, `fotograf`, `link`

Zone değerleri: `ana`, `kategori`, `alt`

---

#### `fetchProducts(array $options)`

```php
$products = $mysql->fetchProducts([
    'tenant_id'  => 'tenant_123',
    'featured'   => true,   // one_cikan = 1
    'limit'      => 12,
    'category_id'=> 5,
]);
```

**Beklenen tablo:** `urunler`  
**Select alanları:** `id`, `slug`, `baslik`, `fiyat`, `fotograf`, `uruntipi`, `kategori_id`  
**Filtreler:** `tenant_id`, `one_cikan`, `kategori_id`, `aktif = 1`

---

#### `fetchCategories(array $options)`

```php
$categories = $mysql->fetchCategories([
    'tenant_id' => 'tenant_123',
    'parent_id' => null,   // Ana kategoriler
    'limit'     => 12,
]);
```

**Beklenen tablo:** `kategoriler`  
**Filtreler:** `tenant_id`, `parent_id`, `aktif = 1` ORDER BY `sira`

---

#### `flushTenantCache(string $tenantId)`

```php
$mysql->flushTenantCache('tenant_123');
// Tenant'a ait slider ve banner cache'lerini temizler
```

---

## ApiAdapter

Harici REST API'yi çağırır. `config('theme.data_sources.api')` ile yapılandırılır.

### Yapılandırma

```env
THEME_API_URL=https://api.example.com
THEME_API_KEY=your_secret_key
THEME_API_TIMEOUT=5
```

### Kullanım

```php
use Nlk\Theme\FlexPage\DataAdapters\ApiAdapter;

$api = app(ApiAdapter::class)->withTenant('tenant_123');
```

`withTenant()` değişmez (immutable) bir kopya döner. Her istekte `X-Tenant-ID` ve `Authorization: Bearer` header'ları otomatik eklenir.

### Metodlar

#### `get(string $endpoint, array $params)`

```php
$data = $api->get('v1/campaigns', ['active' => 1, 'limit' => 5]);

// GET https://api.example.com/v1/campaigns?active=1&limit=5
// Headers: X-Tenant-ID, Authorization: Bearer ...
// Başarısız veya timeout → [] döner, log'a yazılır
```

#### `fetchProducts(array $params)`

```php
$products = $api->fetchProducts(['featured' => 1, 'per_page' => 8]);
// GET v1/products?featured=1&per_page=8
```

#### `fetchCategories(array $params)`

```php
$categories = $api->fetchCategories(['depth' => 1]);
// GET v1/categories?depth=1
```

#### `fetchProduct(int|string $id)`

```php
$product = $api->fetchProduct(42);
// GET v1/products/42
```

### Hata Yönetimi

```
HTTP 2xx  → response body döner (data anahtarı varsa data, yoksa body)
HTTP 4xx/5xx → [] döner + Log::warning()
Timeout/Exception → [] döner + Log::warning()
```

Kötü bir API cevabı hiçbir zaman exception fırlatıp sayfayı bozmaz.

### Cache

```
cache key: "theme:api:{tenantId}:{hash(endpoint+params)}"
TTL: THEME_BUILDER_TTL (default 300 saniye)
```

---

## HybridAdapter

MySQL ve API'yi birleştirir.

```php
use Nlk\Theme\FlexPage\DataAdapters\HybridAdapter;

$hybrid = app(HybridAdapter::class);
```

### Metodlar

#### `mysql()` / `api()`

```php
$hybrid->mysql()->fetchSliders($tenantId);
$hybrid->api()->withTenant($tenantId)->fetchProducts();
```

#### `resolve(string $source)`

```php
$adapter = $hybrid->resolve('mysql'); // MysqlAdapter
$adapter = $hybrid->resolve('api');   // ApiAdapter
```

`featured-products` section'ında settings bazlı dinamik seçim:

```php
$adapter = $hybrid->resolve($settings['source'] ?? 'mysql');
```

#### `mergeProducts(array $mysqlOptions, array $apiParams)`

```php
$products = $hybrid->mergeProducts(
    mysqlOptions: ['tenant_id' => $tenantId, 'limit' => 6],
    apiParams:    ['featured' => 1, 'per_page' => 6]
);
// İkisini birleştirir, id bazlı duplicate temizler
```

---

## Section İçinde Kullanım

`AbstractSection`'dan miras alınır:

```php
class MySection extends AbstractSection
{
    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        // MySQL
        $local = $this->mysql()->fetchProducts([
            'tenant_id' => $tenantId,
            'featured'  => true,
            'limit'     => 4,
        ]);

        // API
        $remote = $this->api()
            ->withTenant($tenantId ?? '')
            ->fetchProducts(['featured' => 1, 'per_page' => 4]);

        // Hybrid merge
        $all = $this->hybrid()->mergeProducts(
            mysqlOptions: ['tenant_id' => $tenantId],
            apiParams:    ['per_page' => 8]
        );

        return ['products' => $local, 'featured' => $all];
    }
}
```

---

## Tablo Adlarını Özelleştirme

Uygulamadaki tablo adları farklıysa `MysqlAdapter`'ı genişletin:

```php
class MyMysqlAdapter extends \Nlk\Theme\FlexPage\DataAdapters\MysqlAdapter
{
    public function fetchSliders(?string $tenantId = null): array
    {
        // 'sliders' yerine 'store_banners' tablosu
        return \DB::table('store_banners')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($r) => (array)$r)
            ->all();
    }
}
```

```php
// AppServiceProvider::register()
$this->app->singleton(\Nlk\Theme\FlexPage\DataAdapters\MysqlAdapter::class,
    fn() => new MyMysqlAdapter());
```
