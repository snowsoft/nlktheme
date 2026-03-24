<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

class CollectionListSection extends AbstractSection
{
    public function type(): string { return 'collection-list'; }
    public function dataSource(): string { return 'mysql'; }

    public function schema(): array
    {
        return [
            'name' => 'Kategori Listesi',
            'settings' => [
                ['type' => 'text',  'id' => 'title',   'label' => 'Başlık', 'default' => 'Koleksiyonlar'],
                ['type' => 'range', 'id' => 'columns', 'label' => 'Sütun', 'min' => 2, 'max' => 6, 'step' => 1, 'default' => 4],
                ['type' => 'range', 'id' => 'limit',   'label' => 'Limit',  'min' => 4, 'max' => 24, 'step' => 4, 'default' => 8],
            ],
            'presets' => [['name' => 'Kategori Listesi', 'category' => 'Collection']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'title'       => $settings['title'] ?? 'Koleksiyonlar',
            'collections' => $this->mysql()->fetchCategories(['tenant_id' => $tenantId, 'limit' => $settings['limit'] ?? 8]),
            'columns'     => $settings['columns'] ?? 4,
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('collection-list', $data);
    }
}
