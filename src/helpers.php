<?php

if (!function_exists('theme')){
	/**
	 * Get the theme instance.
	 *
	 * @param  string  $themeName
	 * @param  string  $layoutName
	 * @return \Nlk\Theme\Theme
	 */
	function theme($themeName = null, $layoutName = null){
		$theme = app('theme');

		if ($themeName){
			$theme->theme($themeName);
		}

		if ($layoutName){
			$theme->layout($layoutName);
		}

		return $theme;
	}
}

if (!function_exists('protectEmail')){
	/**
	 * Protect the Email address against bots or spiders that 
	 * index or harvest addresses for sending you spam.
	 *
	 * @param  string  $email
	 * @return string
	 */
	function protectEmail($email) {
		$p = str_split(trim($email));
		$new_mail = '';

		foreach ($p as $val) {
			$new_mail .= '&#'.ord($val).';';
		}

		return $new_mail;
	}
}


if (!function_exists('meta_init')){
	/**
	 * Returns common metadata
	 *
	 * @return string
	 */
	function meta_init() {
		return '<meta charset="utf-8">'.
		'<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">'.
		'<meta name="viewport" content="width=device-width, initial-scale=1">';
	}
}

if (!function_exists('theme_asset')){
	/**
	 * Get theme asset URL.
	 *
	 * @param  string $path
	 * @return string
	 */
	function theme_asset($path) {
		return \Nlk\Theme\Helpers\ThemeHelper::assetUrl($path);
	}
}

if (!function_exists('theme_css')){
	/**
	 * Include a CSS file from theme.
	 *
	 * @param  string $path
	 * @param  string $media
	 * @return string
	 */
	function theme_css($path, $media = 'all') {
		return \Nlk\Theme\Helpers\ThemeHelper::css($path, $media);
	}
}

if (!function_exists('theme_js')){
	/**
	 * Include a JavaScript file from theme.
	 *
	 * @param  string $path
	 * @param  boolean $defer
	 * @return string
	 */
	function theme_js($path, $defer = false) {
		return \Nlk\Theme\Helpers\ThemeHelper::js($path, $defer);
	}
}

if (!function_exists('theme_image')){
	/**
	 * Get theme image tag.
	 *
	 * @param  string $path
	 * @param  string $alt
	 * @param  array  $attributes
	 * @return string
	 */
	function theme_image($path, $alt = '', $attributes = []) {
		return \Nlk\Theme\Helpers\ThemeHelper::image($path, $alt, $attributes);
	}
}

if (!function_exists('active_class')){
	/**
	 * Get active class for navigation.
	 *
	 * @param  string $path
	 * @param  string $active
	 * @return string
	 */
	function active_class($path, $active = 'active') {
		return \Nlk\Theme\Helpers\ThemeHelper::active($path, $active);
	}
}

if (!function_exists('theme_truncate')){
	/**
	 * Truncate string with ellipsis.
	 *
	 * @param  string  $string
	 * @param  integer $limit
	 * @param  string  $append
	 * @return string
	 */
	function theme_truncate($string, $limit = 100, $append = '...') {
		return \Nlk\Theme\Helpers\ThemeHelper::truncate($string, $limit, $append);
	}
}

if (!function_exists('theme_cache_clear')){
	/**
	 * Clear theme cache.
	 *
	 * @param  string|null $theme
	 * @return void
	 */
	function theme_cache_clear($theme = null) {
		if (function_exists('theme')) {
			if ($theme) {
				theme()->clearThemeCache($theme);
			} else {
				theme()->clearCache();
			}
		}
	}
}

if (!function_exists('has_theme_view')){
	/**
	 * Check if view exists in theme.
	 *
	 * @param  string $view
	 * @return boolean
	 */
	function has_theme_view($view) {
		return \Nlk\Theme\Helpers\ThemeHelper::hasView($view);
	}
}

if (!function_exists('render_if_exists')){
	/**
	 * Render a partial if it exists.
	 *
	 * @param  string $view
	 * @param  array  $args
	 * @param  mixed  $default
	 * @return string
	 */
	function render_if_exists($view, $args = [], $default = '') {
		return \Nlk\Theme\Helpers\ThemeHelper::renderIfExists($view, $args, $default);
	}
}

if (!function_exists('time_ago')){
	/**
	 * Get human readable time difference.
	 *
	 * @param  datetime $datetime
	 * @return string
	 */
	function time_ago($datetime) {
		return \Nlk\Theme\Helpers\ThemeHelper::timeAgo($datetime);
	}
}

if (!function_exists('number_format_short')){
	/**
	 * Format number with suffix.
	 *
	 * @param  integer $number
	 * @return string
	 */
	function number_format_short($number) {
		return \Nlk\Theme\Helpers\ThemeHelper::numberFormat($number);
	}
}

if (!function_exists('theme_section')){
	/**
	 * Get section content.
	 *
	 * @param  string $section
	 * @param  string $default
	 * @return string
	 */
	function theme_section($section, $default = '') {
		return theme()->getSection($section, $default);
	}
}

if (!function_exists('has_theme_section')){
	/**
	 * Check if section exists.
	 *
	 * @param  string $section
	 * @return boolean
	 */
	function has_theme_section($section) {
		return theme()->hasSection($section);
	}
}

if (!function_exists('theme_component')){
	/**
	 * Register or render a component.
	 *
	 * @param  string $name
	 * @param  string|null $view
	 * @param  array  $data
	 * @return mixed
	 */
	function theme_component($name, $view = null, $data = []) {
		if ($view) {
			// Register component
			theme()->component($name, $view);
			return null;
		}
		// Render component
		return theme()->renderComponent($name, $data);
	}
}

if (!function_exists('theme_set')){
	/**
	 * Set theme data.
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @return object
	 */
	function theme_set($key, $value) {
		return theme()->setData($key, $value);
	}
}

if (!function_exists('theme_get')){
	/**
	 * Get theme data.
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	function theme_get($key, $default = null) {
		return theme()->getData($key, $default);
	}
}

if (!function_exists('theme_stack')){
	/**
	 * Get stack content.
	 *
	 * @param  string $name
	 * @return string
	 */
	function theme_stack($name) {
		return theme()->getStack($name);
	}
}

if (!function_exists('theme_security_escape')){
	/**
	 * Escape output to prevent XSS attacks.
	 *
	 * @param  mixed $value
	 * @return string
	 */
	function theme_security_escape($value) {
		return \Nlk\Theme\Security\SecurityHelper::escape($value);
	}
}

if (!function_exists('theme_security_sanitize')){
	/**
	 * Sanitize HTML content.
	 *
	 * @param  string $html
	 * @param  array  $allowedTags
	 * @return string
	 */
	function theme_security_sanitize($html, $allowedTags = null) {
		return \Nlk\Theme\Security\SecurityHelper::sanitizeHtml($html, $allowedTags);
	}
}

if (!function_exists('theme_livewire')){
	/**
	 * Render Livewire component.
	 *
	 * @param  string $component
	 * @param  array  $params
	 * @return string
	 */
	function theme_livewire($component, array $params = []) {
		return \Nlk\Theme\Support\LivewireSupport::renderComponent($component, $params);
	}
}

if (!function_exists('theme_react')){
	/**
	 * Render React component.
	 *
	 * @param  string $component
	 * @param  array  $props
	 * @param  string $id
	 * @return string
	 */
	function theme_react($component, array $props = [], $id = null) {
		return \Nlk\Theme\Support\ReactSSRSupport::renderComponent($component, $props, $id);
	}
}

if (!function_exists('theme_inertia')){
	/**
	 * Render Inertia page.
	 *
	 * @param  string $component
	 * @param  array  $props
	 * @return mixed
	 */
	function theme_inertia($component, array $props = []) {
		return \Nlk\Theme\Support\InertiaSupport::render($component, $props);
	}
}

if (!function_exists('theme_schema')){
	/**
	 * Generate JSON-LD schema script tag.
	 *
	 * @param  array $schema
	 * @return string
	 */
	function theme_schema(array $schema) {
		return \Nlk\Theme\SEO\SchemaGenerator::jsonLd($schema);
	}
}

if (!function_exists('theme_product_schema')){
	/**
	 * Generate Product schema.
	 *
	 * @param  array $data
	 * @return string
	 */
	function theme_product_schema(array $data) {
		$schema = \Nlk\Theme\SEO\ProductSchema::generate($data);
		return \Nlk\Theme\SEO\SchemaGenerator::jsonLd($schema);
	}
}

if (!function_exists('theme_organization_schema')){
	/**
	 * Generate Organization schema.
	 *
	 * @param  array $data
	 * @return string
	 */
	function theme_organization_schema(array $data) {
		$schema = \Nlk\Theme\SEO\SchemaGenerator::organization($data);
		return \Nlk\Theme\SEO\SchemaGenerator::jsonLd($schema);
	}
}

if (!function_exists('theme_breadcrumb_schema')){
	/**
	 * Generate BreadcrumbList schema.
	 *
	 * @param  array $items
	 * @return string
	 */
	function theme_breadcrumb_schema(array $items) {
		$schema = \Nlk\Theme\SEO\SchemaGenerator::breadcrumbList($items);
		return \Nlk\Theme\SEO\SchemaGenerator::jsonLd($schema);
	}
}

if (!function_exists('theme_article_schema')){
	/**
	 * Generate Article schema.
	 *
	 * @param  array $data
	 * @return string
	 */
	function theme_article_schema(array $data) {
		$schema = \Nlk\Theme\SEO\SchemaGenerator::article($data);
		return \Nlk\Theme\SEO\SchemaGenerator::jsonLd($schema);
	}
}

if (!function_exists('theme_video_schema')){
	/**
	 * Generate Video schema.
	 *
	 * @param  array $data
	 * @return string
	 */
	function theme_video_schema(array $data) {
		$schema = \Nlk\Theme\SEO\SchemaGenerator::video($data);
		return \Nlk\Theme\SEO\SchemaGenerator::jsonLd($schema);
	}
}