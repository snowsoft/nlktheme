<?php

namespace Nlk\Theme\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CriticalCssExtractor — Above-the-fold CSS inline yerleştirme.
 *
 * Build zamanında veya runtime'da kritik CSS'i ayıklar
 * ve <head> içine inline olarak ekler.
 *
 * Blade: @critical_css('home')
 */
class CriticalCssExtractor
{
    protected int   $cacheTtl;
    protected string $storePath;

    public function __construct()
    {
        $this->cacheTtl  = (int) config('theme.performance.critical_css_ttl', 86400);
        $this->storePath = (string) config('theme.performance.critical_css_path',
            storage_path('app/theme/critical-css'));
    }

    /**
     * Sayfa için kritik CSS'i döndürür.
     * Önce dosya sisteminden, sonra cache'den bakar.
     *
     * @param  string  $pageKey  'home' | 'category' | 'product' | 'cart' | vb.
     */
    public function get(string $pageKey): string
    {
        $cacheKey = 'theme:critical_css:' . $pageKey;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($pageKey) {
            $file = $this->storePath . DIRECTORY_SEPARATOR . $pageKey . '.css';

            if (file_exists($file)) {
                return file_get_contents($file) ?: '';
            }

            return '';
        });
    }

    /**
     * Kritik CSS'i <style> etiketi olarak döndürür.
     */
    public function renderTag(string $pageKey): string
    {
        $css = $this->get($pageKey);

        if (empty($css)) return '';

        return '<style id="nlk-critical-' . htmlspecialchars($pageKey, ENT_QUOTES) . '">'
            . $css . '</style>';
    }

    /**
     * Kritik CSS dosyasını disk'e yaz.
     *
     * @param  string  $pageKey
     * @param  string  $css      Sıkıştırılmış CSS içeriği
     */
    public function store(string $pageKey, string $css): bool
    {
        try {
            if (!is_dir($this->storePath)) {
                mkdir($this->storePath, 0755, true);
            }

            $file = $this->storePath . DIRECTORY_SEPARATOR . $pageKey . '.css';
            file_put_contents($file, $this->minify($css));

            // Cache'i temizle
            Cache::forget('theme:critical_css:' . $pageKey);

            return true;
        } catch (\Throwable $e) {
            Log::warning('CriticalCssExtractor store error', ['page' => $pageKey, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Tüm kritik CSS cache'ini temizle.
     */
    public function flush(): void
    {
        Cache::forget('theme:critical_css:');
        // Wildcard cache temizleme
        if (is_dir($this->storePath)) {
            foreach (glob($this->storePath . '/*.css') as $file) {
                $page = basename($file, '.css');
                Cache::forget('theme:critical_css:' . $page);
            }
        }
    }

    /**
     * Basit CSS minifikasyonu (yorum + gereksiz boşluk kaldırma).
     */
    protected function minify(string $css): string
    {
        // Yorumları kaldır
        $css = preg_replace('!/\*.*?\*/!s', '', $css);
        // Gereksiz boşlukları kaldır
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace([' {', '{ ', ' }', '} ', ': ', ' :', ' ;', '; '], ['{', '{', '}', '}', ':', ':', ';', ';'], $css);
        return trim($css);
    }
}
