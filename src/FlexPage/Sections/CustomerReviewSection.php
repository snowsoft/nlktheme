<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * CustomerReviewSection — Müşteri yorumları.
 *
 * JSON-LD Review + AggregateRating ile SEO uyumlu.
 * MySQL veya API'den yorumları çeker.
 */
class CustomerReviewSection extends AbstractSection
{
    public function type(): string { return 'customer-reviews'; }
    public function dataSource(): string { return 'mysql'; }

    public function schema(): array
    {
        return [
            'name' => 'Müşteri Yorumları',
            'settings' => [
                ['type' => 'text',     'id' => 'title',        'label' => 'Başlık',      'default' => 'Müşterilerimiz Ne Diyor?'],
                ['type' => 'range',    'id' => 'limit',        'label' => 'Yorum sayısı','min' => 3, 'max' => 18, 'step' => 3, 'default' => 6],
                ['type' => 'range',    'id' => 'min_rating',   'label' => 'Min puan',    'min' => 1, 'max' => 5, 'default' => 4],
                ['type' => 'select',   'id' => 'layout',       'label' => 'Düzen', 'default' => 'grid',
                 'options' => [['value'=>'grid','label'=>'Izgara'],['value'=>'slider','label'=>'Karusel']]],
                ['type' => 'checkbox', 'id' => 'show_stars',   'label' => 'Yıldız göster', 'default' => true],
                ['type' => 'checkbox', 'id' => 'show_date',    'label' => 'Tarih göster',  'default' => false],
                ['type' => 'checkbox', 'id' => 'show_avatar',  'label' => 'Avatar göster', 'default' => true],
                ['type' => 'checkbox', 'id' => 'jsonld',       'label' => 'JSON-LD Schema ekle', 'default' => true],
                ['type' => 'select',   'id' => 'source',       'label' => 'Kaynak', 'default' => 'mysql',
                 'options' => [['value'=>'mysql','label'=>'MySQL'],['value'=>'api','label'=>'API']]],
            ],
            'presets' => [['name' => 'Müşteri Yorumları', 'category' => 'Social Proof']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $limit     = (int)($settings['limit'] ?? 6);
        $minRating = (int)($settings['min_rating'] ?? 4);
        $source    = $settings['source'] ?? 'mysql';
        $reviews   = [];
        $avgRating = 0;
        $total     = 0;

        try {
            if ($source === 'api') {
                $reviews = $this->api()->withTenant($tenantId ?? '')
                    ->get('v1/reviews', ['min_rating' => $minRating, 'per_page' => $limit])['data'] ?? [];
            } else {
                $rows = \Illuminate\Support\Facades\DB::table('yorumlar')
                    ->where('tenant_id', $tenantId)
                    ->where('aktif', 1)
                    ->where('puan', '>=', $minRating)
                    ->orderByDesc('id')
                    ->limit($limit)
                    ->get();

                $reviews = $rows->map(fn($r) => (array)$r)->all();
                $total   = \Illuminate\Support\Facades\DB::table('yorumlar')
                    ->where('tenant_id', $tenantId)->where('aktif', 1)->count();
                $avgRating = $total > 0
                    ? round(\Illuminate\Support\Facades\DB::table('yorumlar')
                        ->where('tenant_id', $tenantId)->where('aktif', 1)
                        ->avg('puan'), 1)
                    : 0;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('CustomerReviewSection error', ['error' => $e->getMessage()]);
        }

        return [
            'title'       => $settings['title'] ?? 'Müşterilerimiz Ne Diyor?',
            'layout'      => $settings['layout'] ?? 'grid',
            'show_stars'  => (bool)($settings['show_stars'] ?? true),
            'show_date'   => (bool)($settings['show_date'] ?? false),
            'show_avatar' => (bool)($settings['show_avatar'] ?? true),
            'jsonld'      => (bool)($settings['jsonld'] ?? true),
            'reviews'     => $reviews,
            'avg_rating'  => $avgRating,
            'total_count' => $total,
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('customer-reviews', $data);
    }
}
