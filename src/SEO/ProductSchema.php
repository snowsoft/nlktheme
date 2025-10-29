<?php

namespace Nlk\Theme\SEO;

class ProductSchema
{
    /**
     * Generate Product schema for e-commerce.
     *
     * @param  array $data
     * @return array
     */
    public static function generate(array $data)
    {
        $schema = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'image' => is_array($data['image'] ?? null) ? $data['image'] : [($data['image'] ?? '')],
            'sku' => $data['sku'] ?? '',
            'mpn' => $data['mpn'] ?? '',
            'brand' => isset($data['brand']) && is_array($data['brand'])
                ? $data['brand']
                : (isset($data['brand']) ? ['@type' => 'Brand', 'name' => $data['brand']] : null),
            'category' => $data['category'] ?? '',
        ];

        // Offers
        if (isset($data['offers'])) {
            if (is_array($data['offers']) && isset($data['offers'][0]) && is_array($data['offers'][0])) {
                // Multiple offers
                $schema['offers'] = [];
                foreach ($data['offers'] as $offer) {
                    $schema['offers'][] = static::buildOffer($offer);
                }
            } else {
                // Single offer
                $schema['offers'] = static::buildOffer($data['offers']);
            }
        }

        // Aggregate Rating
        if (isset($data['aggregateRating'])) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $data['aggregateRating']['ratingValue'] ?? 0,
                'reviewCount' => $data['aggregateRating']['reviewCount'] ?? 0,
                'bestRating' => $data['aggregateRating']['bestRating'] ?? 5,
                'worstRating' => $data['aggregateRating']['worstRating'] ?? 1,
            ];
        }

        // Reviews
        if (isset($data['review']) && is_array($data['review'])) {
            $schema['review'] = [];
            foreach ($data['review'] as $review) {
                $schema['review'][] = [
                    '@type' => 'Review',
                    'author' => isset($review['author']) && is_array($review['author'])
                        ? $review['author']
                        : ['@type' => 'Person', 'name' => ($review['author'] ?? 'Anonymous')],
                    'datePublished' => $review['datePublished'] ?? '',
                    'reviewBody' => $review['reviewBody'] ?? '',
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $review['reviewRating']['ratingValue'] ?? 0,
                        'bestRating' => $review['reviewRating']['bestRating'] ?? 5,
                    ],
                ];
            }
        }

        return $schema;
    }

    /**
     * Build offer schema.
     *
     * @param  array $offer
     * @return array
     */
    protected static function buildOffer(array $offer)
    {
        $offerSchema = [
            '@type' => 'Offer',
            'price' => $offer['price'] ?? 0,
            'priceCurrency' => $offer['currency'] ?? ($offer['priceCurrency'] ?? 'USD'),
            'availability' => isset($offer['availability'])
                ? (strpos($offer['availability'], 'schema.org') !== false
                    ? $offer['availability']
                    : 'https://schema.org/' . ucfirst($offer['availability']))
                : 'https://schema.org/InStock',
            'url' => $offer['url'] ?? '',
        ];

        if (isset($offer['priceValidUntil'])) {
            $offerSchema['priceValidUntil'] = $offer['priceValidUntil'];
        }

        if (isset($offer['seller'])) {
            $offerSchema['seller'] = is_array($offer['seller'])
                ? $offer['seller']
                : ['@type' => 'Organization', 'name' => $offer['seller']];
        }

        return $offerSchema;
    }
}

