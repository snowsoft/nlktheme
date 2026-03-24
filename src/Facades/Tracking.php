<?php

namespace Nlk\Theme\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Nlk\Theme\Tracking\TrackingManager configure(array $config)
 * @method static bool isEnabled()
 * @method static \Nlk\Theme\Tracking\TrackingManager push(string $key, mixed $value)
 * @method static \Nlk\Theme\Tracking\TrackingManager pageView(array $meta = [])
 * @method static \Nlk\Theme\Tracking\TrackingManager viewContent(array $product)
 * @method static \Nlk\Theme\Tracking\TrackingManager addToCart(array $item, float $value, string $currency = 'TRY')
 * @method static \Nlk\Theme\Tracking\TrackingManager initiateCheckout(float $value, string $currency, int $numItems, array $contents = [])
 * @method static \Nlk\Theme\Tracking\TrackingManager purchase(array $order)
 * @method static \Nlk\Theme\Tracking\TrackingManager search(string $query)
 * @method static string renderHead()
 * @method static string renderBody()
 * @method static string renderEvents()
 *
 * @see \Nlk\Theme\Tracking\TrackingManager
 */
class Tracking extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nlk.tracking';
    }
}
