<?php

namespace Nlk\Theme\FlexPage\Sections;

use Nlk\Theme\FlexPage\AbstractSection;

/**
 * MarketSwitcherSection — Dil ve para birimi seçici widget.
 *
 * URL prefix veya query param ile dil değiştirme.
 * Cookie/session ile para birimi seçimi.
 */
class MarketSwitcherSection extends AbstractSection
{
    public function type(): string { return 'market-switcher'; }
    public function dataSource(): string { return 'static'; }

    public function schema(): array
    {
        return [
            'name' => 'Dil & Para Birimi Seçici',
            'settings' => [
                ['type' => 'checkbox', 'id' => 'show_language',  'label' => 'Dil seçici göster',           'default' => true],
                ['type' => 'checkbox', 'id' => 'show_currency',  'label' => 'Para birimi seçici göster',   'default' => true],
                ['type' => 'checkbox', 'id' => 'show_flags',     'label' => 'Bayrak ikonları göster',      'default' => true],
                ['type' => 'select',   'id' => 'dropdown_style', 'label' => 'Dropdown stili', 'default' => 'dropdown',
                 'options' => [['value'=>'dropdown','label'=>'Dropdown'],['value'=>'modal','label'=>'Modal'],['value'=>'inline','label'=>'Satır içi']]],
                ['type' => 'text',     'id' => 'lang_switch_url','label' => 'Dil değiştirme URL',          'default' => '/lang/{locale}'],
                ['type' => 'text',     'id' => 'currency_cookie','label' => 'Para birimi cookie adı',      'default' => 'preferred_currency'],
            ],
            'presets' => [['name' => 'Market Switcher', 'category' => 'Navigation']],
        ];
    }

    public function fetchData(array $settings, ?string $tenantId = null): array
    {
        $currentLocale   = app()->getLocale();
        $currentCurrency = request()->cookie($settings['currency_cookie'] ?? 'preferred_currency', 'TRY');

        $supportedLocales = config('theme.i18n.locales', ['tr', 'en', 'de', 'ar', 'ru']);
        $currencies       = config('theme.i18n.currencies', ['TRY', 'USD', 'EUR', 'GBP']);

        $localeNames = [
            'tr' => ['name' => 'Türkçe',   'native' => 'Türkçe',    'flag' => '🇹🇷'],
            'en' => ['name' => 'English',  'native' => 'English',   'flag' => '🇬🇧'],
            'de' => ['name' => 'Deutsch',  'native' => 'Deutsch',   'flag' => '🇩🇪'],
            'ar' => ['name' => 'العربية',  'native' => 'العربية',   'flag' => '🇸🇦'],
            'ru' => ['name' => 'Русский',  'native' => 'Русский',   'flag' => '🇷🇺'],
            'fr' => ['name' => 'Français', 'native' => 'Français',  'flag' => '🇫🇷'],
            'es' => ['name' => 'Español',  'native' => 'Español',   'flag' => '🇪🇸'],
        ];

        $currencySymbols = [
            'TRY' => ['symbol' => '₺', 'name' => 'Türk Lirası'],
            'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
            'EUR' => ['symbol' => '€', 'name' => 'Euro'],
            'GBP' => ['symbol' => '£', 'name' => 'British Pound'],
            'AED' => ['symbol' => 'AED', 'name' => 'UAE Dirham'],
            'RUB' => ['symbol' => '₽', 'name' => 'Russian Ruble'],
        ];

        $localeList = array_map(fn($l) => array_merge(
            ['code' => $l, 'active' => $l === $currentLocale],
            $localeNames[$l] ?? ['name' => strtoupper($l), 'native' => $l, 'flag' => '🌐']
        ), $supportedLocales);

        $currencyList = array_map(fn($c) => array_merge(
            ['code' => $c, 'active' => $c === $currentCurrency],
            $currencySymbols[$c] ?? ['symbol' => $c, 'name' => $c]
        ), $currencies);

        return [
            'show_language'   => (bool)($settings['show_language'] ?? true),
            'show_currency'   => (bool)($settings['show_currency'] ?? true),
            'show_flags'      => (bool)($settings['show_flags'] ?? true),
            'dropdown_style'  => $settings['dropdown_style'] ?? 'dropdown',
            'lang_switch_url' => $settings['lang_switch_url'] ?? '/lang/{locale}',
            'currency_cookie' => $settings['currency_cookie'] ?? 'preferred_currency',
            'current_locale'  => $currentLocale,
            'current_currency'=> $currentCurrency,
            'locales'         => $localeList,
            'currencies'      => $currencyList,
        ];
    }

    public function render(array $settings, array $blocks, array $data): string
    {
        return $this->renderView('market-switcher', $data);
    }
}
