<?php

namespace Nlk\Theme\Facades;

use Illuminate\Support\Facades\Facade;
use Nlk\Theme\Image\CdnImageManager;

/**
 * @method static string url(string $imageId, array $opts = [])
 * @method static string sizedUrl(string $imageId, string $size, string $format = 'webp', array $opts = [])
 * @method static string variantUrl(string $imageId, string $variant, array $opts = [])
 * @method static string placeholderUrl(string $imageId)
 * @method static string noBgUrl(string $imageId, string $format = 'png')
 * @method static string upscaleUrl(string $imageId, int $scale = 2, string $format = 'webp')
 * @method static string signedUrl(string $imageId, int $ttl = 3600)
 * @method static string srcset(string $imageId, array $widths = [400, 800, 1200], array $opts = [])
 * @method static string suggestedSrcset(string $imageId)
 * @method static string imgTag(string $imageId, array $opts = [], array $attrs = [])
 * @method static string lazyImgTag(string $imageId, array $opts = [], array $attrs = [])
 * @method static string lqipDataUrl(string $imageId)
 * @method static string dominantColor(string $imageId)
 * @method static array  info(string $imageId)
 * @method static array|null importFromUrl(string $url, array $opts = [])
 * @method static array  importBatch(array $urls)
 * @method static \Nlk\Theme\Image\ImageUrlBuilder build(string $imageId)
 *
 * @see CdnImageManager
 */
class CdnImage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nlk.cdn';
    }
}
