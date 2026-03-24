<?php

 

return array(

	/*
	|--------------------------------------------------------------------------
	| Asset url path
	|--------------------------------------------------------------------------
	|
	| The path to asset, this config can be cdn host.
	| eg. http://cdn.domain.com
	|
	*/

	'assetUrl' => env('APP_ASSET_URL', '/'),


	'defaultTheme' => 'default',
	
	/*
	|--------------------------------------------------------------------------
	| Use Default Theme Fallback
	|--------------------------------------------------------------------------
	|
	| If set to true, when a file is not found in the current theme,
	| it will try to find it in the defaultTheme before falling back
	| to regular Laravel views.
	|
	| Set to false to prevent automatic fallback to defaultTheme.
	|
	*/
	
	'useDefaultThemeFallback' => true,
	
	'version' => '000.1',

	/*
	|--------------------------------------------------------------------------
	| Theme Default
	|--------------------------------------------------------------------------
	|
	| If you don't set a theme when using a "Theme" class 
	| the default theme will replace automatically.
	|
	*/

	'themeDefault' => env('APP_THEME', 'default'),

	/*
	|--------------------------------------------------------------------------
	| Layout Default
	|--------------------------------------------------------------------------
	|
	| If you don't set a layout when using a "Theme" class 
	| the default layout will replace automatically.
	|
	*/

	'layoutDefault' => env('APP_THEME_LAYOUT', 'layout'),

	/*
	|--------------------------------------------------------------------------
	| Path to lookup theme
	|--------------------------------------------------------------------------
	|
	| The root path contains themes collections.
	|
	*/

	'themeDir' => env('APP_THEME_DIR', 'themes'),


	'themeURL' => env('APP_THEME_URL', 'themes'),


	/*
	|--------------------------------------------------------------------------
	| Namespaces
	|--------------------------------------------------------------------------
	|
	| Class namespace.
	|
	*/

	'namespaces' => array(
		'widget' => 'App\Widgets'
 	),
	
	
	'minify' => false,

	/*
	|--------------------------------------------------------------------------
	| Template Caching
	|--------------------------------------------------------------------------
	|
	| Enable template compilation caching for better performance.
	| Cache will be automatically cleared when templates are modified.
	|
	*/

	'templateCacheEnabled' => env('APP_THEME_CACHE', true),

	/*
	|--------------------------------------------------------------------------
	| Template Auto Reload
	|--------------------------------------------------------------------------
	|
	| In development, automatically reload templates when they change.
	| This feature only works when APP_DEBUG is true.
	|
	*/

	'autoReload' => env('APP_DEBUG', false),

	/*
	|--------------------------------------------------------------------------
	| Default Template Engine
	|--------------------------------------------------------------------------
	|
	| Supported: 'blade', 'twig', 'smarty', 'plain'
	| Currently only 'blade' is fully implemented.
	|
	*/

	'defaultEngine' => 'blade',

	/*
	|--------------------------------------------------------------------------
	| Listener from events
	|--------------------------------------------------------------------------
	|
	| You can hook a theme when event fired on activities this is cool
	| feature to set up a title, meta, default styles and scripts.
	|
	*/

	'events' => array(
 

	),

	/*
	|--------------------------------------------------------------------------
	| Security Settings
	|--------------------------------------------------------------------------
	|
	| Security configurations for XSS protection, CSP headers, and path validation.
	|
	*/

	'security' => array(
		// Enable security headers
		'headers' => env('THEME_SECURITY_HEADERS', true),

		// Content Security Policy
		'csp' => array(
			'enabled' => env('THEME_CSP_ENABLED', false),
			'directives' => array(
				'default-src' => "'self'",
				'script-src' => "'self' 'unsafe-inline' 'unsafe-eval'",
				'style-src' => "'self' 'unsafe-inline'",
				'img-src' => "'self' data: https:",
				'font-src' => "'self' data:",
				'connect-src' => "'self'",
				'frame-ancestors' => "'none'",
			),
		),

		// Security headers configuration
		'headers_config' => array(
			'x_content_type' => true,
			'x_frame_options' => true,
			'x_frame_options_value' => 'DENY',
			'x_xss_protection' => true,
			'referrer_policy' => true,
			'referrer_policy_value' => 'strict-origin-when-cross-origin',
			'permissions_policy' => false,
			'permissions_policy_value' => '',
		),

		// Enable view path validation
		'validate_view_paths' => env('THEME_VALIDATE_VIEW_PATHS', true),
	),

	/*
	|--------------------------------------------------------------------------
	| Frontend Framework Support
	|--------------------------------------------------------------------------
	|
	| Enable support for modern frontend frameworks like Livewire, React, Inertia.
	|
	*/

	'frontend' => array(
		// Livewire support
		'livewire' => array(
			'enabled' => env('THEME_LIVEWIRE_ENABLED', false),
			'auto_detect' => true,
		),

		// React + SSR support
		'react' => array(
			'enabled' => env('THEME_REACT_ENABLED', false),
			'ssr_enabled' => env('THEME_REACT_SSR_ENABLED', false),
			'vite_entry' => 'resources/js/app.jsx',
		),

		// Inertia.js support
		'inertia' => array(
			'enabled' => env('THEME_INERTIA_ENABLED', false),
			'auto_detect' => true,
		),
	),

	/*
	|--------------------------------------------------------------------------
	| FlexPage-style Section Engine
	|--------------------------------------------------------------------------
	*/

	'shopify' => array(
		'sections_path'  => 'sections',
		'templates_path' => 'templates',
	),

	/*
	|--------------------------------------------------------------------------
	| Hybrid Data Sources (MySQL + REST API)
	|--------------------------------------------------------------------------
	*/

	'data_sources' => array(
		'mysql'            => true,
		'mysql_connection' => env('DB_CONNECTION', 'mysql'),
		'api'              => env('THEME_API_URL', ''),
		'api_key'          => env('THEME_API_KEY', ''),
		'api_timeout'      => (int) env('THEME_API_TIMEOUT', 5),
	),

	/*
	|--------------------------------------------------------------------------
	| Page Builder
	|--------------------------------------------------------------------------
	*/

	'builder' => array(
		'storage'        => env('THEME_BUILDER_STORAGE', 'database'), // 'database' | 'file'
		'cache_ttl'      => (int) env('THEME_BUILDER_TTL', 300),
		'default_tenant' => env('THEME_DEFAULT_TENANT', 'default'),
	),

	/*
	|--------------------------------------------------------------------------
	| Marketing Tracking (GTM, GA4, Google Ads, Facebook Meta Pixel)
	|--------------------------------------------------------------------------
	*/

	'tracking' => array(
		'enabled'       => env('THEME_TRACKING_ENABLED', true),
		'gtm_id'        => env('THEME_GTM_ID', ''),         // GTM-XXXXXXX
		'ga4_id'        => env('THEME_GA4_ID', ''),         // G-XXXXXXXXXX
		'google_ads_id' => env('THEME_GADS_ID', ''),        // AW-XXXXXXXXX
		'fb_pixel_id'   => env('THEME_FB_PIXEL_ID', ''),    // Facebook Pixel ID
		'consent_mode'  => env('THEME_CONSENT_MODE', 'default'),
	),

	/*
	|--------------------------------------------------------------------------
	| CDN Image Engine (cdn.nlkmenu.com)
	|--------------------------------------------------------------------------
	|
	| URL formatları:
	|   /api/image/{id}?w=800&format=auto&fit=cover&smartCompress=1
	|   /api/image/{id}/{size}/{format}   → 800x600/webp
	|   /api/image/{id}/{variant}         → thumbnail|small|medium|large
	|   /api/image/{id}/placeholder       → LQIP 32×32 blur WebP
	|   /api/image/{id}/no-bg             → AI arka plan kaldırma (AR)
	|   /api/image/{id}/upscale?scale=2   → AI süper çözünürlük
	|
	| Auth: X-API-Key header
	|
	*/

	'cdn' => array(
		'url'            => env('CDN_URL', 'https://cdn.nlkmenu.com'),
		'api_key'        => env('CDN_API_KEY', ''),
		'tenant'         => env('CDN_TENANT', 'default'),
		'default_format' => env('CDN_DEFAULT_FORMAT', 'auto'),  // auto = Accept header
		'default_quality'=> (int) env('CDN_DEFAULT_QUALITY', 85),
		'smart_compress' => (bool) env('CDN_SMART_COMPRESS', true),
		'signed_urls'    => (bool) env('CDN_SIGNED_URLS', false),
		'cache_ttl'      => (int) env('CDN_CACHE_TTL', 300),
	),

	/*
	|--------------------------------------------------------------------------
	| Performance (Faz 8)
	|--------------------------------------------------------------------------
	*/

	'performance' => array(
		'critical_css_ttl'   => (int) env('THEME_CRITICAL_CSS_TTL', 86400),
		'critical_css_path'  => env('THEME_CRITICAL_CSS_PATH', storage_path('app/theme/critical-css')),
		'cache_driver'       => env('THEME_CACHE_DRIVER', 'redis'),      // redis önerilir
		'edge_cache_ttl'     => (int) env('THEME_EDGE_CACHE_TTL', 3600), // Cloudflare vb.
	),

	/*
	|--------------------------------------------------------------------------
	| PWA (Faz 8)
	|--------------------------------------------------------------------------
	*/

	'pwa' => array(
		'name'             => env('PWA_NAME', config('app.name')),
		'short_name'       => env('PWA_SHORT_NAME', ''),
		'description'      => env('PWA_DESCRIPTION', ''),
		'theme_color'      => env('PWA_THEME_COLOR', '#000000'),
		'background_color' => env('PWA_BG_COLOR', '#ffffff'),
		'display'          => env('PWA_DISPLAY', 'standalone'), // standalone | fullscreen | browser
		'start_url'        => env('PWA_START_URL', '/'),
		'lang'             => env('PWA_LANG', 'tr'),
		'manifest_url'     => env('PWA_MANIFEST_URL', '/manifest.json'),
		'service_worker'   => (bool) env('PWA_SERVICE_WORKER', false),
		'sw_path'          => env('PWA_SW_PATH', '/sw.js'),
		'offline_page'     => env('PWA_OFFLINE_PAGE', '/offline'),
		'apple_icon'       => env('PWA_APPLE_ICON', '/icons/apple-touch-icon.png'),
		'categories'       => ['shopping', 'lifestyle'],
	),

	/*
	|--------------------------------------------------------------------------
	| i18n & Market (Faz 9)
	|--------------------------------------------------------------------------
	*/

	'i18n' => array(
		'locales'          => explode(',', env('THEME_LOCALES', 'tr,en,de,ar,ru')),
		'rtl_locales'      => ['ar', 'he', 'fa', 'ur'],
		'currencies'       => explode(',', env('THEME_CURRENCIES', 'TRY,USD,EUR,GBP')),
		'default_currency' => env('THEME_DEFAULT_CURRENCY', 'TRY'),
		'currency_api_url' => env('THEME_CURRENCY_API_URL', ''), // Döviz kuru API
	),

	/*
	|--------------------------------------------------------------------------
	| Push Notifications (Faz 7)
	|--------------------------------------------------------------------------
	*/

	'push' => array(
		'vapid_public'  => env('VAPID_PUBLIC_KEY', ''),
		'vapid_private' => env('VAPID_PRIVATE_KEY', ''),
		'vapid_subject' => env('VAPID_SUBJECT', 'mailto:admin@example.com'),
	),

);

