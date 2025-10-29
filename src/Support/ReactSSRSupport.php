<?php

namespace Nlk\Theme\Support;

use Illuminate\Support\Facades\Vite;

class ReactSSRSupport
{
    /**
     * Check if Vite is configured for React.
     *
     * @return bool
     */
    public static function isViteConfigured()
    {
        return function_exists('Vite::react') || file_exists(public_path('build/manifest.json'));
    }

    /**
     * Render React component with SSR support.
     *
     * @param  string $component
     * @param  array  $props
     * @param  string $id
     * @return string
     */
    public static function renderComponent($component, array $props = [], $id = null)
    {
        $id = $id ?: 'react-' . uniqid();
        $propsJson = json_encode($props, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $html = '<div id="' . $id . '" data-props="' . htmlspecialchars($propsJson, ENT_QUOTES) . '"></div>';
        
        // Add hydration script
        if (static::isViteConfigured()) {
            $html .= '<script type="module">';
            $html .= 'import { hydrate } from "/resources/js/app.js";';
            $html .= 'hydrate("' . $id . '", ' . $propsJson . ');';
            $html .= '</script>';
        }

        return $html;
    }

    /**
     * Get Vite React assets.
     *
     * @param  array $entrypoints
     * @return string
     */
    public static function viteAssets(array $entrypoints = [])
    {
        if (!static::isViteConfigured()) {
            return '';
        }

        try {
            if (empty($entrypoints)) {
                return Vite::react('resources/js/app.jsx', 'resources/css/app.css');
            }

            return Vite::react($entrypoints[0] ?? 'resources/js/app.jsx', $entrypoints[1] ?? null);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Render React component with server-side rendering.
     * Note: Requires Node.js and React SSR setup.
     *
     * @param  string $componentPath
     * @param  array  $props
     * @return string
     */
    public static function renderSSR($componentPath, array $props = [])
    {
        // This would require a Node.js SSR server
        // For now, return client-side render markup
        return static::renderComponent($componentPath, $props);
    }
}

