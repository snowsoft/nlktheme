<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * UrgencyBadgeSection — Stok aciliyeti ve FOMO badge'i.
 *
 * "Son X adet kaldı!", "Bu ürünü X kişi sepete ekledi" gibi
 * aciliyet mesajları gösterir.
 */
class UrgencyBadgeSection extends AbstractSection
{
    public function type(): string { return 'urgency-badge'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'Stok Aciliyet Rozeti',
            'settings' => [
                ['type' => 'checkbox', 'id' => 'show_stock_count',  'label' => 'Stok sayısı göster', 'default' => true],
                ['type' => 'checkbox', 'id' => 'show_cart_count',   'label' => 'Sepete ekleyen sayısı', 'default' => true],
                ['type' => 'range',    'id' => 'low_stock_threshold','label' => 'Düşük stok eşiği', 'min' => 1, 'max' => 20, 'default' => 5],
                ['type' => 'text',     'id' => 'low_stock_msg',     'label' => 'Düşük stok mesajı', 'default' => 'Son {count} adet kaldı!'],
                ['type' => 'text',     'id' => 'cart_msg',          'label' => 'Sepet mesajı', 'default' => '{count} kişi bu ürünü sepete ekledi'],
                ['type' => 'text',     'id' => 'sold_msg',          'label' => 'Satış mesajı', 'default' => 'Bugün {count} adet satıldı'],
                ['type' => 'color',    'id' => 'badge_color',       'label' => 'Rozet rengi', 'default' => '#e53935'],
                ['type' => 'range',    'id' => 'cart_count_min',    'label' => 'Min sepet sayısı göster', 'min' => 2, 'max' => 50, 'default' => 8],
                ['type' => 'range',    'id' => 'cart_count_max',    'label' => 'Max sepet sayısı göster', 'min' => 10,'max' => 500,'default' => 124],
            ],
            'presets' => [['name' => 'Stok Aciliyeti', 'category' => 'Conversion']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'show_stock_count'   => (bool)($settings['show_stock_count'] ?? true),
            'show_cart_count'    => (bool)($settings['show_cart_count'] ?? true),
            'low_stock_threshold'=> (int)($settings['low_stock_threshold'] ?? 5),
            'low_stock_msg'      => $settings['low_stock_msg'] ?? 'Son {count} adet kaldı!',
            'cart_msg'           => $settings['cart_msg'] ?? '{count} kişi bu ürünü sepete ekledi',
            'sold_msg'           => $settings['sold_msg'] ?? 'Bugün {count} adet satıldı',
            'badge_color'        => $settings['badge_color'] ?? '#e53935',
            'cart_count_min'     => (int)($settings['cart_count_min'] ?? 8),
            'cart_count_max'     => (int)($settings['cart_count_max'] ?? 124),
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('urgency-badge', $data);
    }
}
