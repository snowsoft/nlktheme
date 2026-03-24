<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * VideoCommerceSection — Ürün tanıtım videosu ile satış.
 *
 * YouTube, Vimeo veya CDN video (HLS/DASH) embed.
 * Ürün üzerinde tıklanabilir hot-spot desteği.
 */
class VideoCommerceSection extends AbstractSection
{
    public function type(): string { return 'video-commerce'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'Video Commerce',
            'settings' => [
                ['type' => 'text',     'id' => 'title',        'label' => 'Başlık',       'default' => ''],
                ['type' => 'select',   'id' => 'video_type',   'label' => 'Video türü', 'default' => 'youtube',
                 'options' => [
                     ['value' => 'youtube',  'label' => 'YouTube'],
                     ['value' => 'vimeo',    'label' => 'Vimeo'],
                     ['value' => 'cdn',      'label' => 'CDN Video (HLS/DASH)'],
                     ['value' => 'direct',   'label' => 'Doğrudan URL (MP4)'],
                 ]],
                ['type' => 'text',     'id' => 'video_url',    'label' => 'Video URL/ID',  'default' => ''],
                ['type' => 'text',     'id' => 'cdn_video_id', 'label' => 'CDN Video ID',  'default' => ''],
                ['type' => 'image_picker', 'id' => 'poster',   'label' => 'Kapak görseli', 'default' => ''],
                ['type' => 'checkbox', 'id' => 'autoplay',     'label' => 'Otomatik oynat (muted)', 'default' => false],
                ['type' => 'checkbox', 'id' => 'loop',         'label' => 'Döngü',         'default' => false],
                ['type' => 'checkbox', 'id' => 'show_controls','label' => 'Kontroller',     'default' => true],
                ['type' => 'select',   'id' => 'aspect_ratio', 'label' => 'En/boy oranı', 'default' => '16:9',
                 'options' => [['value'=>'16:9','label'=>'16:9'],['value'=>'9:16','label'=>'9:16 (Reels)'],['value'=>'1:1','label'=>'1:1 (Kare)']]],
                ['type' => 'text',     'id' => 'cta_label',    'label' => 'CTA Butonu',    'default' => 'Şimdi Al'],
                ['type' => 'text',     'id' => 'cta_url',      'label' => 'CTA URL',       'default' => ''],
            ],
            'presets' => [['name' => 'Video Commerce', 'category' => 'Media']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $videoType = $settings['video_type'] ?? 'youtube';
        $videoUrl  = $settings['video_url'] ?? '';
        $cdnId     = $settings['cdn_video_id'] ?? '';

        // CDN HLS URL oluştur
        $hlsUrl  = $cdnId ? (config('theme.cdn.url') . '/api/video/' . $cdnId . '/hls') : '';
        $dashUrl = $cdnId ? (config('theme.cdn.url') . '/api/video/' . $cdnId . '/dash') : '';
        $thumbUrl= $cdnId ? (config('theme.cdn.url') . '/api/video/' . $cdnId . '/thumbnail') : '';

        return [
            'title'       => $settings['title'] ?? '',
            'video_type'  => $videoType,
            'video_url'   => $videoUrl,
            'cdn_video_id'=> $cdnId,
            'hls_url'     => $hlsUrl,
            'dash_url'    => $dashUrl,
            'thumb_url'   => $thumbUrl,
            'poster'      => $settings['poster'] ?? $thumbUrl,
            'autoplay'    => (bool)($settings['autoplay'] ?? false),
            'loop'        => (bool)($settings['loop'] ?? false),
            'show_controls'=> (bool)($settings['show_controls'] ?? true),
            'aspect_ratio'=> $settings['aspect_ratio'] ?? '16:9',
            'cta_label'   => $settings['cta_label'] ?? '',
            'cta_url'     => $settings['cta_url'] ?? '',
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('video-commerce', $data);
    }
}
