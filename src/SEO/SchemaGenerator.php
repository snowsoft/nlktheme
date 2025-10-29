<?php

namespace Nlk\Theme\SEO;

class SchemaGenerator
{
    /**
     * Generate JSON-LD script tag.
     *
     * @param  array $data
     * @return string
     */
    public static function jsonLd(array $data)
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return '<script type="application/ld+json">' . PHP_EOL . $json . PHP_EOL . '</script>';
    }

    /**
     * Generate Organization schema.
     *
     * @param  array $data
     * @return array
     */
    public static function organization(array $data)
    {
        return [
            '@type' => 'Organization',
            '@id' => $data['url'] ?? '#organization',
            'name' => $data['name'] ?? '',
            'url' => $data['url'] ?? '',
            'logo' => $data['logo'] ?? '',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $data['phone'] ?? '',
                'contactType' => $data['contactType'] ?? 'customer support',
                'email' => $data['email'] ?? '',
            ],
            'sameAs' => $data['social'] ?? [],
        ];
    }

    /**
     * Generate BreadcrumbList schema.
     *
     * @param  array $items
     * @return array
     */
    public static function breadcrumbList(array $items)
    {
        $breadcrumbs = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        foreach ($items as $position => $item) {
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $item['name'] ?? '',
                'item' => $item['url'] ?? '',
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Generate Article schema.
     *
     * @param  array $data
     * @return array
     */
    public static function article(array $data)
    {
        return [
            '@type' => 'Article',
            'headline' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'image' => $data['image'] ?? [],
            'datePublished' => $data['published'] ?? '',
            'dateModified' => $data['modified'] ?? $data['published'] ?? '',
            'author' => [
                '@type' => 'Person',
                'name' => $data['author'] ?? '',
            ],
            'publisher' => $data['publisher'] ?? [],
        ];
    }

    /**
     * Generate Video schema.
     *
     * @param  array $data
     * @return array
     */
    public static function video(array $data)
    {
        return [
            '@type' => 'VideoObject',
            'name' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'thumbnailUrl' => $data['thumbnail'] ?? '',
            'uploadDate' => $data['uploadDate'] ?? '',
            'duration' => $data['duration'] ?? '',
            'contentUrl' => $data['url'] ?? '',
            'embedUrl' => $data['embedUrl'] ?? '',
        ];
    }
}

