<?php

namespace Nlk\Theme\GDPR;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;

/**
 * CookieConsentManager — GDPR Cookie izni yönetimi.
 *
 * Desteklenen kategoriler:
 *   - necessary   : zorunlu (her zaman aktif)
 *   - analytics   : analitik (GA4, Hotjar vb.)
 *   - marketing   : pazarlama (GTM, FB Pixel, Google Ads)
 *   - preferences : tercih (dil, tema vb.)
 *
 * GTM Consent Mode v2 ile entegre.
 */
class CookieConsentManager
{
    protected const COOKIE_NAME    = 'nlk_cookie_consent';
    protected const COOKIE_DAYS    = 365;
    protected array  $granted      = [];
    protected bool   $initialized  = false;

    public function __construct()
    {
        $this->load();
    }

    // ─── Kategori Sorguları ───────────────────────────────────────────────

    public function isGranted(string $category): bool
    {
        if ($category === 'necessary') return true;
        return in_array($category, $this->granted, true);
    }

    public function analyticsGranted(): bool  { return $this->isGranted('analytics'); }
    public function marketingGranted(): bool   { return $this->isGranted('marketing'); }
    public function preferencesGranted(): bool { return $this->isGranted('preferences'); }
    public function allGranted(): bool         { return $this->analyticsGranted() && $this->marketingGranted(); }

    /** Kullanıcı herhangi bir seçim yaptı mı */
    public function hasConsented(): bool { return $this->initialized; }

    public function getGranted(): array { return $this->granted; }

    // ─── İzin Güncelleme ─────────────────────────────────────────────────

    /**
     * Kategorileri güncelle ve cookie kaydet.
     *
     * @param  array  $categories  ['analytics', 'marketing', 'preferences']
     */
    public function grant(array $categories): void
    {
        $valid = array_filter($categories, fn($c) => in_array($c, ['analytics', 'marketing', 'preferences'], true));
        $this->granted = array_values($valid);
        $this->initialized = true;
        $this->save();
    }

    /** Tüm opsiyonel kategorilere izin ver */
    public function grantAll(): void
    {
        $this->grant(['analytics', 'marketing', 'preferences']);
    }

    /** Yalnızca zorunlu tutarak diğerlerini reddet */
    public function rejectAll(): void
    {
        $this->granted = [];
        $this->initialized = true;
        $this->save();
    }

    // ─── GTM Consent Mode v2 ─────────────────────────────────────────────

    /**
     * GTM Consent Mode v2 başlangıç ayarlarını döndürür (default denied).
     */
    public function gtmDefaultConsent(): string
    {
        return json_encode([
            'analytics_storage'    => 'denied',
            'ad_storage'           => 'denied',
            'ad_user_data'         => 'denied',
            'ad_personalization'   => 'denied',
            'functionality_storage'=> 'granted',
            'security_storage'     => 'granted',
        ]);
    }

    /**
     * Kullanıcı izin verdikten sonra GTM'ye gönderilecek consent update.
     */
    public function gtmUpdateConsent(): string
    {
        return json_encode([
            'analytics_storage'  => $this->analyticsGranted() ? 'granted' : 'denied',
            'ad_storage'         => $this->marketingGranted()  ? 'granted' : 'denied',
            'ad_user_data'       => $this->marketingGranted()  ? 'granted' : 'denied',
            'ad_personalization' => $this->marketingGranted()  ? 'granted' : 'denied',
        ]);
    }

    // ─── Cookie Banner HTML ───────────────────────────────────────────────

    /**
     * Cookie banner HTML çıktısı.
     * @cookie_banner direktifi bu metodu çağırır.
     */
    public function renderBanner(): string
    {
        if ($this->hasConsented()) return '';

        $privacyUrl = config('theme.gdpr.privacy_url', '/gizlilik');
        $cookieUrl  = config('theme.gdpr.cookie_url', '/cerez-politikasi');

        return <<<HTML
<div id="nlk-cookie-banner" class="nlk-cookie-banner" role="dialog" aria-label="Cookie Tercihi" aria-modal="true">
  <div class="nlk-cookie-banner__inner">
    <div class="nlk-cookie-banner__text">
      <p>
        Sitemizi geliştirmek ve kişiselleştirilmiş deneyim sunmak için çerezler kullanıyoruz.
        <a href="{$cookieUrl}" target="_blank" rel="noopener">Çerez Politikası</a> ·
        <a href="{$privacyUrl}" target="_blank" rel="noopener">Gizlilik</a>
      </p>
    </div>
    <div class="nlk-cookie-banner__actions">
      <button class="nlk-cookie-banner__btn nlk-cookie-banner__btn--secondary" onclick="NlkConsent.settings()">
        Tercihleri Yönet
      </button>
      <button class="nlk-cookie-banner__btn nlk-cookie-banner__btn--outline" onclick="NlkConsent.rejectAll()">
        Reddet
      </button>
      <button class="nlk-cookie-banner__btn nlk-cookie-banner__btn--primary" onclick="NlkConsent.acceptAll()">
        Tümünü Kabul Et
      </button>
    </div>
  </div>
</div>

<!-- Detaylı cookie ayarları modal -->
<div id="nlk-cookie-modal" class="nlk-cookie-modal" hidden role="dialog" aria-modal="true" aria-label="Cookie Ayarları">
  <div class="nlk-cookie-modal__inner">
    <h2>Çerez Ayarları</h2>
    <div class="nlk-cookie-modal__item">
      <label><input type="checkbox" checked disabled> Zorunlu Çerezler</label>
      <small>Her zaman aktif, siteyi çalıştırmak için gerekli.</small>
    </div>
    <div class="nlk-cookie-modal__item">
      <label><input type="checkbox" id="nlk-consent-analytics"> Analitik Çerezler</label>
      <small>Siteyi nasıl kullandığınızı anlamamıza yardımcı olur (GA4, Hotjar).</small>
    </div>
    <div class="nlk-cookie-modal__item">
      <label><input type="checkbox" id="nlk-consent-marketing"> Pazarlama Çerezleri</label>
      <small>Kişiselleştirilmiş reklamlar için (FA, Google Ads, GTM).</small>
    </div>
    <div class="nlk-cookie-modal__item">
      <label><input type="checkbox" id="nlk-consent-preferences"> Tercih Çerezleri</label>
      <small>Dil ve tema tercihlerinizi hatırlar.</small>
    </div>
    <div class="nlk-cookie-modal__actions">
      <button onclick="NlkConsent.saveSettings()">Seçimi Kaydet</button>
      <button onclick="NlkConsent.acceptAll()">Tümünü Kabul Et</button>
    </div>
  </div>
</div>

<script>
window.NlkConsent = {
  apiUrl: '/theme/consent',
  banner: document.getElementById('nlk-cookie-banner'),
  modal:  document.getElementById('nlk-cookie-modal'),
  acceptAll: function(){
    this._send(['analytics','marketing','preferences']);
  },
  rejectAll: function(){
    this._send([]);
  },
  settings: function(){
    this.banner.hidden = true;
    this.modal.hidden  = false;
  },
  saveSettings: function(){
    var cats = [];
    if(document.getElementById('nlk-consent-analytics').checked)   cats.push('analytics');
    if(document.getElementById('nlk-consent-marketing').checked)    cats.push('marketing');
    if(document.getElementById('nlk-consent-preferences').checked)  cats.push('preferences');
    this._send(cats);
  },
  _send: function(cats){
    fetch(this.apiUrl, {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content},
      body: JSON.stringify({categories: cats})
    }).then(function(){
      window.NlkConsent.banner.hidden = true;
      window.NlkConsent.modal.hidden  = true;
      // GTM Consent Mode v2 update
      if(typeof gtag === 'function'){
        gtag('consent','update',{
          analytics_storage: cats.includes('analytics') ? 'granted' : 'denied',
          ad_storage:        cats.includes('marketing')  ? 'granted' : 'denied',
          ad_user_data:      cats.includes('marketing')  ? 'granted' : 'denied',
          ad_personalization:cats.includes('marketing')  ? 'granted' : 'denied',
        });
      }
    });
  }
};
</script>
HTML;
    }

    // ─── Internal ─────────────────────────────────────────────────────────

    protected function load(): void
    {
        $raw = Request::cookie(self::COOKIE_NAME);
        if ($raw) {
            $data = json_decode(base64_decode($raw), true);
            if (is_array($data)) {
                $this->granted     = $data['granted'] ?? [];
                $this->initialized = true;
            }
        }
    }

    protected function save(): void
    {
        $payload = base64_encode(json_encode(['granted' => $this->granted]));
        Cookie::queue(
            self::COOKIE_NAME,
            $payload,
            self::COOKIE_DAYS * 24 * 60 // dakika
        );
    }
}
