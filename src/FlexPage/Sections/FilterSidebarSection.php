<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * FilterSidebarSection — Kategori sayfası dinamik filtre sidebar.
 *
 * Fiyat aralığı, özellik (renk, beden, marka vb.) filtreleri.
 * AJAX ile sayfa yenilenmeden güncellenir.
 */
class FilterSidebarSection extends AbstractSection
{
    public function type(): string { return 'filter-sidebar'; }
    public function dataSource(): string { return 'mysql'; }

    public function schema(): array
    {
        return [
            'name' => 'Filtre Sidebar',
            'settings' => [
                ['type' => 'checkbox', 'id' => 'show_price_filter',   'label' => 'Fiyat filtresi',    'default' => true],
                ['type' => 'checkbox', 'id' => 'show_brand_filter',   'label' => 'Marka filtresi',    'default' => true],
                ['type' => 'checkbox', 'id' => 'show_color_filter',   'label' => 'Renk filtresi',     'default' => true],
                ['type' => 'checkbox', 'id' => 'show_size_filter',    'label' => 'Beden filtresi',    'default' => true],
                ['type' => 'checkbox', 'id' => 'show_rating_filter',  'label' => 'Puan filtresi',     'default' => false],
                ['type' => 'checkbox', 'id' => 'show_stock_filter',   'label' => 'Stok filtresi',     'default' => false],
                ['type' => 'text',     'id' => 'filter_api_url',      'label' => 'Filtre API URL',    'default' => '/api/v1/filters'],
                ['type' => 'text',     'id' => 'products_api_url',    'label' => 'Ürün API URL',      'default' => '/api/v1/products'],
                ['type' => 'select',   'id' => 'price_display',       'label' => 'Fiyat slider stili','default' => 'range',
                 'options' => [['value'=>'range','label'=>'Slider'],['value'=>'inputs','label'=>'İki input']]],
                ['type' => 'select',   'id' => 'mobile_position',     'label' => 'Mobilde konum', 'default' => 'drawer',
                 'options' => [['value'=>'drawer','label'=>'Drawer'],['value'=>'top','label'=>'Üstte']]],
                ['type' => 'select',   'id' => 'source',              'label' => 'Kaynak', 'default' => 'mysql',
                 'options' => [['value'=>'mysql','label'=>'MySQL'],['value'=>'api','label'=>'API']]],
            ],
            'presets' => [['name' => 'Filtre Sidebar', 'category' => 'Navigation']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $source  = $settings['source'] ?? 'mysql';
        $filters = ['brands' => [], 'colors' => [], 'sizes' => [], 'price_min' => 0, 'price_max' => 10000];

        try {
            if ($source === 'api') {
                $resp    = $this->api()->withTenant($tenantId ?? '')->get('v1/filters');
                $filters = array_merge($filters, $resp['data'] ?? []);
            } else {
                if ($settings['show_brand_filter'] ?? true) {
                    $filters['brands'] = \Illuminate\Support\Facades\DB::table('urunler')
                        ->where('tenant_id', $tenantId)->where('aktif', 1)
                        ->distinct()->pluck('marka')->filter()->values()->all();
                }
                if ($settings['show_color_filter'] ?? true) {
                    $filters['colors'] = \Illuminate\Support\Facades\DB::table('urun_ozellikleri')
                        ->where('tenant_id', $tenantId)->where('ozellik_adi', 'renk')
                        ->distinct()->pluck('deger')->filter()->values()->all();
                }
                if ($settings['show_size_filter'] ?? true) {
                    $filters['sizes'] = \Illuminate\Support\Facades\DB::table('urun_ozellikleri')
                        ->where('tenant_id', $tenantId)->where('ozellik_adi', 'beden')
                        ->distinct()->pluck('deger')->filter()->values()->all();
                }
                $priceRange = \Illuminate\Support\Facades\DB::table('urunler')
                    ->where('tenant_id', $tenantId)->where('aktif', 1)
                    ->selectRaw('MIN(fiyat) as min, MAX(fiyat) as max')->first();
                $filters['price_min'] = (float)($priceRange->min ?? 0);
                $filters['price_max'] = (float)($priceRange->max ?? 10000);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FilterSidebarSection error', ['error' => $e->getMessage()]);
        }

        return [
            'show_price_filter'  => (bool)($settings['show_price_filter'] ?? true),
            'show_brand_filter'  => (bool)($settings['show_brand_filter'] ?? true),
            'show_color_filter'  => (bool)($settings['show_color_filter'] ?? true),
            'show_size_filter'   => (bool)($settings['show_size_filter'] ?? true),
            'show_rating_filter' => (bool)($settings['show_rating_filter'] ?? false),
            'show_stock_filter'  => (bool)($settings['show_stock_filter'] ?? false),
            'filter_api_url'     => $settings['filter_api_url'] ?? '/api/v1/filters',
            'products_api_url'   => $settings['products_api_url'] ?? '/api/v1/products',
            'price_display'      => $settings['price_display'] ?? 'range',
            'mobile_position'    => $settings['mobile_position'] ?? 'drawer',
            'tenant_id'          => $tenantId ?? '',
            'filters'            => $filters,
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('filter-sidebar', $data);
    }
}
