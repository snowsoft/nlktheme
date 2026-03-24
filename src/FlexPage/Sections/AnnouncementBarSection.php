<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

class AnnouncementBarSection extends AbstractSection
{
    public function type(): string { return 'announcement-bar'; }

    public function schema(): array
    {
        return [
            'name' => 'Duyuru Barı',
            'settings' => [
                ['type' => 'text',     'id' => 'text',            'label' => 'Metin',         'default' => 'Kargo bedava!'],
                ['type' => 'url',      'id' => 'link',            'label' => 'Bağlantı'],
                ['type' => 'color',    'id' => 'bg_color',        'label' => 'Arka plan',      'default' => '#000000'],
                ['type' => 'color',    'id' => 'text_color',      'label' => 'Metin rengi',   'default' => '#ffffff'],
                ['type' => 'checkbox', 'id' => 'show_close_btn',  'label' => 'Kapat butonu',  'default' => true],
            ],
            'presets' => [['name' => 'Duyuru Barı', 'category' => 'Promotional']],
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('announcement-bar', ['settings' => $settings]);
    }
}
