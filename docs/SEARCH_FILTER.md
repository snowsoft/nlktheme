# Arama & Filtre (Faz 5)

`SearchSection` ve `FilterSidebarSection`, kategori listeleme ve global arama için AJAX tabanlı kullanıcı deneyimi sağlar.

---

## SearchSection (`ajax-search`)

### Schema Ayarları

| ID | Tip | Varsayılan | Açıklama |
|---|---|---|---|
| `placeholder` | text | `Ürün ara...` | Input placeholder |
| `api_url` | text | `/api/v1/search` | Arama endpoint |
| `min_chars` | range | `2` | Arama için minimum karakter |
| `debounce_ms` | range | `300` | Debounce süresi (ms) |
| `max_results` | range | `8` | Maksimum sonuç sayısı |
| `show_products` | checkbox | `true` | Ürün sonuçları |
| `show_categories` | checkbox | `true` | Kategori sonuçları |
| `show_blog` | checkbox | `false` | Blog sonuçları |
| `show_thumbnail` | checkbox | `true` | Ürün görseli |
| `show_price` | checkbox | `true` | Fiyat göster |
| `results_page_url` | text | `/arama` | Tüm sonuçlar URL |

### API Format

```
GET /api/v1/search?q={query}&per_page=8&with_products=1&with_categories=1&tenant_id=xxx

Response:
{
  "products": [
    { "id": "1", "baslik": "iPhone 15", "fiyat": 59999, "cdn_image_id": "img-1", "url": "/urun/iphone-15" }
  ],
  "categories": [
    { "id": "2", "baslik": "Telefonlar", "url": "/kategori/telefonlar" }
  ],
  "blog": [...]
}
```

### FlexPage JSON Örneği

```json
{
  "type": "ajax-search",
  "settings": {
    "placeholder": "Ürün, kategori veya marka ara...",
    "api_url": "/api/v1/search",
    "min_chars": 2,
    "debounce_ms": 300,
    "max_results": 8,
    "show_products": true,
    "show_categories": true,
    "show_blog": false,
    "show_thumbnail": true,
    "show_price": true,
    "results_page_url": "/arama"
  }
}
```

### Blade Örneği (view şablonu)

```blade
{{-- resources/views/theme/sections/ajax-search.blade.php --}}
<div class="nlk-search" data-search-api="{{ $api_url }}"
     data-min="{{ $min_chars }}" data-debounce="{{ $debounce_ms }}"
     data-max="{{ $max_results }}" data-tenant="{{ $tenant_id }}">
  <input type="search" class="nlk-search__input" placeholder="{{ $placeholder }}">
  <div class="nlk-search__results" hidden></div>
</div>
<script>
/* Minimal AJAX arama implementasyonu */
(function(){
  var el    = document.querySelector('.nlk-search');
  if (!el) return;
  var input   = el.querySelector('.nlk-search__input');
  var results = el.querySelector('.nlk-search__results');
  var api     = el.dataset.searchApi;
  var delay   = parseInt(el.dataset.debounce) || 300;
  var min     = parseInt(el.dataset.min) || 2;
  var timer;

  input.addEventListener('input', function(){
    clearTimeout(timer);
    var q = this.value.trim();
    if (q.length < min) { results.hidden = true; return; }
    timer = setTimeout(function(){
      fetch(api + '?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
          results.innerHTML = renderResults(data);
          results.hidden = false;
        });
    }, delay);
  });

  function renderResults(data) {
    var html = '';
    (data.products||[]).forEach(function(p){
      html += '<a class="nlk-search__item" href="'+p.url+'">'+p.baslik+'</a>';
    });
    return html || '<p class="nlk-search__empty">Sonuç bulunamadı</p>';
  }
})();
</script>
```

---

## FilterSidebarSection (`filter-sidebar`)

### Schema Ayarları

| ID | Tip | Varsayılan | Açıklama |
|---|---|---|---|
| `show_price_filter` | checkbox | `true` | Fiyat aralığı slider |
| `show_brand_filter` | checkbox | `true` | Marka filtresi |
| `show_color_filter` | checkbox | `true` | Renk filtresi |
| `show_size_filter` | checkbox | `true` | Beden filtresi |
| `show_rating_filter` | checkbox | `false` | Puan filtresi |
| `show_stock_filter` | checkbox | `false` | Stokta var filtresi |
| `filter_api_url` | text | `/api/v1/filters` | Filtre meta endpoint |
| `products_api_url` | text | `/api/v1/products` | Ürün listesi endpoint |
| `price_display` | select | `range` | Slider veya iki input |
| `mobile_position` | select | `drawer` | Mobilde drawer/üstte |
| `source` | select | `mysql` | `mysql` veya `api` |

### MySQL Veri Kaynağı

Aşağıdaki tablo yapısını beklenir:

```sql
-- Ürünler tablosu
SELECT DISTINCT marka FROM urunler WHERE tenant_id = ? AND aktif = 1;
SELECT MIN(fiyat), MAX(fiyat) FROM urunler WHERE tenant_id = ? AND aktif = 1;

-- Özellikler tablosu
SELECT DISTINCT deger FROM urun_ozellikleri
  WHERE tenant_id = ? AND ozellik_adi = 'renk';
SELECT DISTINCT deger FROM urun_ozellikleri
  WHERE tenant_id = ? AND ozellik_adi = 'beden';
```

### FlexPage JSON Örneği

```json
{
  "type": "filter-sidebar",
  "settings": {
    "show_price_filter": true,
    "show_brand_filter": true,
    "show_color_filter": true,
    "show_size_filter": true,
    "show_rating_filter": false,
    "filter_api_url": "/api/v1/filters",
    "products_api_url": "/api/v1/products",
    "price_display": "range",
    "mobile_position": "drawer",
    "source": "mysql"
  }
}
```

### Sayfa Entegrasyonu

Filtre sidebar normalde kategori sayfalarında kullanılır:

```blade
{{-- category.blade.php --}}
<div class="category-layout">
  @section_render('filter-sidebar', $tenantId)

  <div class="product-grid" id="nlk-products">
    {{-- AJAX ile güncellenir --}}
  </div>
</div>

<script>
/* Filtre değiştiğinde ürünleri yeniden yükle */
document.addEventListener('nlk:filter:change', function(e) {
  var params = new URLSearchParams(e.detail.filters);
  fetch('/api/v1/products?' + params)
    .then(r => r.json())
    .then(data => {
      document.getElementById('nlk-products').innerHTML = renderProducts(data);
    });
});
</script>
```
