<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * ShoppableImageSection — Tıklanabilir ürün noktaları olan görsel.
 *
 * Görsel üzerinde hotspot'lar tanımlanır.
 * Her hotspot tıklandığında ürün kartı açılır.
 */
class ShoppableImageSection extends AbstractSection
{
    public function type(): string { return 'shoppable-image'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'Shoppable Görsel',
            'settings' => [
                ['type' => 'image_picker', 'id' => 'image',   'label' => 'Arka plan görseli', 'default' => ''],
                ['type' => 'text',         'id' => 'cdn_image_id', 'label' => 'CDN Görsel ID', 'default' => ''],
                ['type' => 'text',         'id' => 'title',   'label' => 'Başlık',  'default' => 'Bu Görünümü Satın Al'],
                ['type' => 'select',       'id' => 'hotspot_style', 'label' => 'Hotspot stili', 'default' => 'pulse',
                 'options' => [['value'=>'pulse','label'=>'Nabız animasyonu'],['value'=>'pin','label'=>'Pin'],['value'=>'dot','label'=>'Nokta']]],
            ],
            'blocks' => [
                [
                    'type' => 'hotspot',
                    'name' => 'Hotspot',
                    'settings' => [
                        ['type' => 'range',  'id' => 'x_pos',      'label' => 'X pozisyon (%)', 'min' => 0, 'max' => 100, 'step' => 1, 'default' => 50],
                        ['type' => 'range',  'id' => 'y_pos',      'label' => 'Y pozisyon (%)', 'min' => 0, 'max' => 100, 'step' => 1, 'default' => 50],
                        ['type' => 'text',   'id' => 'product_id', 'label' => 'Ürün ID', 'default' => ''],
                        ['type' => 'text',   'id' => 'product_url','label' => 'Ürün URL', 'default' => ''],
                        ['type' => 'text',   'id' => 'label',      'label' => 'Etiket', 'default' => 'Ürünü Gör'],
                    ],
                ],
            ],
            'presets' => [['name' => 'Shoppable Görsel', 'category' => 'E-Commerce']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $cdnId    = $settings['cdn_image_id'] ?? '';
        $imageUrl = $cdnId
            ? config('theme.cdn.url') . '/api/image/' . $cdnId . '?w=1200&format=auto&fit=cover'
            : ($settings['image'] ?? '');

        return [
            'title'         => $settings['title'] ?? 'Bu Görünümü Satın Al',
            'image_url'     => $imageUrl,
            'cdn_image_id'  => $cdnId,
            'hotspot_style' => $settings['hotspot_style'] ?? 'pulse',
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        // Hotspot'ları blocks'tan oluştur
        $hotspots = array_map(fn($b) => [
            'x'          => (int)($b['settings']['x_pos'] ?? 50),
            'y'          => (int)($b['settings']['y_pos'] ?? 50),
            'product_id' => $b['settings']['product_id'] ?? '',
            'product_url'=> $b['settings']['product_url'] ?? '',
            'label'      => $b['settings']['label'] ?? 'Ürünü Gör',
        ], $blocks);

        $data['hotspots'] = array_values($hotspots);
        return $this->renderView('shoppable-image', $data);
    }
}
