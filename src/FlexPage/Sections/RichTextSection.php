<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

class RichTextSection extends AbstractSection
{
    public function type(): string { return 'rich-text'; }

    public function schema(): array
    {
        return [
            'name' => 'Zengin Metin',
            'settings' => [
                ['type' => 'text',     'id' => 'title',      'label' => 'Başlık'],
                ['type' => 'richtext', 'id' => 'content',    'label' => 'İçerik'],
                ['type' => 'select',   'id' => 'text_align', 'label' => 'Hizalama', 'default' => 'left',
                    'options' => [
                        ['value' => 'left',   'label' => 'Sol'],
                        ['value' => 'center', 'label' => 'Orta'],
                        ['value' => 'right',  'label' => 'Sağ'],
                    ],
                ],
            ],
            'presets' => [['name' => 'Zengin Metin', 'category' => 'Text']],
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('rich-text', ['settings' => $settings]);
    }
}

