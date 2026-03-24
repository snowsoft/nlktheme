<?php

namespace Nlk\Theme\PageBuilder;

use Nlk\Theme\Database\Models\ThemePageSetting;
use Nlk\Theme\Database\Models\ThemeSectionRow;
use Nlk\Theme\FlexPage\SectionRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * JSON-driven PageBuilder — DB-backed (replaces file-based storage).
 *
 * Usage:
 *   $builder = app(PageBuilder::class);
 *   $html    = $builder->renderPage('home', $tenantId);
 *
 *   // Or from JSON (FlexPage export format):
 *   $builder->importJson($jsonString, $tenantId);
 *   $json = $builder->exportJson('home', $tenantId);
 */
class PageBuilder
{
    private string $cachePrefix = 'theme:page:';

    public function __construct(
        private readonly SectionRegistry $registry,
    ) {}

    // ─── Load ─────────────────────────────────────────────────────────────────

    /**
     * Load page configuration from DB.
     *
     * @return array{sections_order: array, settings: array, sections: ThemeSectionRow[]}
     */
    public function loadPage(string $pageKey, string $tenantId): array
    {
        $ttl      = config('theme.builder.cache_ttl', 300);
        $cacheKey = $this->cachePrefix . $tenantId . ':' . $pageKey;

        return Cache::remember($cacheKey, $ttl, function () use ($pageKey, $tenantId) {
            $page = ThemePageSetting::forTenant($tenantId)
                ->forPage($pageKey)
                ->with('sectionRows')
                ->first();

            if (!$page) {
                return ['sections_order' => [], 'settings' => [], 'sections' => []];
            }

            return [
                'sections_order' => $page->sections_order ?? [],
                'settings'       => $page->settings ?? [],
                'template'       => $page->template,
                'is_published'   => $page->is_published,
                'sections'       => $page->sectionRows->keyBy('section_id')->toArray(),
            ];
        });
    }

    // ─── Save ─────────────────────────────────────────────────────────────────

    /**
     * Save (upsert) a full page configuration.
     *
     * @param  array{sections_order?: array, settings?: array, template?: string}  $pageData
     * @param  array<string, array{type: string, settings?: array, block_order?: array, disabled?: bool}>  $sections
     */
    public function savePage(
        string $pageKey,
        string $tenantId,
        array  $pageData   = [],
        array  $sections   = [],
    ): ThemePageSetting {
        $page = ThemePageSetting::updateOrCreate(
            ['tenant_id' => $tenantId, 'page_key' => $pageKey],
            [
                'template'       => $pageData['template'] ?? 'index',
                'sections_order' => $pageData['sections_order'] ?? array_keys($sections),
                'settings'       => $pageData['settings'] ?? [],
                'is_published'   => $pageData['is_published'] ?? false,
            ]
        );

        // Sync section rows
        $position = 0;
        $sectionOrder = $pageData['sections_order'] ?? array_keys($sections);

        foreach ($sectionOrder as $sectionId) {
            if (!isset($sections[$sectionId])) {
                continue;
            }

            $sData = $sections[$sectionId];

            ThemeSectionRow::updateOrCreate(
                ['page_settings_id' => $page->id, 'section_id' => $sectionId],
                [
                    'tenant_id'   => $tenantId,
                    'type'        => $sData['type'],
                    'settings'    => $sData['settings'] ?? [],
                    'block_order' => $sData['block_order'] ?? [],
                    'position'    => $position++,
                    'disabled'    => $sData['disabled'] ?? false,
                ]
            );
        }

        $this->flushCache($pageKey, $tenantId);
        return $page;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    /**
     * Render all enabled sections of a page to HTML.
     */
    public function renderPage(string $pageKey, string $tenantId): string
    {
        $page     = $this->loadPage($pageKey, $tenantId);
        $order    = $page['sections_order'] ?? [];
        $sections = $page['sections'] ?? [];
        $html     = '';

        foreach ($order as $sectionId) {
            $row = $sections[$sectionId] ?? null;

            if (!$row || ($row['disabled'] ?? false)) {
                continue;
            }

            $type     = $row['type'] ?? '';
            $settings = $row['settings'] ?? [];
            $blocks   = $row['block_order'] ?? [];

            if (!$this->registry->has($type)) {
                Log::warning("ThemeEngine: Unknown section type [{$type}]", ['page' => $pageKey]);
                continue;
            }

            try {
                $section = $this->registry->resolve($type);
                $data    = $section->fetchData($settings, $tenantId);
                $html   .= $section->render($settings, $blocks, $data);
            } catch (\Throwable $e) {
                Log::error("ThemeEngine: Section render failed [{$type}]", ['error' => $e->getMessage()]);
            }
        }

        return $html;
    }

    // ─── Import / Export (FlexPage JSON) ────────────────────────────

    /**
     * Export a page as a FlexPage JSON string.
     */
    public function exportJson(string $pageKey, string $tenantId): string
    {
        $page = $this->loadPage($pageKey, $tenantId);

        $export = [
            'page_key'       => $pageKey,
            'template'       => $page['template'] ?? 'index',
            'settings'       => $page['settings'] ?? [],
            'sections'       => [],
            'order'          => $page['sections_order'] ?? [],
        ];

        foreach ($page['sections'] as $sectionId => $row) {
            $export['sections'][$sectionId] = [
                'type'        => $row['type'],
                'settings'    => $row['settings'] ?? [],
                'block_order' => $row['block_order'] ?? [],
                'disabled'    => $row['disabled'] ?? false,
            ];
        }

        return json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Import from a FlexPage JSON string and persist to DB.
     */
    public function importJson(string $json, string $tenantId): ThemePageSetting
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $pageKey  = $data['page_key'] ?? 'home';
        $pageData = [
            'template'       => $data['template'] ?? 'index',
            'settings'       => $data['settings'] ?? [],
            'sections_order' => $data['order'] ?? array_keys($data['sections'] ?? []),
        ];

        return $this->savePage($pageKey, $tenantId, $pageData, $data['sections'] ?? []);
    }

    // ─── Schema ───────────────────────────────────────────────────────────────

    /**
     * Get all registered section schemas (for a visual editor / admin).
     */
    public function getSectionSchemas(): array
    {
        $schemas = [];
        foreach ($this->registry->all() as $type => $class) {
            try {
                $schemas[$type] = app($class)->schema();
            } catch (\Throwable) {
                // skip
            }
        }
        return $schemas;
    }

    // ─── Cache ────────────────────────────────────────────────────────────────

    public function flushCache(string $pageKey, string $tenantId): void
    {
        Cache::forget($this->cachePrefix . $tenantId . ':' . $pageKey);
    }

    public function flushTenantCache(string $tenantId): void
    {
        // Redis SCAN yok, prefix based — uygulamada cache tags kullanılabilir
        Cache::flush();
    }
}
