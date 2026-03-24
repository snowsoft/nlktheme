<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * LoyaltyWidgetSection — Sadakat puan göstergesi.
 *
 * Üye puanı, kazanma/harcama bilgisi ve ödül durumu.
 * Kullanıcı giriş yapmışsa API'den puan bilgisini getirir.
 */
class LoyaltyWidgetSection extends AbstractSection
{
    public function type(): string { return 'loyalty-widget'; }
    public function dataSource(): string { return 'api'; }

    public function schema(): array
    {
        return [
            'name' => 'Sadakat Puanları',
            'settings' => [
                ['type' => 'text',     'id' => 'title',        'label' => 'Başlık',       'default' => 'Puan Durumunuz'],
                ['type' => 'text',     'id' => 'points_label', 'label' => 'Puan birimi',  'default' => 'Puan'],
                ['type' => 'text',     'id' => 'api_url',      'label' => 'Puan API URL', 'default' => '/api/v1/loyalty/balance'],
                ['type' => 'text',     'id' => 'redeem_url',   'label' => 'Kullan URL',   'default' => '/profil/puanlar'],
                ['type' => 'text',     'id' => 'earn_rate_msg','label' => 'Kazanım mesajı','default'=> 'Her 100₺ harcamana 10 puan'],
                ['type' => 'checkbox', 'id' => 'show_tier',    'label' => 'Seviye göster','default' => true],
                ['type' => 'checkbox', 'id' => 'show_history', 'label' => 'İşlem geçmişi bağlantısı', 'default' => true],
                ['type' => 'select',   'id' => 'display_mode', 'label' => 'Mod', 'default' => 'card',
                 'options' => [['value'=>'card','label'=>'Kart'],['value'=>'badge','label'=>'Rozet'],['value'=>'bar','label'=>'İlerleme çubuğu']]],
            ],
            'presets' => [['name' => 'Sadakat Puanları', 'category' => 'Loyalty']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $balance = null;

        try {
            $resp    = $this->api()->withTenant($tenantId ?? '')->get('v1/loyalty/balance');
            $balance = $resp['data'] ?? $resp;
        } catch (\Throwable $e) {
            // Misafir kullanıcı veya API yok — graceful degrade
        }

        return [
            'title'         => $settings['title'] ?? 'Puan Durumunuz',
            'points_label'  => $settings['points_label'] ?? 'Puan',
            'api_url'       => $settings['api_url'] ?? '/api/v1/loyalty/balance',
            'redeem_url'    => $settings['redeem_url'] ?? '/profil/puanlar',
            'earn_rate_msg' => $settings['earn_rate_msg'] ?? '',
            'show_tier'     => (bool)($settings['show_tier'] ?? true),
            'show_history'  => (bool)($settings['show_history'] ?? true),
            'display_mode'  => $settings['display_mode'] ?? 'card',
            'balance'       => $balance,    // null = gösterilmez (giriş yok)
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('loyalty-widget', $data);
    }
}
