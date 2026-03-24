<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * TrustBadgeSection — Güven rozet ve logoları.
 *
 * SSL, güvenli ödeme, kargo garantisi, iade garantisi gibi
 * güven unsurlarını satış artırıcı rozetlerle gösterir.
 */
class TrustBadgeSection extends AbstractSection
{
    public function type(): string { return 'trust-badges'; }
    public function dataSource(): string { return 'static'; }

    // Varsayılan rozetler
    protected array $defaultBadges = [
        ['icon' => 'shield-check',  'title' => '256-bit SSL',         'desc' => 'Güvenli bağlantı'],
        ['icon' => 'credit-card',   'title' => 'Güvenli Ödeme',       'desc' => 'iyzico, Stripe, PayTR'],
        ['icon' => 'truck',         'title' => 'Hızlı Teslimat',      'desc' => '1-3 iş günü'],
        ['icon' => 'refresh',       'title' => '30 Gün İade',         'desc' => 'Koşulsuz iade garantisi'],
        ['icon' => 'headset',       'title' => '7/24 Destek',         'desc' => 'Canlı yardım'],
        ['icon' => 'tag',           'title' => 'En İyi Fiyat',        'desc' => 'Fiyat farkı iadesi'],
    ];

    public function schema(): array
    {
        return [
            'name' => 'Güven Rozetleri',
            'settings' => [
                ['type' => 'text',   'id' => 'title',    'label' => 'Başlık (opsiyonel)', 'default' => ''],
                ['type' => 'select', 'id' => 'layout',   'label' => 'Düzen', 'default' => 'horizontal',
                 'options' => [['value'=>'horizontal','label'=>'Yatay'],['value'=>'grid','label'=>'Izgara']]],
                ['type' => 'select', 'id' => 'icon_set', 'label' => 'İkon seti', 'default' => 'heroicons',
                 'options' => [['value'=>'heroicons','label'=>'HeroIcons'],['value'=>'fontawesome','label'=>'FontAwesome']]],
                ['type' => 'select', 'id' => 'style',    'label' => 'Stil', 'default' => 'minimal',
                 'options' => [['value'=>'minimal','label'=>'Minimal'],['value'=>'card','label'=>'Kart'],['value'=>'icon-only','label'=>'Sadece ikon']]],

                // Rozet 1
                ['type' => 'header', 'content' => 'Rozet 1'],
                ['type' => 'checkbox', 'id' => 'badge1_enabled', 'label' => 'Aktif', 'default' => true],
                ['type' => 'text',     'id' => 'badge1_icon',    'label' => 'İkon',  'default' => 'shield-check'],
                ['type' => 'text',     'id' => 'badge1_title',   'label' => 'Başlık','default' => '256-bit SSL'],
                ['type' => 'text',     'id' => 'badge1_desc',    'label' => 'Açıklama', 'default' => 'Güvenli bağlantı'],

                // Rozet 2
                ['type' => 'header', 'content' => 'Rozet 2'],
                ['type' => 'checkbox', 'id' => 'badge2_enabled', 'label' => 'Aktif', 'default' => true],
                ['type' => 'text',     'id' => 'badge2_icon',    'label' => 'İkon',  'default' => 'credit-card'],
                ['type' => 'text',     'id' => 'badge2_title',   'label' => 'Başlık','default' => 'Güvenli Ödeme'],
                ['type' => 'text',     'id' => 'badge2_desc',    'label' => 'Açıklama', 'default' => 'iyzico & Stripe'],

                // Rozet 3
                ['type' => 'header', 'content' => 'Rozet 3'],
                ['type' => 'checkbox', 'id' => 'badge3_enabled', 'label' => 'Aktif', 'default' => true],
                ['type' => 'text',     'id' => 'badge3_icon',    'label' => 'İkon',  'default' => 'truck'],
                ['type' => 'text',     'id' => 'badge3_title',   'label' => 'Başlık','default' => 'Hızlı Teslimat'],
                ['type' => 'text',     'id' => 'badge3_desc',    'label' => 'Açıklama', 'default' => '1-3 iş günü'],

                // Rozet 4
                ['type' => 'header', 'content' => 'Rozet 4'],
                ['type' => 'checkbox', 'id' => 'badge4_enabled', 'label' => 'Aktif', 'default' => true],
                ['type' => 'text',     'id' => 'badge4_icon',    'label' => 'İkon',  'default' => 'refresh'],
                ['type' => 'text',     'id' => 'badge4_title',   'label' => 'Başlık','default' => '30 Gün İade'],
                ['type' => 'text',     'id' => 'badge4_desc',    'label' => 'Açıklama', 'default' => 'Koşulsuz iade'],
            ],
            'presets' => [['name' => 'Güven Rozetleri', 'category' => 'Trust']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $badges = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($settings["badge{$i}_enabled"])) {
                $badges[] = [
                    'icon'  => $settings["badge{$i}_icon"]  ?? $this->defaultBadges[$i - 1]['icon'],
                    'title' => $settings["badge{$i}_title"] ?? $this->defaultBadges[$i - 1]['title'],
                    'desc'  => $settings["badge{$i}_desc"]  ?? $this->defaultBadges[$i - 1]['desc'],
                ];
            }
        }

        if (empty($badges)) {
            $badges = $this->defaultBadges;
        }

        return [
            'title'   => $settings['title'] ?? '',
            'layout'  => $settings['layout'] ?? 'horizontal',
            'style'   => $settings['style'] ?? 'minimal',
            'icon_set'=> $settings['icon_set'] ?? 'heroicons',
            'badges'  => $badges,
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('trust-badges', $data);
    }
}
