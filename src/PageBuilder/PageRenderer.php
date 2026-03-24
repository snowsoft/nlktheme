<?php

namespace Nlk\Theme\PageBuilder;

use Nlk\Theme\PageBuilder\PageBuilder;

/**
 * PageRenderer — standalone render helper.
 * Delegates to PageBuilder for full section rendering.
 */
class PageRenderer
{
    public function __construct(
        private readonly PageBuilder $builder,
    ) {}

    /**
     * Render all page sections to HTML.
     */
    public function render(string $pageKey, string $tenantId): string
    {
        return $this->builder->renderPage($pageKey, $tenantId);
    }

    /**
     * Render a single named section from a page.
     */
    public function renderSection(string $pageKey, string $tenantId, string $sectionId): string
    {
        $page     = $this->builder->loadPage($pageKey, $tenantId);
        $sections = $page['sections'] ?? [];
        $row      = $sections[$sectionId] ?? null;

        if (!$row || ($row['disabled'] ?? false)) {
            return '';
        }

        $registry = app(\Nlk\Theme\FlexPage\SectionRegistry::class);

        if (!$registry->has($row['type'])) {
            return '';
        }

        $section = $registry->resolve($row['type']);
        $data    = $section->fetchData($row['settings'] ?? [], $tenantId);

        return $section->render($row['settings'] ?? [], $row['block_order'] ?? [], $data);
    }
}
