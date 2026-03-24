<?php

namespace Nlk\Theme\SEO;

/**
 * SEO Manager — meta tags, Open Graph, Twitter Card, canonical, hreflang.
 * Used with @seo_head Blade directive.
 */
class SeoManager
{
    private array $meta     = [];
    private array $og       = [];
    private array $twitter  = [];
    private array $jsonLd   = [];
    private array $links    = [];  // canonical, hreflang, preload, preconnect
    private ?string $title  = null;

    // ─── Title ────────────────────────────────────────────────────────────────

    public function title(string $title, ?string $suffix = null): static
    {
        $this->title = $suffix ? "{$title} | {$suffix}" : $title;
        $this->og('title', $this->title);
        $this->twitter('title', $this->title);
        return $this;
    }

    // ─── Meta ─────────────────────────────────────────────────────────────────

    public function description(string $desc): static
    {
        $desc = mb_strimwidth(strip_tags($desc), 0, 160, '…');
        $this->meta['description'] = $desc;
        $this->og('description', $desc);
        $this->twitter('description', $desc);
        return $this;
    }

    public function keywords(string $keywords): static
    {
        $this->meta['keywords'] = $keywords;
        return $this;
    }

    public function robots(string $value = 'index,follow'): static
    {
        $this->meta['robots'] = $value;
        return $this;
    }

    public function noindex(): static
    {
        return $this->robots('noindex,nofollow');
    }

    // ─── Open Graph ───────────────────────────────────────────────────────────

    public function og(string $property, string $content): static
    {
        $this->og[$property] = $content;
        return $this;
    }

    public function ogType(string $type = 'website'): static
    {
        return $this->og('type', $type);
    }

    public function ogImage(string $url, ?int $width = null, ?int $height = null): static
    {
        $this->og['image'] = $url;
        if ($width)  $this->og['image:width']  = (string) $width;
        if ($height) $this->og['image:height'] = (string) $height;
        return $this;
    }

    /** Product-specific OG tags */
    public function ogProduct(float $price, string $currency = 'TRY', string $availability = 'in stock'): static
    {
        $this->og['product:price:amount']   = (string) $price;
        $this->og['product:price:currency'] = $currency;
        $this->og['product:availability']   = $availability;
        return $this->ogType('product');
    }

    // ─── Twitter Card ─────────────────────────────────────────────────────────

    public function twitter(string $name, string $content): static
    {
        $this->twitter[$name] = $content;
        return $this;
    }

    public function twitterCard(string $type = 'summary_large_image'): static
    {
        return $this->twitter('card', $type);
    }

    public function twitterSite(string $handle): static
    {
        return $this->twitter('site', $handle);
    }

    // ─── Links ────────────────────────────────────────────────────────────────

    public function canonical(string $url): static
    {
        $this->links[] = "<link rel=\"canonical\" href=\"" . htmlspecialchars($url, ENT_QUOTES) . "\">";
        return $this;
    }

    public function hreflang(string $lang, string $url): static
    {
        $url = htmlspecialchars($url, ENT_QUOTES);
        $this->links[] = "<link rel=\"alternate\" hreflang=\"{$lang}\" href=\"{$url}\">";
        return $this;
    }

    public function preconnect(string $url, bool $crossorigin = false): static
    {
        $co = $crossorigin ? ' crossorigin' : '';
        $this->links[] = "<link rel=\"preconnect\" href=\"{$url}\"{$co}>";
        return $this;
    }

    public function dnsPrefetch(string $url): static
    {
        $this->links[] = "<link rel=\"dns-prefetch\" href=\"{$url}\">";
        return $this;
    }

    public function preload(string $url, string $as, ?string $type = null, bool $crossorigin = false): static
    {
        $typeAttr = $type ? " type=\"{$type}\"" : '';
        $co       = $crossorigin ? ' crossorigin' : '';
        $this->links[] = "<link rel=\"preload\" href=\"{$url}\" as=\"{$as}\"{$typeAttr}{$co}>";
        return $this;
    }

    // ─── JSON-LD Rich Snippets ────────────────────────────────────────────────

    public function addJsonLd(array $schema): static
    {
        // Wrap in @context if missing
        if (!isset($schema['@context'])) {
            $schema = array_merge(['@context' => 'https://schema.org'], $schema);
        }
        $this->jsonLd[] = $schema;
        return $this;
    }

    // Convenience helpers
    public function addProductSchema(array $data): static
    {
        return $this->addJsonLd(ProductSchema::generate($data));
    }

    public function addBreadcrumb(array $items): static
    {
        return $this->addJsonLd(['@context' => 'https://schema.org'] + SchemaGenerator::breadcrumbList($items));
    }

    public function addWebSiteSchema(string $name, string $url, ?string $searchUrl = null): static
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => $name,
            'url'      => $url,
        ];
        if ($searchUrl) {
            $schema['potentialAction'] = [
                '@type'       => 'SearchAction',
                'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => $searchUrl],
                'query-input' => 'required name=search_term_string',
            ];
        }
        return $this->addJsonLd($schema);
    }

    public function addFaqSchema(array $faqs): static
    {
        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(fn($faq) => [
                '@type'          => 'Question',
                'name'           => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
            ], $faqs),
        ];
        return $this->addJsonLd($schema);
    }

    public function addItemListSchema(array $items, string $listName = 'Products'): static
    {
        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'name'            => $listName,
            'itemListElement' => array_map(fn($item, $pos) => [
                '@type'    => 'ListItem',
                'position' => $pos + 1,
                'name'     => $item['name'] ?? '',
                'url'      => $item['url'] ?? '',
                'image'    => $item['image'] ?? '',
            ], $items, array_keys($items)),
        ];
        return $this->addJsonLd($schema);
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render(): string
    {
        $html = '';

        // Title
        if ($this->title) {
            $html .= '<title>' . htmlspecialchars($this->title, ENT_QUOTES) . '</title>' . "\n";
        }

        // Meta tags
        foreach ($this->meta as $name => $content) {
            $c     = htmlspecialchars($content, ENT_QUOTES);
            $html .= "<meta name=\"{$name}\" content=\"{$c}\">\n";
        }

        // Open Graph
        foreach ($this->og as $property => $content) {
            $c     = htmlspecialchars($content, ENT_QUOTES);
            $html .= "<meta property=\"og:{$property}\" content=\"{$c}\">\n";
        }

        // Twitter Card
        foreach ($this->twitter as $name => $content) {
            $c     = htmlspecialchars($content, ENT_QUOTES);
            $html .= "<meta name=\"twitter:{$name}\" content=\"{$c}\">\n";
        }

        // Links (canonical, hreflang, preload, preconnect)
        foreach ($this->links as $link) {
            $html .= $link . "\n";
        }

        // JSON-LD
        foreach ($this->jsonLd as $schema) {
            $json  = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $html .= "<script type=\"application/ld+json\">\n{$json}\n</script>\n";
        }

        return $html;
    }
}
