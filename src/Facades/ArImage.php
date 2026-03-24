<?php

namespace Nlk\Theme\Facades;

use Illuminate\Support\Facades\Facade;
use Nlk\Theme\Image\ArImageManager;

/**
 * @method static string noBgUrl(string $imageId, string $format = 'png')
 * @method static string noBgWebp(string $imageId)
 * @method static string noBgImg(string $imageId, array $attrs = [])
 * @method static string arTag(string $imageId, ?string $glbUrl = null, ?string $usdzUrl = null, array $opts = [])
 * @method static string arScript()
 *
 * @see ArImageManager
 */
class ArImage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nlk.ar';
    }
}
