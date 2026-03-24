<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

class FeaturedProductsSection extends AbstractSection
{
    public function type(): string { return 'featured-products'; }

    public function dataSource(): string { return 'hybrid'; }

    public function schema(): array
    {
        return [
            'name' => 'Öne Çıkan Ürünler',
            'settings' => [
                ['type' => 'text',     'id' => 'title',           'label' => 'Başlık',          'default' => 'Öne Çıkan Ürünler'],
                ['type' => 'range',    'id' => 'products_to_show','label' => 'Ürün sayısı',    'min' => 2, 'max' => 24, 'step' => 2, 'default' => 8],
                ['type' => 'select',   'id' => 'columns',         'label' => 'Sütun',           'default' => '4',
                    'options' => [
                        ['value' => '2', 'label' => '2'],
                        ['value' => '3', 'label' => '3'],
                        ['value' => '4', 'label' => '4'],
                    ],
                ],
                ['type' => 'checkbox', 'id' => 'show_price',      'label' => 'Fiyat göster',   'default' => true],
                ['type' => 'checkbox', 'id' => 'show_rating',     'label' => 'Puan göster',    'default' => true],
                ['type' => 'select',   'id' => 'source',          'label' => 'Kaynak',          'default' => 'mysql',
                    'options' => [
                        ['value' => 'mysql', 'label' => 'Veritabanı'],
                        ['value' => 'api',   'label' => 'API'],
                    ],
                ],
            ],
            'presets' => [['name' => 'Öne Çıkan Ürünler', 'category' => 'Products']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $limit  = (int) ($settings['products_to_show'] ?? 8);
        $source = $settings['source'] ?? 'mysql';

        $products = match ($source) {
            'api'   => $this->api()->withTenant($tenantId ?? '')->fetchProducts(['featured' => 1, 'per_page' => $limit]),
            default => $this->mysql()->fetchProducts(['tenant_id' => $tenantId, 'featured' => true, 'limit' => $limit]),
        };

        return [
            'title'    => $settings['title'] ?? 'Öne Çıkan Ürünler',
            'products' => $products,
            'columns'  => $settings['columns'] ?? '4',
            'settings' => $settings,
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('featured-products', $data);
    }
}
