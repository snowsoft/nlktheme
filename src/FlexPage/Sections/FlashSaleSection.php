<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * FlashSaleSection — Flash sale / kampanya sayacı.
 *
 * Countdown + indirimli fiyat gösterimi, ürün listesi ile.
 */
class FlashSaleSection extends AbstractSection
{
    public function type(): string { return 'flash-sale'; }
    public function dataSource(): string { return 'mysql'; }

    public function schema(): array
    {
        return [
            'name' => 'Flash Sale',
            'settings' => [
                ['type' => 'text',     'id' => 'title',       'label' => 'Başlık',        'default' => '⚡ Flash İndirim!'],
                ['type' => 'text',     'id' => 'subtitle',    'label' => 'Alt başlık',    'default' => 'Sınırlı süre, sınırlı stok'],
                ['type' => 'text',     'id' => 'end_date',    'label' => 'Bitiş (YYYY-MM-DD HH:MM)', 'default' => ''],
                ['type' => 'range',    'id' => 'products_to_show', 'label' => 'Ürün sayısı', 'min' => 2, 'max' => 12, 'step' => 2, 'default' => 4],
                ['type' => 'checkbox', 'id' => 'show_countdown',   'label' => 'Geri sayım göster', 'default' => true],
                ['type' => 'checkbox', 'id' => 'show_discount_badge', 'label' => '%İndirim rozeti göster', 'default' => true],
                ['type' => 'color',    'id' => 'accent_color', 'label' => 'Vurgu rengi', 'default' => '#e53935'],
                ['type' => 'select',   'id' => 'source',       'label' => 'Veri kaynağı', 'default' => 'mysql',
                 'options' => [['value'=>'mysql','label'=>'MySQL'],['value'=>'api','label'=>'API']]],
            ],
            'presets' => [['name' => 'Flash Sale', 'category' => 'Promotional']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $source   = $settings['source'] ?? 'mysql';
        $limit    = (int)($settings['products_to_show'] ?? 4);
        $products = [];

        try {
            if ($source === 'api') {
                $products = $this->api()->withTenant($tenantId ?? '')
                    ->fetchProducts(['flash_sale' => 1, 'per_page' => $limit]);
            } else {
                $products = $this->mysql()->fetchProducts([
                    'tenant_id'  => $tenantId,
                    'flash_sale' => true,
                    'limit'      => $limit,
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FlashSaleSection data error', ['error' => $e->getMessage()]);
        }

        return [
            'title'           => $settings['title'] ?? '⚡ Flash İndirim!',
            'subtitle'        => $settings['subtitle'] ?? '',
            'end_date'        => $settings['end_date'] ?? '',
            'show_countdown'  => (bool)($settings['show_countdown'] ?? true),
            'show_discount_badge' => (bool)($settings['show_discount_badge'] ?? true),
            'accent_color'    => $settings['accent_color'] ?? '#e53935',
            'products'        => $products,
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('flash-sale', $data);
    }
}
