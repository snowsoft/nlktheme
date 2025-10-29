<?php

namespace Nlk\Theme\Security;

use Illuminate\Support\Str;

class SecurityHelper
{
    /**
     * Sanitize HTML content to prevent XSS attacks.
     *
     * @param  string $html
     * @param  array  $allowedTags
     * @return string
     */
    public static function sanitizeHtml($html, $allowedTags = null)
    {
        if ($allowedTags === null) {
            // Default allowed tags for safe HTML
            $allowedTags = '<p><br><strong><em><u><a><img><ul><ol><li><h1><h2><h3><h4><h5><h6>';
        }

        return strip_tags($html, $allowedTags);
    }

    /**
     * Escape output to prevent XSS.
     *
     * @param  mixed $value
     * @return string
     */
    public static function escape($value)
    {
        if (is_array($value) || is_object($value)) {
            return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8', false);
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Validate view path to prevent directory traversal attacks.
     *
     * @param  string $path
     * @return bool
     */
    public static function isValidViewPath($path)
    {
        // Remove namespace if exists
        $path = str_replace(['theme.', '::'], ['', '/'], $path);
        
        // Check for directory traversal patterns
        $dangerous = ['..', '../', '..\\', '%2e%2e', '%2f', '%5c'];
        
        foreach ($dangerous as $pattern) {
            if (strpos($path, $pattern) !== false) {
                return false;
            }
        }

        // Check if path contains only allowed characters
        return preg_match('/^[a-zA-Z0-9\/_\-\s\.]+$/', $path) === 1;
    }

    /**
     * Generate Content Security Policy header.
     *
     * @param  array $directives
     * @return string
     */
    public static function generateCSP(array $directives = [])
    {
        $defaults = [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline' 'unsafe-eval'",
            'style-src' => "'self' 'unsafe-inline'",
            'img-src' => "'self' data: https:",
            'font-src' => "'self' data:",
            'connect-src' => "'self'",
            'frame-ancestors' => "'none'",
        ];

        $directives = array_merge($defaults, $directives);

        $csp = [];
        foreach ($directives as $directive => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
            $csp[] = $directive . ' ' . $value;
        }

        return implode('; ', $csp);
    }

    /**
     * Generate nonce for CSP script/style tags.
     *
     * @return string
     */
    public static function generateNonce()
    {
        return base64_encode(random_bytes(16));
    }

    /**
     * Clean file name to prevent path traversal.
     *
     * @param  string $filename
     * @return string
     */
    public static function sanitizeFilename($filename)
    {
        // Remove directory separators
        $filename = str_replace(['/', '\\', '..'], '', $filename);
        
        // Remove null bytes
        $filename = str_replace("\0", '', $filename);
        
        // Only allow alphanumeric, dots, hyphens, and underscores
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    }

    /**
     * Validate asset path.
     *
     * @param  string $path
     * @return bool
     */
    public static function isValidAssetPath($path)
    {
        // Allow relative paths starting with / or ./
        if (preg_match('/^(\.\.\/|\/\.\.|\.\.\\\\)/', $path)) {
            return false;
        }

        return true;
    }
}

