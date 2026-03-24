<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * SocialProofSection — Sosyal kanıt bildirimleri.
 *
 * Gösterir:
 * - "X kişi şu an bakıyor"
 * - "Son 1 saatte N satıldı"
 * - "K kişi son 24 saatte inceledi"
 */
class SocialProofSection extends AbstractSection
{
    public function type(): string { return 'social-proof'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'Sosyal Kanıt',
            'settings' => [
                ['type' => 'checkbox', 'id' => 'show_viewers',  'label' => 'İzleyici sayısı göster', 'default' => true],
                ['type' => 'checkbox', 'id' => 'show_sold',     'label' => 'Satış sayısı göster', 'default' => true],
                ['type' => 'range',    'id' => 'viewers_min',   'label' => 'Min izleyici', 'min' => 1, 'max' => 50,  'default' => 5],
                ['type' => 'range',    'id' => 'viewers_max',   'label' => 'Max izleyici', 'min' => 10,'max' => 200, 'default' => 28],
                ['type' => 'range',    'id' => 'sold_min',      'label' => 'Min satış', 'min' => 1, 'max' => 50,  'default' => 3],
                ['type' => 'range',    'id' => 'sold_max',      'label' => 'Max satış', 'min' => 5, 'max' => 500, 'default' => 47],
                ['type' => 'range',    'id' => 'refresh_secs',  'label' => 'Yenileme (sn)', 'min' => 10, 'max' => 120, 'default' => 30],
                ['type' => 'select',   'id' => 'style',         'label' => 'Stil', 'default' => 'badge',
                 'options' => [['value'=>'badge','label'=>'Rozet'],['value'=>'inline','label'=>'Satır içi']]],
            ],
            'presets' => [['name' => 'Sosyal Kanıt', 'category' => 'Conversion']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'show_viewers' => $settings['show_viewers'] ?? true,
            'show_sold'    => $settings['show_sold'] ?? true,
            'viewers_min'  => (int)($settings['viewers_min'] ?? 5),
            'viewers_max'  => (int)($settings['viewers_max'] ?? 28),
            'sold_min'     => (int)($settings['sold_min'] ?? 3),
            'sold_max'     => (int)($settings['sold_max'] ?? 47),
            'refresh_secs' => (int)($settings['refresh_secs'] ?? 30),
            'style'        => $settings['style'] ?? 'badge',
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('social-proof', $data);
    }
}
