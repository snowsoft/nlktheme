<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * BlogListSection — Blog yazı listesi.
 *
 * JSON-LD Article schema ile SEO uyumlu.
 * MySQL veya API'den yazıları çeker.
 */
class BlogListSection extends AbstractSection
{
    public function type(): string { return 'blog-list'; }
    public function dataSource(): string { return 'mysql'; }

    public function schema(): array
    {
        return [
            'name' => 'Blog Yazıları',
            'settings' => [
                ['type' => 'text',     'id' => 'title',    'label' => 'Başlık',       'default' => 'Blog & Haberler'],
                ['type' => 'range',    'id' => 'limit',    'label' => 'Yazı sayısı',  'min' => 2, 'max' => 12, 'step' => 2, 'default' => 3],
                ['type' => 'select',   'id' => 'layout',   'label' => 'Düzen', 'default' => 'grid',
                 'options' => [['value'=>'grid','label'=>'Izgara'],['value'=>'list','label'=>'Liste'],['value'=>'slider','label'=>'Karusel']]],
                ['type' => 'select',   'id' => 'columns',  'label' => 'Sütun sayısı', 'default' => '3',
                 'options' => [['value'=>'2','label'=>'2'],['value'=>'3','label'=>'3'],['value'=>'4','label'=>'4']]],
                ['type' => 'checkbox', 'id' => 'show_author',      'label' => 'Yazar göster',   'default' => true],
                ['type' => 'checkbox', 'id' => 'show_date',        'label' => 'Tarih göster',   'default' => true],
                ['type' => 'checkbox', 'id' => 'show_category',    'label' => 'Kategori göster','default' => true],
                ['type' => 'checkbox', 'id' => 'show_read_time',   'label' => 'Okuma süresi',   'default' => false],
                ['type' => 'select',   'id' => 'source',  'label' => 'Kaynak', 'default' => 'mysql',
                 'options' => [['value'=>'mysql','label'=>'MySQL'],['value'=>'api','label'=>'API']]],
                ['type' => 'text',     'id' => 'view_all_url', 'label' => 'Tümünü gör URL', 'default' => '/blog'],
            ],
            'presets' => [['name' => 'Blog Yazıları', 'category' => 'Content']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $limit  = (int)($settings['limit'] ?? 3);
        $source = $settings['source'] ?? 'mysql';
        $posts  = [];

        try {
            if ($source === 'api') {
                $posts = $this->api()->withTenant($tenantId ?? '')
                    ->get('v1/blog/posts', ['per_page' => $limit])['data'] ?? [];
            } else {
                $rows  = \Illuminate\Support\Facades\DB::table('blog_yazilari')
                    ->where('tenant_id', $tenantId)
                    ->where('yayinda', 1)
                    ->orderByDesc('yayinlanma_tarihi')
                    ->limit($limit)
                    ->get();
                $posts = $rows->map(fn($r) => (array)$r)->all();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('BlogListSection error', ['error' => $e->getMessage()]);
        }

        return [
            'title'         => $settings['title'] ?? 'Blog & Haberler',
            'layout'        => $settings['layout'] ?? 'grid',
            'columns'       => (int)($settings['columns'] ?? 3),
            'show_author'   => (bool)($settings['show_author'] ?? true),
            'show_date'     => (bool)($settings['show_date'] ?? true),
            'show_category' => (bool)($settings['show_category'] ?? true),
            'show_read_time'=> (bool)($settings['show_read_time'] ?? false),
            'view_all_url'  => $settings['view_all_url'] ?? '/blog',
            'posts'         => $posts,
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('blog-list', $data);
    }
}
