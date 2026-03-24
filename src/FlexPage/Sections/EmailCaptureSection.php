<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * EmailCaptureSection — Newsletter popup / e-posta listesi.
 *
 * Exit-intent veya sayfa yüklenince bir gecikme ile gösterilir.
 * Webhook veya dahili API'ye subscribe eder.
 */
class EmailCaptureSection extends AbstractSection
{
    public function type(): string { return 'email-capture'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'E-posta Yakalama',
            'settings' => [
                ['type' => 'text',     'id' => 'title',         'label' => 'Başlık',         'default' => 'Özel Teklifler İçin Abone Ol'],
                ['type' => 'text',     'id' => 'subtitle',      'label' => 'Alt başlık',     'default' => 'İlk siparişinde %10 indirim kazan!'],
                ['type' => 'text',     'id' => 'placeholder',   'label' => 'Input placeholder','default' => 'E-posta adresiniz'],
                ['type' => 'text',     'id' => 'button_label',  'label' => 'Buton yazısı',   'default' => 'Abone Ol'],
                ['type' => 'text',     'id' => 'success_msg',   'label' => 'Başarı mesajı',  'default' => '🎉 Teşekkürler! Onay e-postanızı kontrol edin.'],
                ['type' => 'text',     'id' => 'api_url',       'label' => 'Subscribe API',  'default' => '/api/v1/newsletter/subscribe'],
                ['type' => 'select',   'id' => 'trigger',       'label' => 'Tetikleyici', 'default' => 'exit_intent',
                 'options' => [
                     ['value' => 'exit_intent', 'label' => 'Exit Intent'],
                     ['value' => 'scroll',       'label' => 'Scroll %50'],
                     ['value' => 'delay',        'label' => 'Gecikme'],
                     ['value' => 'always',       'label' => 'Her zaman'],
                 ]],
                ['type' => 'range',    'id' => 'delay_secs',    'label' => 'Gecikme (sn)',   'min' => 1, 'max' => 30, 'default' => 5],
                ['type' => 'checkbox', 'id' => 'show_image',    'label' => 'Görsel göster',  'default' => true],
                ['type' => 'image_picker', 'id' => 'image',     'label' => 'Görsel',         'default' => ''],
                ['type' => 'text',     'id' => 'cookie_days',   'label' => 'Tekrar göster (gün)', 'default' => '30'],
            ],
            'presets' => [['name' => 'Newsletter Popup', 'category' => 'Marketing']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'title'        => $settings['title'] ?? 'Özel Teklifler İçin Abone Ol',
            'subtitle'     => $settings['subtitle'] ?? '',
            'placeholder'  => $settings['placeholder'] ?? 'E-posta adresiniz',
            'button_label' => $settings['button_label'] ?? 'Abone Ol',
            'success_msg'  => $settings['success_msg'] ?? 'Teşekkürler!',
            'api_url'      => $settings['api_url'] ?? '/api/v1/newsletter/subscribe',
            'trigger'      => $settings['trigger'] ?? 'exit_intent',
            'delay_secs'   => (int)($settings['delay_secs'] ?? 5),
            'show_image'   => (bool)($settings['show_image'] ?? true),
            'image'        => $settings['image'] ?? '',
            'cookie_days'  => (int)($settings['cookie_days'] ?? 30),
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('email-capture', $data);
    }
}
