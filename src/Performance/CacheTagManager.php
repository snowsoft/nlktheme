<?php

namespace Nlk\Theme\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CacheTagManager — Redis tag tabanlı granüler cache invalidation.
 *
 * Laravel Cache::tags() üzerine. Fallback olarak tags
 * desteklemeyen sürücülerde prefix tabanlı çalışır.
 *
 * Kullanım:
 *   CacheTagManager::forPage('home', 'tenant_1')->remember('data', fn() => [...]);
 *   CacheTagManager::invalidatePage('home', 'tenant_1');
 *   CacheTagManager::invalidateTenant('tenant_1');
 */
class CacheTagManager
{
    protected bool $tagsSupported;

    public function __construct()
    {
        $driver = config('cache.default', 'file');
        // Redis ve Memcached tag destekler
        $this->tagsSupported = in_array($driver, ['redis', 'memcached'], true);
    }

    // ─── Tag Oluşturma ────────────────────────────────────────────────────

    /**
     * Sayfa + tenant için tag listesi döndürür.
     */
    public function tagsFor(string $type, string ...$parts): array
    {
        $tags = ['theme'];
        foreach ($parts as $part) {
            if ($part !== '') $tags[] = $type . ':' . $part;
        }
        return $tags;
    }

    // ─── Cache İşlemleri ──────────────────────────────────────────────────

    /**
     * Tag'lı cache store.
     *
     * @param  string    $key
     * @param  int       $ttl      Saniye
     * @param  callable  $callback
     * @param  array     $tags
     */
    public function remember(string $key, int $ttl, callable $callback, array $tags = []): mixed
    {
        try {
            if ($this->tagsSupported && !empty($tags)) {
                return Cache::tags($tags)->remember($key, $ttl, $callback);
            }
        } catch (\Throwable $e) {
            Log::debug('CacheTagManager: tags not supported, fallback', ['error' => $e->getMessage()]);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Bir sayfa + tenant için tüm cache'i temizle.
     */
    public function invalidatePage(string $pageKey, string $tenantId = ''): void
    {
        $tags = $this->tagsFor('page', $pageKey, $tenantId);
        $this->flushTags($tags);

        // Fallback: prefix bazlı
        Cache::forget('theme:page:' . $pageKey . ':' . $tenantId);
    }

    /**
     * Bir tenant için tüm theme cache'ini temizle.
     */
    public function invalidateTenant(string $tenantId): void
    {
        $tags = $this->tagsFor('tenant', $tenantId);
        $this->flushTags($tags);

        Cache::forget('theme_tenant_' . $tenantId);
    }

    /**
     * Belirli bir section tipinin cache'ini temizle.
     */
    public function invalidateSection(string $sectionType, string $tenantId = ''): void
    {
        $tags = $this->tagsFor('section', $sectionType, $tenantId);
        $this->flushTags($tags);
    }

    /**
     * Tüm theme cache'ini temizle.
     */
    public function flushAll(): void
    {
        $this->flushTags(['theme']);
    }

    // ─── Static Factory ───────────────────────────────────────────────────

    /**
     * Sayfa + tenant için tag tabanlı cache store.
     *
     * @return \Illuminate\Cache\TaggedCache
     */
    public static function forPage(string $pageKey, string $tenantId = '')
    {
        try {
            return Cache::tags(['theme', 'page:' . $pageKey, 'tenant:' . $tenantId]);
        } catch (\Throwable $e) {
            return Cache::driver();
        }
    }

    // ─── Internal ─────────────────────────────────────────────────────────

    protected function flushTags(array $tags): void
    {
        try {
            if ($this->tagsSupported) {
                Cache::tags($tags)->flush();
            }
        } catch (\Throwable $e) {
            Log::debug('CacheTagManager flush error', ['tags' => $tags, 'error' => $e->getMessage()]);
        }
    }
}
