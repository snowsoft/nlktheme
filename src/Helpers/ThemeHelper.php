<?php

namespace Nlk\Theme\Helpers;

class ThemeHelper
{
    /**
     * Check if we're in production environment.
     *
     * @return boolean
     */
    public static function isProduction()
    {
        return !config('app.debug');
    }

    /**
     * Get theme asset URL.
     *
     * @param  string $path
     * @return string
     */
    public static function assetUrl($path)
    {
        $baseUrl = config('theme.assetUrl', '/');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Get theme asset path.
     *
     * @param  string $path
     * @return string
     */
    public static function assetPath($path)
    {
        return public_path('themes/' . theme()->getThemeName() . '/' . ltrim($path, '/'));
    }

    /**
     * Include a CSS file.
     *
     * @param  string $path
     * @param  string $media
     * @return string
     */
    public static function css($path, $media = 'all')
    {
        $url = static::assetUrl($path);
        return "<link rel='stylesheet' type='text/css' href='{$url}' media='{$media}'>";
    }

    /**
     * Include a JavaScript file.
     *
     * @param  string $path
     * @param  boolean $defer
     * @return string
     */
    public static function js($path, $defer = false)
    {
        $url = static::assetUrl($path);
        $deferAttr = $defer ? 'defer' : '';
        return "<script type='text/javascript' src='{$url}' {$deferAttr}></script>";
    }

    /**
     * Get image URL.
     *
     * @param  string $path
     * @param  string $alt
     * @param  array  $attributes
     * @return string
     */
    public static function image($path, $alt = '', $attributes = [])
    {
        $url = static::assetUrl($path);
        $attrs = '';
        
        foreach ($attributes as $key => $value) {
            $attrs .= " {$key}='{$value}'";
        }
        
        return "<img src='{$url}' alt='{$alt}'{$attrs}>";
    }

    /**
     * Active class helper for navigation.
     *
     * @param  string $path
     * @param  string $active
     * @return string
     */
    public static function active($path, $active = 'active')
    {
        return request()->is($path) ? $active : '';
    }

    /**
     * Sanitize HTML output.
     *
     * @param  string $string
     * @return string
     */
    public static function sanitize($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Truncate string with ellipsis.
     *
     * @param  string  $string
     * @param  integer $limit
     * @param  string  $append
     * @return string
     */
    public static function truncate($string, $limit = 100, $append = '...')
    {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }
        
        return mb_substr($string, 0, $limit) . $append;
    }

    /**
     * Get current theme name.
     *
     * @return string
     */
    public static function currentTheme()
    {
        if (function_exists('theme')) {
            return theme()->getThemeName();
        }
        
        return config('theme.themeDefault', 'default');
    }

    /**
     * Check if view exists in current theme.
     *
     * @param  string $view
     * @return boolean
     */
    public static function hasView($view)
    {
        if (function_exists('theme')) {
            return theme()->viewExists($view);
        }
        
        return false;
    }

    /**
     * Render a partial if it exists.
     *
     * @param  string $view
     * @param  array  $args
     * @param  mixed  $default
     * @return string
     */
    public static function renderIfExists($view, $args = [], $default = '')
    {
        if (static::hasView($view)) {
            if (function_exists('theme')) {
                return theme()->partial($view, $args);
            }
        }
        
        return $default;
    }

    /**
     * Cache helper.
     *
     * @param  string   $key
     * @param  callable $callback
     * @param  integer  $minutes
     * @return mixed
     */
    public static function cache($key, $callback, $minutes = 60)
    {
        return \Cache::remember("theme.{$key}", $minutes * 60, $callback);
    }

    /**
     * Time ago helper.
     *
     * @param  datetime $datetime
     * @return string
     */
    public static function timeAgo($datetime)
    {
        $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            return floor($diff/60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff/3600) . ' hours ago';
        } elseif ($diff < 604800) {
            return floor($diff/86400) . ' days ago';
        } else {
            return date('Y-m-d', $timestamp);
        }
    }

    /**
     * Format number with suffix (K, M, B).
     *
     * @param  integer $number
     * @return string
     */
    public static function numberFormat($number)
    {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . 'B';
        } elseif ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        
        return $number;
    }
}
