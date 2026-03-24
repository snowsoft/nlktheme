<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * UpsellSection — "Birlikte alınanlar" / "Bunu da beğenebilirsin".
 *
 * Ürün detay sayfasında önerilen tamamlayıcı/alternatif ürünler.
 */
class UpsellSection extends AbstractSection
{
    public function type(): string { return 'upsell'; }
    public function dataSource(): string { return 'mysql'; }

    public function schema(): array
    {
        return [
            'name' => 'Upsell Öneriler',
            'settings' => [
                ['type' => 'text',   'id' => 'title',             'label' => 'Başlık',         'default' => 'Birlikte Alınanlar'],
                ['type' => 'select', 'id' => 'mode',              'label' => 'Mod', 'default' => 'complementary',
                 'options' => [
                     ['value' => 'complementary',  'label' => 'Tamamlayıcı ürünler'],
                     ['value' => 'alternative',    'label' => 'Alternatif ürünler'],
                     ['value' => 'same_category',  'label' => 'Aynı kategori'],
                     ['value' => 'bestseller',     'label' => 'Çok satanlar'],
                 ]],
                ['type' => 'range',    'id' => 'products_to_show', 'label' => 'Ürün sayısı', 'min' => 2, 'max' => 8, 'step' => 2, 'default' => 4],
                ['type' => 'checkbox', 'id' => 'show_add_to_cart', 'label' => 'Sepete ekle butonu', 'default' => true],
                ['type' => 'select',   'id' => 'source',           'label' => 'Veri kaynağı', 'default' => 'mysql',
                 'options' => [['value'=>'mysql','label'=>'MySQL'],['value'=>'api','label'=>'API']]],
            ],
            'presets' => [['name' => 'Upsell Öneriler', 'category' => 'E-Commerce']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $limit  = (int)($settings['products_to_show'] ?? 4);
        $mode   = $settings['mode'] ?? 'complementary';
        $source = $settings['source'] ?? 'mysql';

        $products = [];

        try {
            if ($source === 'api') {
                $products = $this->api()->withTenant($tenantId ?? '')
                    ->fetchProducts(['upsell_mode' => $mode, 'per_page' => $limit]);
            } else {
                $queryOpts = ['tenant_id' => $tenantId, 'limit' => $limit];

                if ($mode === 'same_category') $queryOpts['same_category'] = true;
                elseif ($mode === 'bestseller') $queryOpts['bestseller'] = true;
                else $queryOpts['featured'] = true;

                $products = $this->mysql()->fetchProducts($queryOpts);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('UpsellSection data error', ['error' => $e->getMessage()]);
        }

        return [
            'title'          => $settings['title'] ?? 'Birlikte Alınanlar',
            'mode'           => $mode,
            'products'       => $products,
            'show_add_to_cart' => (bool)($settings['show_add_to_cart'] ?? true),
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('upsell', $data);
    }
}
