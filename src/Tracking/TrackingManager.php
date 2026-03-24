<?php

namespace Nlk\Theme\Tracking;

/**
 * Central Tracking Manager.
 * Manages Google GTM/GA4/Ads + Facebook Meta Pixel.
 *
 * Config keys (per tenant, stored in theme_page_settings.settings or DB ayarlar):
 *   tracking.gtm_id       — Google Tag Manager container ID (GTM-XXXX)
 *   tracking.ga4_id       — GA4 Measurement ID (G-XXXXXXX)
 *   tracking.google_ads_id — Google Ads Conversion ID (AW-XXXXXXXXX)
 *   tracking.fb_pixel_id  — Facebook Pixel ID
 *   tracking.enabled      — global on/off
 *   tracking.consent_mode — GDPR consent mode (default|granted)
 */
class TrackingManager
{
    /** @var array<string, mixed> */
    private array $config = [];

    /** @var array<string, array<string, mixed>> — queued e-commerce events */
    private array $events = [];

    /** @var array<string, string> — extra data layer pushes */
    private array $dataLayer = [];

    // ─── Bootstrap ────────────────────────────────────────────────────────────

    public function configure(array $config): static
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? true);
    }

    public function hasGtm(): bool
    {
        return !empty($this->config['gtm_id']);
    }

    public function hasGa4(): bool
    {
        return !empty($this->config['ga4_id']);
    }

    public function hasGoogleAds(): bool
    {
        return !empty($this->config['google_ads_id']);
    }

    public function hasFbPixel(): bool
    {
        return !empty($this->config['fb_pixel_id']);
    }

    // ─── Data Layer Pushes ────────────────────────────────────────────────────

    /** Push arbitrary key-value to GTM dataLayer */
    public function push(string $key, mixed $value): static
    {
        $this->dataLayer[$key] = $value;
        return $this;
    }

    // ─── E-Commerce Events ────────────────────────────────────────────────────

    /**
     * Fire a PageView event (all platforms).
     */
    public function pageView(array $meta = []): static
    {
        $this->events['page_view'] = $meta;
        return $this;
    }

    /**
     * ViewContent / view_item — product detail page.
     *
     * @param  array{id: string|int, name: string, price: float, currency: string, category?: string, brand?: string}  $product
     */
    public function viewContent(array $product): static
    {
        $this->events['view_content'] = $product;
        return $this;
    }

    /**
     * AddToCart / add_to_cart event.
     */
    public function addToCart(array $item, float $value, string $currency = 'TRY'): static
    {
        $this->events['add_to_cart'] = compact('item', 'value', 'currency');
        return $this;
    }

    /**
     * InitiateCheckout / begin_checkout event.
     */
    public function initiateCheckout(float $value, string $currency, int $numItems, array $contents = []): static
    {
        $this->events['initiate_checkout'] = compact('value', 'currency', 'numItems', 'contents');
        return $this;
    }

    /**
     * Purchase / purchase event.
     *
     * @param  array{id: string, value: float, currency: string, items: array}  $order
     */
    public function purchase(array $order): static
    {
        $this->events['purchase'] = $order;
        return $this;
    }

    /**
     * Search event.
     */
    public function search(string $query): static
    {
        $this->events['search'] = ['query' => $query];
        return $this;
    }

    // ─── Renderers ────────────────────────────────────────────────────────────

    /**
     * Render everything that goes in <head>.
     */
    public function renderHead(): string
    {
        if (!$this->isEnabled()) return '';

        $html = '';

        // Google Tag Manager (head snippet)
        if ($this->hasGtm()) {
            $html .= $this->gtmHead();
        }

        // GA4 direct (when no GTM)
        if (!$this->hasGtm() && $this->hasGa4()) {
            $html .= $this->ga4Head();
        }

        // Facebook Pixel (noscript not in head, but base pixel init is)
        if ($this->hasFbPixel()) {
            $html .= $this->fbPixelHead();
        }

        return $html;
    }

    /**
     * Render everything that goes right after <body>.
     */
    public function renderBody(): string
    {
        if (!$this->isEnabled()) return '';

        $html = '';

        // GTM noscript
        if ($this->hasGtm()) {
            $html .= $this->gtmBody();
        }

        // FB Pixel noscript fallback
        if ($this->hasFbPixel()) {
            $html .= $this->fbPixelNoscript();
        }

        return $html;
    }

    /**
     * Render queued event scripts (inline JS — place before </body>).
     */
    public function renderEvents(): string
    {
        if (!$this->isEnabled() || empty($this->events)) return '';

        $html = '';

        foreach ($this->events as $eventName => $data) {
            $html .= $this->renderEvent($eventName, $data);
        }

        return $html;
    }

    // ─── Private renderers ────────────────────────────────────────────────────

    private function gtmHead(): string
    {
        $id  = htmlspecialchars($this->config['gtm_id'], ENT_QUOTES);
        $dl  = $this->buildDataLayerScript();
        $consent = $this->consentModeScript();

        return <<<HTML
<!-- Google Tag Manager -->
{$consent}
{$dl}
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$id}');</script>
<!-- End Google Tag Manager -->
HTML;
    }

    private function gtmBody(): string
    {
        $id = htmlspecialchars($this->config['gtm_id'], ENT_QUOTES);
        return <<<HTML
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$id}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
HTML;
    }

    private function ga4Head(): string
    {
        $id = htmlspecialchars($this->config['ga4_id'], ENT_QUOTES);
        return <<<HTML
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$id}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$id}');
</script>
<!-- End Google Analytics 4 -->
HTML;
    }

    private function fbPixelHead(): string
    {
        $id  = htmlspecialchars($this->config['fb_pixel_id'], ENT_QUOTES);
        return <<<HTML
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window,document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$id}');
fbq('track', 'PageView');
</script>
<!-- End Meta Pixel Code -->
HTML;
    }

    private function fbPixelNoscript(): string
    {
        $id = htmlspecialchars($this->config['fb_pixel_id'], ENT_QUOTES);
        return <<<HTML
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={$id}&ev=PageView&noscript=1"/></noscript>
HTML;
    }

    private function buildDataLayerScript(): string
    {
        if (empty($this->dataLayer)) {
            return '<script>window.dataLayer = window.dataLayer || [];</script>';
        }

        $dlJson = json_encode($this->dataLayer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return "<script>window.dataLayer = window.dataLayer || []; dataLayer.push({$dlJson});</script>";
    }

    private function consentModeScript(): string
    {
        if (($this->config['consent_mode'] ?? 'default') !== 'default') {
            return '';
        }

        // Google Consent Mode v2 — default denied (GDPR)
        return <<<'HTML'
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('consent', 'default', {
    'ad_storage': 'denied',
    'ad_user_data': 'denied',
    'ad_personalization': 'denied',
    'analytics_storage': 'denied',
    'wait_for_update': 500
  });
  gtag('set', 'ads_data_redaction', true);
  gtag('set', 'url_passthrough', true);
</script>
HTML;
    }

    private function renderEvent(string $eventName, array $data): string
    {
        $scripts = [];

        // ─ GA4 / GTM event ─
        $ga4Event = match ($eventName) {
            'view_content'     => $this->ga4ViewItem($data),
            'add_to_cart'      => $this->ga4AddToCart($data),
            'initiate_checkout'=> $this->ga4BeginCheckout($data),
            'purchase'         => $this->ga4Purchase($data),
            'search'           => $this->ga4Search($data),
            default            => null,
        };

        if ($ga4Event) {
            $scripts[] = "<script>if(typeof gtag==='function'){{$ga4Event}}</script>";
        }

        // ─ Facebook Pixel event ─
        if ($this->hasFbPixel()) {
            $fbEvent = match ($eventName) {
                'view_content'      => $this->fbViewContent($data),
                'add_to_cart'       => $this->fbAddToCart($data),
                'initiate_checkout' => $this->fbInitiateCheckout($data),
                'purchase'          => $this->fbPurchase($data),
                'search'            => $this->fbSearch($data),
                default             => null,
            };
            if ($fbEvent) {
                $scripts[] = "<script>if(typeof fbq==='function'){{$fbEvent}}</script>";
            }
        }

        return implode("\n", $scripts);
    }

    // ─── GA4 Ecommerce Events ──────────────────────────────────────────────────

    private function ga4ViewItem(array $d): string
    {
        $json = json_encode([
            'currency' => $d['currency'] ?? 'TRY',
            'value'    => $d['price'] ?? 0,
            'items'    => [[
                'item_id'       => $d['id'] ?? '',
                'item_name'     => $d['name'] ?? '',
                'item_category' => $d['category'] ?? '',
                'item_brand'    => $d['brand'] ?? '',
                'price'         => $d['price'] ?? 0,
                'quantity'      => 1,
            ]],
        ], JSON_UNESCAPED_UNICODE);
        return "gtag('event','view_item',{$json});";
    }

    private function ga4AddToCart(array $d): string
    {
        $json = json_encode([
            'currency' => $d['currency'] ?? 'TRY',
            'value'    => $d['value'] ?? 0,
            'items'    => [$d['item'] ?? []],
        ], JSON_UNESCAPED_UNICODE);
        return "gtag('event','add_to_cart',{$json});";
    }

    private function ga4BeginCheckout(array $d): string
    {
        $json = json_encode([
            'currency'  => $d['currency'] ?? 'TRY',
            'value'     => $d['value'] ?? 0,
            'num_items' => $d['numItems'] ?? 1,
            'items'     => $d['contents'] ?? [],
        ], JSON_UNESCAPED_UNICODE);
        return "gtag('event','begin_checkout',{$json});";
    }

    private function ga4Purchase(array $d): string
    {
        $json = json_encode([
            'transaction_id' => $d['id'] ?? '',
            'value'          => $d['value'] ?? 0,
            'currency'       => $d['currency'] ?? 'TRY',
            'tax'            => $d['tax'] ?? 0,
            'shipping'       => $d['shipping'] ?? 0,
            'items'          => $d['items'] ?? [],
        ], JSON_UNESCAPED_UNICODE);
        return "gtag('event','purchase',{$json});";
    }

    private function ga4Search(array $d): string
    {
        $q = json_encode($d['query'] ?? '');
        return "gtag('event','search',{search_term:{$q}});";
    }

    // ─── Facebook Pixel Events ────────────────────────────────────────────────

    private function fbViewContent(array $d): string
    {
        $json = json_encode([
            'content_ids'  => [$d['id'] ?? ''],
            'content_name' => $d['name'] ?? '',
            'content_type' => 'product',
            'currency'     => $d['currency'] ?? 'TRY',
            'value'        => $d['price'] ?? 0,
        ], JSON_UNESCAPED_UNICODE);
        return "fbq('track','ViewContent',{$json});";
    }

    private function fbAddToCart(array $d): string
    {
        $item = $d['item'] ?? [];
        $json = json_encode([
            'content_ids'  => [$item['id'] ?? ''],
            'content_name' => $item['name'] ?? '',
            'content_type' => 'product',
            'currency'     => $d['currency'] ?? 'TRY',
            'value'        => $d['value'] ?? 0,
        ], JSON_UNESCAPED_UNICODE);
        return "fbq('track','AddToCart',{$json});";
    }

    private function fbInitiateCheckout(array $d): string
    {
        $json = json_encode([
            'currency'    => $d['currency'] ?? 'TRY',
            'value'       => $d['value'] ?? 0,
            'num_items'   => $d['numItems'] ?? 1,
            'content_ids' => array_column($d['contents'] ?? [], 'id'),
        ], JSON_UNESCAPED_UNICODE);
        return "fbq('track','InitiateCheckout',{$json});";
    }

    private function fbPurchase(array $d): string
    {
        $json = json_encode([
            'currency'    => $d['currency'] ?? 'TRY',
            'value'       => $d['value'] ?? 0,
            'content_ids' => array_column($d['items'] ?? [], 'item_id'),
            'content_type'=> 'product',
            'order_id'    => $d['id'] ?? '',
        ], JSON_UNESCAPED_UNICODE);
        return "fbq('track','Purchase',{$json});";
    }

    private function fbSearch(array $d): string
    {
        $q = json_encode(['search_string' => $d['query'] ?? ''], JSON_UNESCAPED_UNICODE);
        return "fbq('track','Search',{$q});";
    }
}
