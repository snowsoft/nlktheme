<?php

namespace Nlk\Theme\I18n;

use Illuminate\Support\Facades\Cache;

/**
 * SectionI18nResolver — FlexPage section ayarlarında çok dil desteği.
 *
 * Section settings JSON'ında şu format desteklenir:
 * {
 *   "title": "En İyi Ürünler",
 *   "title_en": "Best Products",
 *   "title_de": "Beste Produkte"
 * }
 *
 * resolve($settings, 'en') çağrısında 'title_en' değeri döner,
 * bulunamazsa 'title' fallback olarak kullanılır.
 */
class SectionI18nResolver
{
    protected string $locale;
    protected string $fallback;

    public function __construct()
    {
        $this->locale   = app()->getLocale();
        $this->fallback = config('app.fallback_locale', 'tr');
    }

    /**
     * Settings array'ini mevcut locale'e göre resolve eder.
     *
     * @param  array   $settings  Section settings dizisi
     * @param  string|null $locale  Override locale
     */
    public function resolve(array $settings, ?string $locale = null): array
    {
        $locale  = $locale ?? $this->locale;
        $resolved = [];

        // Base key'leri bul (suffix'siz)
        $allKeys = array_keys($settings);
        $baseKeys = array_filter($allKeys, fn($k) => !preg_match('/_[a-z]{2}(_[A-Z]{2})?$/', $k));

        foreach ($baseKeys as $key) {
            // Locale-specific key: title_en, title_de, subtitle_tr gibi
            $localKey   = $key . '_' . $locale;
            $fallbackKey= $key . '_' . $this->fallback;

            $resolved[$key] = $settings[$localKey]    // tam locale: 'title_en'
                           ?? $settings[$fallbackKey] // fallback locale: 'title_tr'
                           ?? $settings[$key];        // varsayılan
        }

        // Locale-specific key'leri de ekle (admin editör için)
        foreach ($settings as $key => $value) {
            if (!isset($resolved[$key])) {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }

    /**
     * Desteklenen locale listesi (config'den).
     */
    public function supportedLocales(): array
    {
        return config('theme.i18n.locales', ['tr', 'en', 'de', 'ar', 'ru']);
    }

    /**
     * Mevcut locale.
     */
    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * RTL locale mi? (Arapça, İbranice vb.)
     */
    public function isRtl(?string $locale = null): bool
    {
        $locale = $locale ?? $this->locale;
        $rtl    = config('theme.i18n.rtl_locales', ['ar', 'he', 'fa', 'ur']);
        return in_array($locale, $rtl, true);
    }

    /**
     * HTML dir attribute ('rtl' veya 'ltr').
     */
    public function htmlDir(?string $locale = null): string
    {
        return $this->isRtl($locale) ? 'rtl' : 'ltr';
    }
}
