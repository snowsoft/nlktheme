<?php

namespace Nlk\Theme\FlexPage\DataAdapters;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * MySQL Adapter — directly queries tenant database tables.
 * Tenant connection is determined by the active DB connection.
 */
class MysqlAdapter
{
    public function __construct(
        private readonly string $connection = 'mysql',
        private readonly int    $cacheTtl   = 300,
    ) {}

    /** Resolve DB query builder for given connection */
    private function db(string $table)
    {
        return DB::connection($this->connection)->table($table);
    }

    // ─── Sliders ─────────────────────────────────────────────────────────────

    public function fetchSliders(?string $tenantId = null): array
    {
        $cacheKey = "theme:mysql:sliders:{$tenantId}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($tenantId) {
            return $this->db('sliders')
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->where('active', 1)
                ->orderBy('position')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();
        });
    }

    // ─── Banners / Afişler ───────────────────────────────────────────────────

    public function fetchBanners(?string $tenantId = null, ?string $zone = null): array
    {
        $cacheKey = "theme:mysql:banners:{$tenantId}:{$zone}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($tenantId, $zone) {
            return $this->db('afisler')
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->when($zone, fn ($q) => $q->where('yer', $zone))
                ->where('aktif', 1)
                ->orderBy('sira')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();
        });
    }

    // ─── Products ────────────────────────────────────────────────────────────

    /**
     * Fetch featured / filtered products.
     *
     * @param  array{limit?:int, category_id?:int, featured?:bool, tenant_id?:string}  $options
     */
    public function fetchProducts(array $options = []): array
    {
        $tenantId  = $options['tenant_id'] ?? null;
        $limit     = $options['limit'] ?? 12;
        $featured  = $options['featured'] ?? false;
        $catId     = $options['category_id'] ?? null;
        $cacheKey  = "theme:mysql:products:" . md5(serialize($options));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($tenantId, $limit, $featured, $catId) {
            return $this->db('urunler')
                ->select(['id', 'slug', 'baslik', 'fiyat', 'fotograf', 'uruntipi', 'kategori_id'])
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->when($featured, fn ($q) => $q->where('one_cikan', 1))
                ->when($catId, fn ($q) => $q->where('kategori_id', $catId))
                ->where('aktif', 1)
                ->limit($limit)
                ->orderByDesc('id')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();
        });
    }

    // ─── Categories ──────────────────────────────────────────────────────────

    public function fetchCategories(array $options = []): array
    {
        $tenantId = $options['tenant_id'] ?? null;
        $parentId = $options['parent_id'] ?? null;
        $limit    = $options['limit'] ?? 20;
        $cacheKey = "theme:mysql:categories:" . md5(serialize($options));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($tenantId, $parentId, $limit) {
            return $this->db('kategoriler')
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->when(is_null($parentId), fn ($q) => $q->whereNull('parent_id'))
                ->when(!is_null($parentId), fn ($q) => $q->where('parent_id', $parentId))
                ->where('aktif', 1)
                ->orderBy('sira')
                ->limit($limit)
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();
        });
    }

    /** Invalidate cached data for given tenant */
    public function flushTenantCache(string $tenantId): void
    {
        $keys = [
            "theme:mysql:sliders:{$tenantId}",
            "theme:mysql:banners:{$tenantId}:*",
        ];
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
