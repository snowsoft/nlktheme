<?php

namespace Nlk\Theme\Image;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CdnImageManager — cdn.nlkmenu.com entegrasyonu.
 *
 * Temel URL formatları:
 *   GET /api/image/{id}?w=&h=&format=auto&fit=cover&smartCompress=1
 *   GET /api/image/{id}/{size}/{format}?quality=85&fit=cover
 *   GET /api/image/{id}/{variant}  (thumbnail|small|medium|large|webp|avif)
 *   GET /api/image/{id}/placeholder  (LQIP 32×32 blur WebP)
 *   GET /api/image/{id}/no-bg        (AI arka plan kaldırma)
 *   GET /api/image/{id}/upscale?scale=2 (AI süper çözünürlük)
 *   GET /api/image/{id}/signed-url   (imzalı URL üret)
 */
class CdnImageManager
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $defaultFormat;
    protected int    $defaultQuality;
    protected bool   $smartCompress;
    protected int    $cacheTtl;

    public function __construct()
    {
        $this->baseUrl        = rtrim((string) config('theme.cdn.url', 'https://cdn.nlkmenu.com'), '/');
        $this->apiKey         = (string) config('theme.cdn.api_key', '');
        $this->defaultFormat  = (string) config('theme.cdn.default_format', 'auto');
        $this->defaultQuality = (int)    config('theme.cdn.default_quality', 85);
        $this->smartCompress  = (bool)   config('theme.cdn.smart_compress', true);
        $this->cacheTtl       = (int)    config('theme.cdn.cache_ttl', 300);
    }

    // ─── URL Builder ──────────────────────────────────────────────────────────

    /**
     * Fluent URL builder döndürür.
     */
    public function build(string $imageId): ImageUrlBuilder
    {
        return new ImageUrlBuilder($imageId, $this);
    }

    /**
     * On-the-fly resize + format URL üretir.
     *
     * @param  string  $imageId  CDN görüntü ID
     * @param  array   $opts     [w, h, format, fit, position, quality, blur, crop, smartCompress...]
     */
    public function url(string $imageId, array $opts = []): string
    {
        $params = array_filter([
            'w'             => $opts['w'] ?? $opts['width'] ?? null,
            'h'             => $opts['h'] ?? $opts['height'] ?? null,
            'format'        => $opts['format'] ?? $this->defaultFormat,
            'fit'           => $opts['fit'] ?? null,
            'position'      => $opts['position'] ?? null,
            'quality'       => $opts['quality'] ?? $opts['q'] ?? $this->defaultQuality,
            'blur'          => $opts['blur'] ?? null,
            'sharpen'       => $opts['sharpen'] ?? null,
            'grayscale'     => $opts['grayscale'] ?? null,
            'filter'        => $opts['filter'] ?? null,
            'rotate'        => $opts['rotate'] ?? null,
            'crop'          => $opts['crop'] ?? null,
            'watermark'     => $opts['watermark'] ?? null,
            'smartCompress' => ($opts['smartCompress'] ?? $this->smartCompress) ? '1' : null,
            'brightness'    => $opts['brightness'] ?? null,
            'contrast'      => $opts['contrast'] ?? null,
        ], fn($v) => $v !== null && $v !== false && $v !== '');

        return $this->baseUrl . '/api/image/' . $imageId . '?' . http_build_query($params);
    }

    /**
     * Boyut + format ile URL: /api/image/{id}/{size}/{format}
     * Örnek: url800x600Webp('abc123') → .../api/image/abc123/800x600/webp
     *
     * @param  string  $size    "800x600" formatında
     * @param  string  $format  jpeg|png|webp|avif|gif
     */
    public function sizedUrl(string $imageId, string $size, string $format = 'webp', array $opts = []): string
    {
        $params = array_filter([
            'quality'  => $opts['quality'] ?? $this->defaultQuality,
            'fit'      => $opts['fit'] ?? null,
            'position' => $opts['position'] ?? null,
            'crop'     => $opts['crop'] ?? null,
        ], fn($v) => $v !== null);

        $base = $this->baseUrl . '/api/image/' . $imageId . '/' . $size . '/' . $format;
        return $params ? $base . '?' . http_build_query($params) : $base;
    }

    /**
     * Named variant URL: thumbnail | small | medium | large | webp | avif
     */
    public function variantUrl(string $imageId, string $variant, array $opts = []): string
    {
        $params = array_filter([
            'quality'  => $opts['quality'] ?? null,
            'fit'      => $opts['fit'] ?? null,
            'position' => $opts['position'] ?? null,
        ], fn($v) => $v !== null);

        $base = $this->baseUrl . '/api/image/' . $imageId . '/' . $variant;
        return $params ? $base . '?' . http_build_query($params) : $base;
    }

    /**
     * LQIP placeholder URL (32×32 blur WebP — lazy loading için).
     */
    public function placeholderUrl(string $imageId): string
    {
        return $this->baseUrl . '/api/image/' . $imageId . '/placeholder';
    }

    /**
     * AI: Arka plan kaldırılmış URL (AR görüntüler için).
     */
    public function noBgUrl(string $imageId, string $format = 'png'): string
    {
        return $this->baseUrl . '/api/image/' . $imageId . '/no-bg?format=' . $format;
    }

    /**
     * AI: Süper çözünürlük URL.
     */
    public function upscaleUrl(string $imageId, int $scale = 2, string $format = 'webp'): string
    {
        return $this->baseUrl . '/api/image/' . $imageId . '/upscale?scale=' . $scale . '&format=' . $format;
    }

    // ─── Srcset ───────────────────────────────────────────────────────────────

    /**
     * Responsive srcset string üretir.
     *
     * @param  array  $widths   [400, 800, 1200]
     */
    public function srcset(string $imageId, array $widths = [400, 800, 1200], array $opts = []): string
    {
        $parts = [];
        foreach ($widths as $w) {
            $parts[] = $this->url($imageId, array_merge($opts, ['w' => $w])) . ' ' . $w . 'w';
        }
        return implode(', ', $parts);
    }

    /**
     * CDN API'den suggestedSrcset bilgisini çeker (cached).
     */
    public function suggestedSrcset(string $imageId): string
    {
        $key  = 'theme:cdn:srcset:' . $imageId;
        $info = Cache::remember($key, $this->cacheTtl, fn () => $this->info($imageId));

        $items = $info['suggestedSrcset'] ?? [];
        if (empty($items)) {
            return $this->srcset($imageId);
        }

        return implode(', ', array_map(
            fn($s) => $s['url'] . ' ' . $s['width'] . 'w',
            $items
        ));
    }

    // ─── Signed URL ───────────────────────────────────────────────────────────

    /**
     * İmzalı (süreli) URL üretir. CDN API'den alır.
     * Private görüntüler veya güvenli paylaşım için.
     *
     * @param  int  $ttl  Saniye (default 3600)
     */
    public function signedUrl(string $imageId, int $ttl = 3600): string
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get($this->baseUrl . '/api/image/' . $imageId . '/signed-url', [
                    'expiresIn' => $ttl,
                ]);

            if ($response->successful()) {
                return $response->json('url', $this->url($imageId));
            }
        } catch (\Throwable $e) {
            Log::warning('CDN signedUrl error', ['id' => $imageId, 'error' => $e->getMessage()]);
        }

        // Fallback: imzasız URL
        return $this->url($imageId);
    }

    // ─── Import ───────────────────────────────────────────────────────────────

    /**
     * Harici URL'deki görseli CDN'e import eder.
     * Döner: ['id' => '...', ...]
     */
    public function importFromUrl(string $imageUrl, array $opts = []): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->post($this->baseUrl . '/api/import/url', array_merge(
                    ['url' => $imageUrl],
                    $opts
                ));

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('CDN importFromUrl failed', ['url' => $imageUrl, 'status' => $response->status()]);
        } catch (\Throwable $e) {
            Log::warning('CDN importFromUrl error', ['url' => $imageUrl, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Toplu URL import (max 50).
     */
    public function importBatch(array $urls): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->post($this->baseUrl . '/api/import/batch', ['urls' => $urls]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning('CDN importBatch error', ['error' => $e->getMessage()]);
        }

        return [];
    }

    // ─── Info ─────────────────────────────────────────────────────────────────

    /**
     * Görüntü metadata: suggestedSrcset, placeholderDataUrl, dominantColor, availableSizes
     */
    public function info(string $imageId): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get($this->baseUrl . '/api/info/' . $imageId);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning('CDN info error', ['id' => $imageId, 'error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * LQIP base64 placeholder (data URL).
     * CDN /api/info/{id} yanıtındaki placeholderDataUrl alanı.
     */
    public function lqipDataUrl(string $imageId): ?string
    {
        $key  = 'theme:cdn:lqip:' . $imageId;
        $info = Cache::remember($key, $this->cacheTtl * 10, fn () => $this->info($imageId));
        return $info['placeholderDataUrl'] ?? null;
    }

    /**
     * Dominant color (CSS background-color için).
     */
    public function dominantColor(string $imageId): string
    {
        $key  = 'theme:cdn:color:' . $imageId;
        $info = Cache::remember($key, $this->cacheTtl * 10, fn () => $this->info($imageId));
        return $info['dominantColor'] ?? '#f0f0f0';
    }

    // ─── HTML Helpers ─────────────────────────────────────────────────────────

    /**
     * Temel <img> etiketi üretir.
     */
    public function imgTag(string $imageId, array $opts = [], array $attrs = []): string
    {
        $src    = $this->url($imageId, $opts);
        $srcset = isset($opts['srcset']) ? $this->srcset($imageId, $opts['srcset'], $opts) : '';
        $alt    = htmlspecialchars($attrs['alt'] ?? '', ENT_QUOTES);
        $width  = $attrs['width'] ?? $opts['w'] ?? '';
        $height = $attrs['height'] ?? $opts['h'] ?? '';
        $class  = $attrs['class'] ?? '';
        $id     = $attrs['id'] ?? '';
        $extra  = '';

        foreach (['data-zoom', 'data-lightbox', 'aria-label'] as $k) {
            if (isset($attrs[$k])) {
                $extra .= ' ' . $k . '="' . htmlspecialchars($attrs[$k], ENT_QUOTES) . '"';
            }
        }

        $tag = '<img src="' . $src . '"';
        $tag .= $srcset ? ' srcset="' . $srcset . '" sizes="(max-width:768px) 100vw, 50vw"' : '';
        $tag .= ' alt="' . $alt . '"';
        $tag .= $width  ? ' width="' . $width . '"' : '';
        $tag .= $height ? ' height="' . $height . '"' : '';
        $tag .= $class  ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';
        $tag .= $id     ? ' id="' . htmlspecialchars($id, ENT_QUOTES) . '"' : '';
        $tag .= $extra;
        $tag .= ' loading="eager" decoding="async">';

        return $tag;
    }

    /**
     * Lazy-loading <img> etiketi (LQIP blur-up pattern).
     *
     * 1. Placeholder data URL veya CDN placeholder olarak src
     * 2. data-src = asıl CDN URL
     * 3. IntersectionObserver ile swap (Blade @cdn_img_lazy kullanımı)
     */
    public function lazyImgTag(string $imageId, array $opts = [], array $attrs = []): string
    {
        $placeholder = $this->lqipDataUrl($imageId) ?? $this->placeholderUrl($imageId);
        $fullSrc     = $this->url($imageId, $opts);
        $srcset      = isset($opts['srcset']) ? $this->srcset($imageId, $opts['srcset'], $opts) : '';
        $alt         = htmlspecialchars($attrs['alt'] ?? '', ENT_QUOTES);
        $width       = $attrs['width'] ?? $opts['w'] ?? '';
        $height      = $attrs['height'] ?? $opts['h'] ?? '';
        $class       = trim(($attrs['class'] ?? '') . ' cdn-lazy');
        $color       = $this->dominantColor($imageId);

        $tag = '<img';
        $tag .= ' src="' . $placeholder . '"';
        $tag .= ' data-src="' . $fullSrc . '"';
        $tag .= $srcset ? ' data-srcset="' . $srcset . '" sizes="(max-width:768px) 100vw, 50vw"' : '';
        $tag .= ' alt="' . $alt . '"';
        $tag .= $width  ? ' width="' . $width . '"' : '';
        $tag .= $height ? ' height="' . $height . '"' : '';
        $tag .= ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"';
        $tag .= ' style="background-color:' . htmlspecialchars($color, ENT_QUOTES) . ';transition:filter .3s;"';
        $tag .= ' loading="lazy" decoding="async">';

        return $tag;
    }

    // ─── Internal ────────────────────────────────────────────────────────────

    public function getBaseUrl(): string { return $this->baseUrl; }
    public function getApiKey(): string  { return $this->apiKey; }

    protected function headers(): array
    {
        return array_filter([
            'X-API-Key'    => $this->apiKey ?: null,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ]);
    }
}
