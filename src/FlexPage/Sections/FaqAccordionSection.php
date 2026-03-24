<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * FaqAccordionSection — SSS (Sıkça Sorulan Sorular) akordeon.
 *
 * JSON-LD FAQPage schema ile SEO uyumlu.
 * Statik veya API'den sorular çeker.
 */
class FaqAccordionSection extends AbstractSection
{
    public function type(): string { return 'faq-accordion'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'SSS Akordeon',
            'settings' => [
                ['type' => 'text',     'id' => 'title',      'label' => 'Başlık',    'default' => 'Sıkça Sorulan Sorular'],
                ['type' => 'select',   'id' => 'source',     'label' => 'Kaynak', 'default' => 'static',
                 'options' => [['value'=>'static','label'=>'Manuel'],['value'=>'api','label'=>'API']]],
                ['type' => 'text',     'id' => 'api_url',    'label' => 'API URL',   'default' => '/api/v1/faq'],
                ['type' => 'checkbox', 'id' => 'jsonld',     'label' => 'FAQPage JSON-LD', 'default' => true],
                ['type' => 'select',   'id' => 'open_first', 'label' => 'İlk yanıt açık', 'default' => 'yes',
                 'options' => [['value'=>'yes','label'=>'Evet'],['value'=>'no','label'=>'Hayır']]],
            ],
            'blocks' => [
                [
                    'type' => 'faq_item',
                    'name' => 'SSS Maddesi',
                    'settings' => [
                        ['type' => 'text',     'id' => 'question', 'label' => 'Soru',   'default' => 'Soru buraya?'],
                        ['type' => 'richtext', 'id' => 'answer',   'label' => 'Cevap',  'default' => 'Cevap buraya.'],
                    ],
                ],
            ],
            'presets' => [['name' => 'SSS', 'category' => 'Content']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $source = $settings['source'] ?? 'static';
        $faqs   = [];

        if ($source === 'api') {
            try {
                $response = $this->api()->withTenant($tenantId ?? '')->get('v1/faq');
                $faqs = $response['data'] ?? [];
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('FaqAccordionSection error', ['error' => $e->getMessage()]);
            }
        }

        return [
            'title'      => $settings['title'] ?? 'Sıkça Sorulan Sorular',
            'source'     => $source,
            'api_url'    => $settings['api_url'] ?? '/api/v1/faq',
            'jsonld'     => (bool)($settings['jsonld'] ?? true),
            'open_first' => ($settings['open_first'] ?? 'yes') === 'yes',
            'faqs'       => $faqs, // API'den gelen; static'te boş (blade'da $blocks kullanılır)
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        // FAQ blokları sections JSON'dan $blocks ile gelir
        if (empty($data['faqs']) && !empty($blocks)) {
            $data['faqs'] = array_values(array_map(fn($b) => [
                'question' => $b['settings']['question'] ?? '',
                'answer'   => $b['settings']['answer'] ?? '',
            ], $blocks));
        }

        return $this->renderView('faq-accordion', $data);
    }
}
