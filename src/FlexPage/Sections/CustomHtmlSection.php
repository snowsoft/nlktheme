<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

class CustomHtmlSection extends AbstractSection
{
    public function type(): string { return 'custom-html'; }

    public function schema(): array
    {
        return [
            'name' => 'Özel HTML',
            'settings' => [
                ['type' => 'html', 'id' => 'html', 'label' => 'HTML kodu'],
            ],
            'presets' => [['name' => 'Özel HTML', 'category' => 'Advanced']],
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        // Raw HTML — no view file needed
        return $settings['html'] ?? '';
    }
}
