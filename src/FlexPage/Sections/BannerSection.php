<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

class BannerSection extends AbstractSection
{
    public function type(): string { return 'image-banner'; }

    public function dataSource(): string { return 'mysql'; }

    public function schema(): array
    {
        return [
            'name' => 'Görsel Banner',
            'settings' => [
                ['type' => 'text',     'id' => 'title',        'label' => 'Başlık'],
                ['type' => 'textarea', 'id' => 'subtitle',     'label' => 'Alt başlık'],
                ['type' => 'text',     'id' => 'btn_label',    'label' => 'Buton metni', 'default' => 'Keşfet'],
                ['type' => 'url',      'id' => 'btn_link',     'label' => 'Buton bağlantısı'],
                ['type' => 'select',   'id' => 'zone',         'label' => 'Afiş bölgesi', 'default' => 'ana',
                    'options' => [
                        ['value' => 'ana',     'label' => 'Ana sayfa'],
                        ['value' => 'kategori','label' => 'Kategori'],
                        ['value' => 'alt',     'label' => 'Alt banner'],
                    ],
                ],
                ['type' => 'select', 'id' => 'text_align', 'label' => 'Metin hizası', 'default' => 'center',
                    'options' => [
                        ['value' => 'left',   'label' => 'Sol'],
                        ['value' => 'center', 'label' => 'Orta'],
                        ['value' => 'right',  'label' => 'Sağ'],
                    ],
                ],
            ],
            'presets' => [['name' => 'Görsel Banner', 'category' => 'Image']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'banners'  => $this->mysql()->fetchBanners($tenantId, $settings['zone'] ?? null),
            'settings' => $settings,
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('image-banner', $data);
    }
}
