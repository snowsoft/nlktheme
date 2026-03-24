<?php

namespace Nlk\Theme\FlexPage;

use Nlk\Theme\FlexPage\Contracts\Section;
use Nlk\Theme\FlexPage\DataAdapters\MysqlAdapter;
use Nlk\Theme\FlexPage\DataAdapters\ApiAdapter;
use Illuminate\Support\Facades\View;

/**
 * Base class for all sections.
 * Subclasses override type(), schema(), fetchData() and render().
 */
abstract class AbstractSection implements Section
{
    final public function dataSource(): string
    {
        return 'static'; // override in subclass: 'mysql' | 'api' | 'hybrid' | 'static'
    }

    // ─── Data Helpers ─────────────────────────────────────────────────────────

    protected function mysql(): MysqlAdapter
    {
        return app(MysqlAdapter::class);
    }

    protected function api(): ApiAdapter
    {
        return app(ApiAdapter::class);
    }

    // ─── Render Helper ────────────────────────────────────────────────────────

    /**
     * Render a Blade view from the active theme's sections directory.
     * Falls back to the package's built-in view.
     *
     * @param  array<string, mixed>  $data
     */
    protected function renderView(string $view, array $data = []): string
    {
        // Try theme namespace first: theme::{active}.sections.{view}
        $themeView = 'theme::' . $this->type() . '.' . $view;

        if (View::exists($themeView)) {
            return View::make($themeView, $data)->render();
        }

        // Fallback: built-in package view
        $packageView = 'nlktheme::sections.' . $view;

        if (View::exists($packageView)) {
            return View::make($packageView, $data)->render();
        }

        return '';
    }

    /**
     * Default: no data needed for static sections.
     *
     * @param  array<string, mixed>  $settings
     */
    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [];
    }

    /**
     * Default schema: empty (override in subclass for proper FlexPage section schema).
     */
    public function schema(): array
    {
        return [
            'name'     => $this->type(),
            'settings' => [],
            'blocks'   => [],
            'presets'  => [],
        ];
    }
}
