<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * WebPushSection — Web Push Notification abonelik.
 *
 * Service Worker tabanlı push bildirim aboneliği.
 * VAPID anahtarları ile çalışır.
 */
class WebPushSection extends AbstractSection
{
    public function type(): string { return 'web-push'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'Web Push Aboneliği',
            'settings' => [
                ['type' => 'text',     'id' => 'title',         'label' => 'Başlık',           'default' => 'Bildirimleri Aç!'],
                ['type' => 'text',     'id' => 'description',   'label' => 'Açıklama',         'default' => 'Kampanya ve yeni ürünlerden ilk siz haberdar olun'],
                ['type' => 'text',     'id' => 'button_label',  'label' => 'Buton yazısı',     'default' => 'Bildirimlere İzin Ver'],
                ['type' => 'text',     'id' => 'success_msg',   'label' => 'Başarı mesajı',    'default' => '✓ Bildirimler açıldı!'],
                ['type' => 'text',     'id' => 'denied_msg',    'label' => 'Engellendi mesajı','default' => 'Tarayıcı ayarlarından bildirimlere izin verin.'],
                ['type' => 'text',     'id' => 'subscribe_url', 'label' => 'Subscribe API',    'default' => '/api/v1/push/subscribe'],
                ['type' => 'text',     'id' => 'vapid_public',  'label' => 'VAPID Public Key', 'default' => ''],
                ['type' => 'text',     'id' => 'sw_path',       'label' => 'Service Worker yolu','default' => '/sw.js'],
                ['type' => 'select',   'id' => 'trigger',       'label' => 'Tetikleyici', 'default' => 'button',
                 'options' => [['value'=>'button','label'=>'Butona tıklayınca'],['value'=>'auto','label'=>'Sayfa yüklenince (5sn)'],['value'=>'scroll','label'=>'Scroll %50']]],
                ['type' => 'checkbox', 'id' => 'hide_if_subscribed', 'label' => 'Abone olduktan sonra gizle', 'default' => true],
            ],
            'presets' => [['name' => 'Web Push', 'category' => 'Marketing']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'title'              => $settings['title'] ?? 'Bildirimleri Aç!',
            'description'        => $settings['description'] ?? '',
            'button_label'       => $settings['button_label'] ?? 'Bildirimlere İzin Ver',
            'success_msg'        => $settings['success_msg'] ?? 'Bildirimler açıldı!',
            'denied_msg'         => $settings['denied_msg'] ?? 'Bildirimlere izin verilmedi.',
            'subscribe_url'      => $settings['subscribe_url'] ?? '/api/v1/push/subscribe',
            'vapid_public'       => $settings['vapid_public'] ?? config('theme.push.vapid_public', ''),
            'sw_path'            => $settings['sw_path'] ?? '/sw.js',
            'trigger'            => $settings['trigger'] ?? 'button',
            'hide_if_subscribed' => (bool)($settings['hide_if_subscribed'] ?? true),
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('web-push', $data);
    }
}
