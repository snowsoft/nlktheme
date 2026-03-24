<?php

namespace Nlk\Theme\Image;

/**
 * ImageUrlBuilder — CdnImageManager için fluent URL oluşturucu.
 *
 * Kullanım:
 *   CdnImage::build('img-id')
 *       ->width(800)->height(600)
 *       ->format('webp')->fit('cover')
 *       ->quality(90)->smartCompress()
 *       ->crop('salient')
 *       ->get();
 */
class ImageUrlBuilder
{
    protected string          $imageId;
    protected CdnImageManager $manager;
    protected array           $opts = [];

    public function __construct(string $imageId, CdnImageManager $manager)
    {
        $this->imageId = $imageId;
        $this->manager = $manager;
    }

    // ─── Boyut ─────────────────────────────────────────────────────────────

    public function width(int $w): static
    {
        $this->opts['w'] = $w;
        return $this;
    }

    public function height(int $h): static
    {
        $this->opts['h'] = $h;
        return $this;
    }

    public function size(int $w, int $h): static
    {
        return $this->width($w)->height($h);
    }

    // ─── Format & Kalite ───────────────────────────────────────────────────

    /** format: jpeg | png | webp | avif | auto */
    public function format(string $format): static
    {
        $this->opts['format'] = $format;
        return $this;
    }

    /** WebP/AVIF akıllı sıkıştırma (Accept header'a göre auto-format) */
    public function auto(): static
    {
        return $this->format('auto');
    }

    /** Kalite 1-100 */
    public function quality(int $q): static
    {
        $this->opts['quality'] = $q;
        return $this;
    }

    /** WebP/AVIF akıllı sıkıştırma — boyut/kalite dengesi */
    public function smartCompress(bool $enabled = true): static
    {
        $this->opts['smartCompress'] = $enabled ? '1' : null;
        return $this;
    }

    // ─── Fit & Kırpma ──────────────────────────────────────────────────────

    /** fit: inside | cover | contain | fill | outside */
    public function fit(string $mode): static
    {
        $this->opts['fit'] = $mode;
        return $this;
    }

    /** cover = hedefi doldur, crop yap */
    public function cover(): static { return $this->fit('cover'); }

    /** inside = sığdır (letterbox yok, büyütme yok) */
    public function inside(): static { return $this->fit('inside'); }

    /**
     * Kırpma konumu
     * center | top | bottom | left | right | attention | entropy
     */
    public function position(string $pos): static
    {
        $this->opts['position'] = $pos;
        return $this;
    }

    /** Akıllı kırpma — parlaklık, ten rengi, nesne */
    public function attention(): static { return $this->position('attention'); }

    /** Akıllı kırpma — Shannon entropi (ilgi odağı) */
    public function entropy(): static { return $this->position('entropy'); }

    /**
     * Manuel crop: "x,y,w,h" | "smart" | "salient" (AI)
     */
    public function crop(string $cropDef): static
    {
        $this->opts['crop'] = $cropDef;
        return $this;
    }

    /** AI saliency crop */
    public function salientCrop(): static { return $this->crop('salient'); }

    // ─── Efektler ──────────────────────────────────────────────────────────

    public function blur(float $sigma): static
    {
        $this->opts['blur'] = $sigma;
        return $this;
    }

    public function sharpen(float $amount): static
    {
        $this->opts['sharpen'] = $amount;
        return $this;
    }

    public function grayscale(): static
    {
        $this->opts['grayscale'] = '1';
        return $this;
    }

    public function sepia(): static
    {
        $this->opts['filter'] = 'sepia';
        return $this;
    }

    public function negate(): static
    {
        $this->opts['filter'] = 'negate';
        return $this;
    }

    public function rotate(int $degrees): static
    {
        $this->opts['rotate'] = $degrees;
        return $this;
    }

    public function brightness(float $factor): static
    {
        $this->opts['brightness'] = $factor;
        return $this;
    }

    public function contrast(float $value): static
    {
        $this->opts['contrast'] = $value;
        return $this;
    }

    // ─── Watermark & Güvenlik ──────────────────────────────────────────────

    /** Köşe overlay watermark */
    public function watermark(): static
    {
        $this->opts['watermark'] = '1';
        return $this;
    }

    /** Görünmez LSB steganografi filigranı */
    public function invisibleWatermark(): static
    {
        $this->opts['watermark'] = 'invisible';
        return $this;
    }

    // ─── Responsive ────────────────────────────────────────────────────────

    /**
     * Responsive srcset üret.
     *
     * @param  array  $widths  [400, 800, 1200]
     */
    public function srcset(array $widths = [400, 800, 1200]): string
    {
        return $this->manager->srcset($this->imageId, $widths, $this->opts);
    }

    // ─── Çıktı ─────────────────────────────────────────────────────────────

    /** URL string'ini döndürür */
    public function get(): string
    {
        return $this->manager->url($this->imageId, $this->opts);
    }

    /** URL string olarak kullan (Blade: {!! ... !!}) */
    public function __toString(): string
    {
        return $this->get();
    }

    /** <img> etiketi */
    public function img(array $attrs = []): string
    {
        return $this->manager->imgTag($this->imageId, $this->opts, $attrs);
    }

    /** Lazy-loading <img> etiketi (LQIP blur-up) */
    public function lazyImg(array $attrs = []): string
    {
        return $this->manager->lazyImgTag($this->imageId, $this->opts, $attrs);
    }

    /** named variant URL */
    public function variant(string $variant): string
    {
        return $this->manager->variantUrl($this->imageId, $variant, $this->opts);
    }

    /** LQIP placeholder URL */
    public function placeholder(): string
    {
        return $this->manager->placeholderUrl($this->imageId);
    }

    /** AI no-bg URL */
    public function noBg(string $format = 'png'): string
    {
        return $this->manager->noBgUrl($this->imageId, $format);
    }

    /** AI upscale URL */
    public function upscale(int $scale = 2): string
    {
        return $this->manager->upscaleUrl($this->imageId, $scale);
    }

    /** İmzalı URL */
    public function signed(int $ttl = 3600): string
    {
        return $this->manager->signedUrl($this->imageId, $ttl);
    }
}
