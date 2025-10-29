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

);
