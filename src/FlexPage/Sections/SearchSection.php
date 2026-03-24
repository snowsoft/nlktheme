<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * SearchSection — AJAX arama kutusu ve anlık öneriler.
 *
 * Yazarken ürün/kategori/blog öneri. Algolia/Scout veya
 * özel REST endpoint ile çalışır.
 */
class SearchSection extends AbstractSection
{
    public function type(): string { return 'ajax-search'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'AJAX Arama',
            'settings' => [
                ['type' => 'text',     'id' => 'placeholder',  'label' => 'Placeholder',    'default' => 'Ürün, kategori veya marka ara...'],
                ['type' => 'text',     'id' => 'api_url',      'label' => 'Arama API URL',  'default' => '/api/v1/search'],
                ['type' => 'range',    'id' => 'min_chars',    'label' => 'Min karakter',   'min' => 1, 'max' => 5, 'default' => 2],
                ['type' => 'range',    'id' => 'debounce_ms',  'label' => 'Debounce (ms)',  'min' => 100, 'max' => 1000, 'step' => 100, 'default' => 300],
                ['type' => 'range',    'id' => 'max_results',  'label' => 'Max sonuç',      'min' => 3, 'max' => 20, 'default' => 8],
                ['type' => 'checkbox', 'id' => 'show_products',   'label' => 'Ürünler göster',   'default' => true],
                ['type' => 'checkbox', 'id' => 'show_categories', 'label' => 'Kategoriler göster','default' => true],
                ['type' => 'checkbox', 'id' => 'show_blog',       'label' => 'Blog göster',       'default' => false],
                ['type' => 'checkbox', 'id' => 'show_thumbnail',  'label' => 'Ürün görseli',      'default' => true],
                ['type' => 'checkbox', 'id' => 'show_price',      'label' => 'Fiyat göster',       'default' => true],
                ['type' => 'text',     'id' => 'results_page_url','label' => 'Tüm sonuçlar URL',  'default' => '/arama'],
            ],
            'presets' => [['name' => 'AJAX Arama', 'category' => 'Navigation']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'placeholder'      => $settings['placeholder']  ?? 'Ürün ara...',
            'api_url'          => $settings['api_url']       ?? '/api/v1/search',
            'min_chars'        => (int)($settings['min_chars'] ?? 2),
            'debounce_ms'      => (int)($settings['debounce_ms'] ?? 300),
            'max_results'      => (int)($settings['max_results'] ?? 8),
            'show_products'    => (bool)($settings['show_products'] ?? true),
            'show_categories'  => (bool)($settings['show_categories'] ?? true),
            'show_blog'        => (bool)($settings['show_blog'] ?? false),
            'show_thumbnail'   => (bool)($settings['show_thumbnail'] ?? true),
            'show_price'       => (bool)($settings['show_price'] ?? true),
            'results_page_url' => $settings['results_page_url'] ?? '/arama',
            'tenant_id'        => $tenantId ?? '',
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('ajax-search', $data);
    }
}
