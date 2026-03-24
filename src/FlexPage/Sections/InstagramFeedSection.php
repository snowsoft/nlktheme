<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * InstagramFeedSection — Instagram son paylaşımlar.
 *
 * Instagram Basic Display API veya üçüncü taraf feed servisi.
 * CDN'e import edilerek depolanabilir.
 */
class InstagramFeedSection extends AbstractSection
{
    public function type(): string { return 'instagram-feed'; }
    public function dataSource(): string { return 'api'; }

    public function schema(): array
    {
        return [
            'name' => 'Instagram Feed',
            'settings' => [
                ['type' => 'text',   'id' => 'title',      'label' => 'Başlık',        'default' => 'Instagram\'da Bizi Takip Edin'],
                ['type' => 'text',   'id' => 'username',   'label' => '@kullanıcıadı', 'default' => '@magazan'],
                ['type' => 'text',   'id' => 'api_url',    'label' => 'Feed API URL',  'default' => '/api/v1/instagram/feed'],
                ['type' => 'range',  'id' => 'limit',      'label' => 'Gönderi sayısı','min' => 4, 'max' => 12, 'step' => 4, 'default' => 8],
                ['type' => 'select', 'id' => 'columns',    'label' => 'Sütun', 'default' => '4',
                 'options' => [['value'=>'4','label'=>'4'],['value'=>'6','label'=>'6'],['value'=>'3','label'=>'3']]],
                ['type' => 'checkbox','id'=>'show_hover_overlay','label'=>'Hover overlay','default'=>true],
                ['type' => 'text',   'id' => 'follow_url', 'label' => 'Takip URL',     'default' => ''],
                ['type' => 'text',   'id' => 'follow_label','label' => 'Takip butonu', 'default' => 'Instagram\'da Takip Et'],
            ],
            'presets' => [['name' => 'Instagram Feed', 'category' => 'Social']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $posts  = [];
        $apiUrl = $settings['api_url'] ?? '/api/v1/instagram/feed';
        $limit  = (int)($settings['limit'] ?? 8);

        try {
            $response = $this->api()->withTenant($tenantId ?? '')
                ->get(ltrim(str_replace('/api/', '', $apiUrl), '/'), ['limit' => $limit]);
            $posts = $response['data'] ?? $response;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info('InstagramFeedSection: API error', ['error' => $e->getMessage()]);
        }

        return [
            'title'              => $settings['title'] ?? 'Instagram\'da Bizi Takip Edin',
            'username'           => $settings['username'] ?? '',
            'columns'            => (int)($settings['columns'] ?? 4),
            'show_hover_overlay' => (bool)($settings['show_hover_overlay'] ?? true),
            'follow_url'         => $settings['follow_url'] ?? '',
            'follow_label'       => $settings['follow_label'] ?? 'Takip Et',
            'posts'              => is_array($posts) ? array_slice($posts, 0, $limit) : [],
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('instagram-feed', $data);
    }
}
