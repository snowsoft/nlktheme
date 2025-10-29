<?php

namespace Nlk\Theme\Support;

use Inertia\Inertia;

class InertiaSupport
{
    /**
     * Check if Inertia is installed.
     *
     * @return bool
     */
    public static function isInstalled()
    {
        return class_exists(Inertia::class);
    }

    /**
     * Render Inertia page with theme layout.
     *
     * @param  string $component
     * @param  array  $props
     * @param  string|null $rootView
     * @return \Inertia\Response
     */
    public static function render($component, array $props = [], $rootView = null)
    {
        if (!static::isInstalled()) {
            throw new \Exception('Inertia.js is not installed. Please install inertiajs/inertia-laravel package.');
        }

        // Use theme layout if root view not specified
        if (!$rootView && function_exists('theme')) {
            $theme = theme();
            $layout = $theme->getLayoutName();
            $rootView = 'theme.' . $theme->getThemeName() . '::layouts.' . $layout;
        }

        return Inertia::render($component, $props, [
            'rootView' => $rootView
        ]);
    }

    /**
     * Share data globally with Inertia.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public static function share($key, $value)
    {
        if (static::isInstalled()) {
            Inertia::share($key, $value);
        }
    }

    /**
     * Check if current request is an Inertia request.
     *
     * @return bool
     */
    public static function isInertiaRequest()
    {
        if (!static::isInstalled()) {
            return false;
        }

        return request()->header('X-Inertia') !== null ||
               request()->header('X-Inertia-Version') !== null;
    }

    /**
     * Get Inertia version.
     *
     * @return string|null
     */
    public static function getVersion()
    {
        if (!static::isInstalled()) {
            return null;
        }

        try {
            return Inertia::getVersion();
        } catch (\Exception $e) {
            return null;
        }
    }
}

