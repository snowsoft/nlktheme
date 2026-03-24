<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * RecentlyViewedSection — Son gezilen ürünler.
 *
 * Kullanıcının localStorage'ında saklanan ürün ID'lerini gösterir.
 * Server-side'da herhangi bir DB işlemi gerekmez; JS tarafından render edilir.
 */
class RecentlyViewedSection extends AbstractSection
{
    public function type(): string { return 'recently-viewed'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'Son Gezilen Ürünler',
            'settings' => [
                ['type' => 'text',     'id' => 'title',   'label' => 'Başlık',       'default' => 'Son Bakılan Ürünler'],
                ['type' => 'range',    'id' => 'limit',   'label' => 'Max ürün',     'min' => 2, 'max' => 12, 'step' => 2, 'default' => 6],
                ['type' => 'checkbox', 'id' => 'hide_current', 'label' => 'Mevcut ürünü gizle', 'default' => true],
                ['type' => 'text',     'id' => 'api_url', 'label' => 'Ürün API URL', 'default' => '/api/v1/products'],
            ],
            'presets' => [['name' => 'Son Gezilen Ürünler', 'category' => 'E-Commerce']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'title'       => $settings['title'] ?? 'Son Bakılan Ürünler',
            'limit'       => (int)($settings['limit'] ?? 6),
            'hide_current'=> (bool)($settings['hide_current'] ?? true),
            'api_url'     => $settings['api_url'] ?? '/api/v1/products',
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('recently-viewed', $data);
    }
}
