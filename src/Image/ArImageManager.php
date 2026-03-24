<?php

namespace Nlk\Theme\Image;

/**
 * ArImageManager — AR (Artırılmış Gerçeklik) görüntü desteği.
 *
 * CDN'in AI özelliklerini kullanarak:
 * - Arka plan kaldırma (no-bg) → ürünü beyaz/şeffaf arka plana koy
 * - Model-on-product overlay (model.glb / USDZ için URL üretme)
 * - AR Quick Look (iOS Safari) & Scene Viewer (Android Chrome) entegrasyonu
 */
class ArImageManager
{
    protected CdnImageManager $cdn;

    public function __construct(CdnImageManager $cdn)
    {
        $this->cdn = $cdn;
    }

    // ─── No-Background (AI) ───────────────────────────────────────────────

    /**
     * Arka planı kaldırılmış PNG URL'i döndürür.
     * E-ticaret ürün görseli → beyaz arka plan
     */
    public function noBgUrl(string $imageId, string $format = 'png'): string
    {
        return $this->cdn->noBgUrl($imageId, $format);
    }

    /**
     * Arka planı kaldırılmış WebP URL (daha küçük boyut).
     */
    public function noBgWebp(string $imageId): string
    {
        return $this->cdn->noBgUrl($imageId, 'webp');
    }

    // ─── AR HTML Viewer ───────────────────────────────────────────────────

    /**
     * iOS AR Quick Look / Android Scene Viewer için <a rel="ar"> etiketi.
     *
     * @param  string  $imageId     CDN görüntü ID (arka plan kaldırılmış PNG kullanılır)
     * @param  string|null $glbUrl  3D model URL (.glb — Scene Viewer için)
     * @param  string|null $usdzUrl 3D model URL (.usdz — iOS Quick Look için)
     * @param  array   $opts
     *   - title:   AR başlık (product adı)
     *   - price:   Fiyat string
     *   - call_to_action: "Satın Al" gibi CTA
     *   - fallback_img: AR desteklenmiyorsa gösterilecek görsel URL
     */
    public function arTag(
        string $imageId,
        ?string $glbUrl  = null,
        ?string $usdzUrl = null,
        array $opts = []
    ): string {
        $noBgUrl  = $this->noBgUrl($imageId, 'png');
        $thumbUrl = $this->cdn->variantUrl($imageId, 'medium');
        $title    = htmlspecialchars($opts['title'] ?? 'Ürün AR Görünümü', ENT_QUOTES);
        $price    = htmlspecialchars($opts['price'] ?? '', ENT_QUOTES);
        $cta      = htmlspecialchars($opts['call_to_action'] ?? 'Sepete Ekle', ENT_QUOTES);

        // Scene Viewer parametreleri (Android Chrome)
        $sceneViewerUrl = '';
        if ($glbUrl) {
            $svParams = http_build_query(array_filter([
                'file'            => $glbUrl,
                'mode'            => 'ar_preferred',
                'title'           => $opts['title'] ?? '',
                'link'            => $opts['link'] ?? '',
                'sound'           => null,
                'resizable'       => 'false',
                'enable_vertical_placement' => 'true',
            ]));
            $sceneViewerUrl = "intent://arvr.google.com/scene-viewer/1.0?{$svParams}#Intent;scheme=https;package=com.google.ar.core;action=android.intent.action.VIEW;S.browser_fallback_url=" . urlencode($thumbUrl) . ";end;";
        }

        // iOS Quick Look linki
        $iosUrl = $usdzUrl ?: $noBgUrl;

        $html = '<div class="ar-product-viewer" data-image-id="' . $imageId . '">' . PHP_EOL;
        $html .= '  <img src="' . $thumbUrl . '" alt="' . $title . '" class="ar-product-viewer__thumb" loading="lazy">' . PHP_EOL;

        // AR butonu (device detection JS ile açılır)
        $html .= '  <button class="ar-product-viewer__btn"' . PHP_EOL;
        $html .= '    data-glb="' . htmlspecialchars($glbUrl ?? '', ENT_QUOTES) . '"' . PHP_EOL;
        $html .= '    data-usdz="' . htmlspecialchars($usdzUrl ?? '', ENT_QUOTES) . '"' . PHP_EOL;
        $html .= '    data-no-bg="' . htmlspecialchars($noBgUrl, ENT_QUOTES) . '"' . PHP_EOL;
        $html .= '    data-ios-url="' . htmlspecialchars($iosUrl, ENT_QUOTES) . '"' . PHP_EOL;

        if ($sceneViewerUrl) {
            $html .= '    data-android-url="' . htmlspecialchars($sceneViewerUrl, ENT_QUOTES) . '"' . PHP_EOL;
        }

        $html .= '    aria-label="AR\'da Görüntüle">' . PHP_EOL;
        $html .= '    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2 2 7v10l10 5 10-5V7L12 2zm0 2.18L20 8.5l-8 4-8-4 8-3.82zM4 9.82l7 3.5V20l-7-3.5V9.82zm9 10.38v-6.88l7-3.5v6.88l-7 3.5z"/></svg>' . PHP_EOL;
        $html .= '    AR\'da Görüntüle' . PHP_EOL;
        $html .= '  </button>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        return $html;
    }

    /**
     * Basit no-bg ürün görseli <img> etiketi oluşturur.
     * AR viewer olmadan sadece arka planı kaldırılmış görsel.
     */
    public function noBgImg(string $imageId, array $attrs = []): string
    {
        $src   = $this->noBgUrl($imageId, 'png');
        $alt   = htmlspecialchars($attrs['alt'] ?? '', ENT_QUOTES);
        $class = htmlspecialchars(($attrs['class'] ?? '') . ' ar-no-bg-img', ENT_QUOTES);
        $w     = $attrs['width'] ?? '';
        $h     = $attrs['height'] ?? '';

        $tag = '<img src="' . $src . '" alt="' . $alt . '" class="' . $class . '"';
        $tag .= $w ? ' width="' . $w . '"' : '';
        $tag .= $h ? ' height="' . $h . '"' : '';
        $tag .= ' loading="lazy" decoding="async">';

        return $tag;
    }

    /**
     * AR JavaScript snippet'ini döndürür.
     * Layout'ta bir kez <head> veya </body> öncesinde ekleyin.
     */
    public function arScript(): string
    {
        return <<<'JS'
<script id="nlk-ar-viewer">
(function(){
  const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
  const isAndroid = /android/i.test(navigator.userAgent);

  document.querySelectorAll('.ar-product-viewer__btn').forEach(function(btn){
    btn.addEventListener('click', function(){
      if(isIos){
        const url = btn.dataset.iosUrl || btn.dataset.noBg;
        const a = document.createElement('a');
        a.setAttribute('rel','ar');
        a.setAttribute('href', url);
        a.appendChild(document.createElement('img'));
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      } else if(isAndroid && btn.dataset.androidUrl){
        window.location.href = btn.dataset.androidUrl;
      } else {
        // Fallback: no-bg görüntüyü yeni sekmede aç
        window.open(btn.dataset.noBg, '_blank');
      }
    });
  });
})();
</script>
JS;
    }
}
