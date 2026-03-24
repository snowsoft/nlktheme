<?php

namespace Nlk\Theme\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string renderPage(string $pageKey, string $tenantId)
 * @method static array  loadPage(string $pageKey, string $tenantId)
 * @method static \Nlk\Theme\Database\Models\ThemePageSetting savePage(string $pageKey, string $tenantId, array $pageData, array $sections)
 * @method static string exportJson(string $pageKey, string $tenantId)
 * @method static \Nlk\Theme\Database\Models\ThemePageSetting importJson(string $json, string $tenantId)
 * @method static array  getSectionSchemas()
 * @method static void   flushCache(string $pageKey, string $tenantId)
 *
 * @see \Nlk\Theme\PageBuilder\PageBuilder
 */
class PageBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nlk.pagebuilder';
    }
}
