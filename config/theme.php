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

);
