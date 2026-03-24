<?php

namespace Nlk\Theme\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Nlk\Theme\SEO\SeoManager title(string $title, ?string $suffix = null)
 * @method static \Nlk\Theme\SEO\SeoManager description(string $desc)
 * @method static \Nlk\Theme\SEO\SeoManager keywords(string $keywords)
 * @method static \Nlk\Theme\SEO\SeoManager robots(string $value = 'index,follow')
 * @method static \Nlk\Theme\SEO\SeoManager noindex()
 * @method static \Nlk\Theme\SEO\SeoManager og(string $property, string $content)
 * @method static \Nlk\Theme\SEO\SeoManager ogType(string $type = 'website')
 * @method static \Nlk\Theme\SEO\SeoManager ogImage(string $url, ?int $width = null, ?int $height = null)
 * @method static \Nlk\Theme\SEO\SeoManager ogProduct(float $price, string $currency = 'TRY', string $availability = 'in stock')
 * @method static \Nlk\Theme\SEO\SeoManager twitter(string $name, string $content)
 * @method static \Nlk\Theme\SEO\SeoManager twitterCard(string $type = 'summary_large_image')
 * @method static \Nlk\Theme\SEO\SeoManager canonical(string $url)
 * @method static \Nlk\Theme\SEO\SeoManager hreflang(string $lang, string $url)
 * @method static \Nlk\Theme\SEO\SeoManager preconnect(string $url, bool $crossorigin = false)
 * @method static \Nlk\Theme\SEO\SeoManager preload(string $url, string $as, ?string $type = null, bool $crossorigin = false)
 * @method static \Nlk\Theme\SEO\SeoManager addJsonLd(array $schema)
 * @method static \Nlk\Theme\SEO\SeoManager addProductSchema(array $data)
 * @method static \Nlk\Theme\SEO\SeoManager addBreadcrumb(array $items)
 * @method static \Nlk\Theme\SEO\SeoManager addWebSiteSchema(string $name, string $url, ?string $searchUrl = null)
 * @method static \Nlk\Theme\SEO\SeoManager addFaqSchema(array $faqs)
 * @method static \Nlk\Theme\SEO\SeoManager addItemListSchema(array $items, string $listName = 'Products')
 * @method static string render()
 *
 * @see \Nlk\Theme\SEO\SeoManager
 */
class Seo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nlk.seo';
    }
}
