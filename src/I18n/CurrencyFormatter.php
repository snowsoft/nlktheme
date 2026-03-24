<?php

namespace Nlk\Theme\I18n;

use NumberFormatter;

/**
 * CurrencyFormatter — Çok para birimi fiyat formatlama.
 *
 * PHP intl NumberFormatter tabanlı.
 * Fallback: basit string format.
 *
 * Kullanım:
 *   $fmt = app('nlk.currency');
 *   $fmt->format(1234.5, 'TRY', 'tr');   → "₺1.234,50"
 *   $fmt->format(1234.5, 'EUR', 'de');   → "1.234,50 €"
 *   $fmt->symbol('TRY');                 → "₺"
 *   $fmt->convert(100, 'TRY', 'USD');    → 3.15 (kur endpoint'inden)
 */
class CurrencyFormatter
{
    protected array $rates = [];
    protected array $symbols = [
        'TRY' => '₺',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'RUB' => '₽',
        'AED' => 'AED',
        'SAR' => 'SAR',
        'JPY' => '¥',
    ];

    /**
     * Para birimi sembolü döndürür.
     */
    public function symbol(string $currency): string
    {
        return $this->symbols[strtoupper($currency)] ?? $currency;
    }

    /**
     * Fiyatı biçimlendirir.
     *
     * @param  float   $amount    Tutar
     * @param  string  $currency  ISO 4217 kodu (TRY, USD, EUR...)
     * @param  string|null $locale PHP locale (tr, en-US, de...)
     */
    public function format(float $amount, string $currency = 'TRY', ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if (class_exists(NumberFormatter::class)) {
            try {
                $fmt = new NumberFormatter($locale, NumberFormatter::CURRENCY);
                return $fmt->formatCurrency($amount, strtoupper($currency));
            } catch (\Throwable) {
                // intl yok veya hata — fallback
            }
        }

        // Fallback formatter
        return $this->fallbackFormat($amount, $currency);
    }

    /**
     * Kısa format (sembol önde, ondalık 2 hane).
     */
    public function short(float $amount, string $currency = 'TRY'): string
    {
        $symbol = $this->symbol($currency);
        return $symbol . number_format($amount, 2, ',', '.');
    }

    /**
     * Büyük sayıları kısalt: 1500 → 1.5K, 1500000 → 1.5M
     */
    public function compact(float $amount, string $currency = 'TRY'): string
    {
        $symbol = $this->symbol($currency);
        if ($amount >= 1_000_000) {
            return $symbol . round($amount / 1_000_000, 1) . 'M';
        }
        if ($amount >= 1_000) {
            return $symbol . round($amount / 1_000, 1) . 'K';
        }
        return $this->short($amount, $currency);
    }

    /**
     * Döviz kurlarını ayarla (API'den veya config'den).
     *
     * @param  array  $rates  ['USD' => 32.5, 'EUR' => 35.0] (TRY base)
     */
    public function setRates(array $rates): self
    {
        $this->rates = $rates;
        return $this;
    }

    /**
     * Döviz çevirimi yapar.
     *
     * @param  float   $amount
     * @param  string  $from    Kaynak para birimi
     * @param  string  $to      Hedef para birimi
     */
    public function convert(float $amount, string $from = 'TRY', string $to = 'USD'): ?float
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if ($from === $to) return $amount;

        // TRY base varsayımı
        if (isset($this->rates[$to]) && $this->rates[$to] > 0) {
            // from → TRY → to
            $inTry = ($from === 'TRY')
                ? $amount
                : ($amount * ($this->rates[$from] ?? 1));
            return round($inTry / $this->rates[$to], 4);
        }

        return null; // Kur bulunamadı
    }

    // ─── Internal ─────────────────────────────────────────────────────────

    protected function fallbackFormat(float $amount, string $currency): string
    {
        $symbol = $this->symbol($currency);
        $formatted = number_format($amount, 2, ',', '.');
        return in_array($currency, ['TRY', 'RUB'])
            ? $formatted . ' ' . $symbol
            : $symbol . $formatted;
    }
}
