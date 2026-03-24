<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * MiniCartSection — AJAX mini sepet.
 *
 * Sayfa yenilenmeden açılan sepet drawer.
 * Ürün listesi, toplam tutar, ödemeye git butonu içerir.
 * API endpoint ile entegre çalışır.
 */
class MiniCartSection extends AbstractSection
{
    public function type(): string { return 'mini-cart'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'Mini Sepet',
            'settings' => [
                ['type' => 'text',     'id' => 'title',           'label' => 'Başlık',          'default' => 'Sepetim'],
                ['type' => 'text',     'id' => 'empty_msg',       'label' => 'Boş sepet mesajı','default' => 'Sepetiniz boş'],
                ['type' => 'text',     'id' => 'checkout_label',  'label' => 'Ödeme butonu',    'default' => 'Ödemeye Geç'],
                ['type' => 'text',     'id' => 'cart_api_url',    'label' => 'Sepet API URL',   'default' => '/api/v1/cart'],
                ['type' => 'text',     'id' => 'checkout_url',    'label' => 'Ödeme sayfası',   'default' => '/odeme'],
                ['type' => 'checkbox', 'id' => 'show_thumbnail',  'label' => 'Ürün görseli',    'default' => true],
                ['type' => 'checkbox', 'id' => 'show_free_shipping_bar', 'label' => 'Ücretsiz kargo sayacı', 'default' => true],
                ['type' => 'range',    'id' => 'free_shipping_threshold', 'label' => 'Ücretsiz kargo eşiği (₺)', 'min' => 50, 'max' => 1000, 'step' => 50, 'default' => 500],
                ['type' => 'select',   'id' => 'position',        'label' => 'Konum', 'default' => 'right',
                 'options' => [['value'=>'right','label'=>'Sağ'],['value'=>'left','label'=>'Sol']]],
            ],
            'presets' => [['name' => 'Mini Sepet', 'category' => 'E-Commerce']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'title'                   => $settings['title'] ?? 'Sepetim',
            'empty_msg'               => $settings['empty_msg'] ?? 'Sepetiniz boş',
            'checkout_label'          => $settings['checkout_label'] ?? 'Ödemeye Geç',
            'cart_api_url'            => $settings['cart_api_url'] ?? '/api/v1/cart',
            'checkout_url'            => $settings['checkout_url'] ?? '/odeme',
            'show_thumbnail'          => (bool)($settings['show_thumbnail'] ?? true),
            'show_free_shipping_bar'  => (bool)($settings['show_free_shipping_bar'] ?? true),
            'free_shipping_threshold' => (int)($settings['free_shipping_threshold'] ?? 500),
            'position'                => $settings['position'] ?? 'right',
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('mini-cart', $data);
    }
}
