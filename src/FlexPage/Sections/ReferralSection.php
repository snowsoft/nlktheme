<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * ReferralSection — Arkadaşını getir / davet kodu.
 *
 * Kişiye özel davet linki veya kod gösterir.
 * Kopyalama butonu + sosyal paylaşım entegrasyonu.
 */
class ReferralSection extends AbstractSection
{
    public function type(): string { return 'referral'; }
    public function dataSource(): string { return 'api'; }

    public function schema(): array
    {
        return [
            'name' => 'Arkadaşını Getir',
            'settings' => [
                ['type' => 'text',     'id' => 'title',           'label' => 'Başlık',           'default' => 'Arkadaşını Davet Et!'],
                ['type' => 'text',     'id' => 'subtitle',        'label' => 'Alt başlık',       'default' => 'Her başarılı davet için 50₺ kazan'],
                ['type' => 'text',     'id' => 'api_url',         'label' => 'Davet kodu API',   'default' => '/api/v1/referral/code'],
                ['type' => 'text',     'id' => 'share_message',   'label' => 'Paylaşım mesajı',  'default' => 'Bu kodu kullan ve ilk siparişinde indirim kazan: {code}'],
                ['type' => 'checkbox', 'id' => 'show_whatsapp',   'label' => 'WhatsApp paylaş',  'default' => true],
                ['type' => 'checkbox', 'id' => 'show_telegram',   'label' => 'Telegram paylaş',  'default' => true],
                ['type' => 'checkbox', 'id' => 'show_twitter',    'label' => 'X (Twitter) paylaş','default' => false],
                ['type' => 'checkbox', 'id' => 'show_facebook',   'label' => 'Facebook paylaş',  'default' => false],
                ['type' => 'checkbox', 'id' => 'show_copy_link',  'label' => 'Link kopyala',     'default' => true],
                ['type' => 'text',     'id' => 'referral_url_base','label' => 'Davet URL base',  'default' => 'https://magazan.com/davet/'],
            ],
            'presets' => [['name' => 'Arkadaşını Getir', 'category' => 'Marketing']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $referralCode = null;
        $referralUrl  = null;

        try {
            $resp = $this->api()->withTenant($tenantId ?? '')->get('v1/referral/code');
            $referralCode = $resp['code'] ?? $resp['data']['code'] ?? null;
            $base         = rtrim($settings['referral_url_base'] ?? '', '/');
            $referralUrl  = $referralCode ? $base . '/' . $referralCode : null;
        } catch (\Throwable $e) {
            // Misafir kullanıcı veya API yok
        }

        $shareMsg = str_replace('{code}', $referralCode ?? '', $settings['share_message'] ?? '');

        return [
            'title'           => $settings['title'] ?? 'Arkadaşını Davet Et!',
            'subtitle'        => $settings['subtitle'] ?? '',
            'referral_code'   => $referralCode,
            'referral_url'    => $referralUrl,
            'share_message'   => $shareMsg,
            'show_whatsapp'   => (bool)($settings['show_whatsapp'] ?? true),
            'show_telegram'   => (bool)($settings['show_telegram'] ?? true),
            'show_twitter'    => (bool)($settings['show_twitter'] ?? false),
            'show_facebook'   => (bool)($settings['show_facebook'] ?? false),
            'show_copy_link'  => (bool)($settings['show_copy_link'] ?? true),
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('referral', $data);
    }
}
