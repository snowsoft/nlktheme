<?php

namespace Nlk\Theme\FlexPage\Contracts;

interface Section
{
    /**
     * Unique section type identifier (e.g. 'hero', 'featured-products').
     */
    public function type(): string;

    /**
     * FlexPage-compatible section schema definition.
     * Describes settings, blocks, presets, etc.
     *
     * @return array<string, mixed>
     */
    public function schema(): array;

    /**
     * Data source: 'mysql' | 'api' | 'hybrid' | 'static'
     */
    public function dataSource(): string;

    /**
     * Fetch hydrated data for this section.
     *
     * @param  array<string, mixed>  $settings  Section settings from DB/JSON
     * @param  string|null  $tenantId
     * @return array<string, mixed>
     */
    public function fetchData(array $settings, ?string $tenantId = null): array;

    /**
     * Render the section to HTML.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $data  Pre-fetched data (from fetchData)
     * @return string
     */
    public function render(array $settings, array $blocks, array $data): string;
}
