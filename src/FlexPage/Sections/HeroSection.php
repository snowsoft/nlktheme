<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * Hero / Slideshow section.
 * Data source: MySQL (sliders table) with optional API overlay.
 */
class HeroSection extends AbstractSection
{
    public function type(): string
    {
        return 'hero';
    }

    public function dataSource(): string
    {
        return 'mysql';
    }

    public function schema(): array
    {
        return [
            'name' => 'Hero',
            'settings' => [
                ['type' => 'checkbox', 'id' => 'autoplay', 'label' => 'Otomatik geçiş', 'default' => true],
                ['type' => 'range', 'id' => 'interval', 'label' => 'Süre (ms)', 'min' => 2000, 'max' => 10000, 'step' => 500, 'default' => 5000],
                ['type' => 'select', 'id' => 'height', 'label' => 'Yükseklik', 'default' => 'medium',
                    'options' => [
                        ['value' => 'small',  'label' => 'Küçük'],
                        ['value' => 'medium', 'label' => 'Orta'],
                        ['value' => 'large',  'label' => 'Büyük'],
                        ['value' => 'full',   'label' => 'Tam ekran'],
                    ],
                ],
            ],
            'presets' => [
                ['name' => 'Hero Slider', 'category' => 'Image'],
            ],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        return [
            'slides'   => $this->mysql()->fetchSliders($tenantId),
            'autoplay' => $settings['autoplay'] ?? true,
            'interval' => $settings['interval'] ?? 5000,
            'height'   => $settings['height'] ?? 'medium',
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('hero', $data);
    }
}
