<?php

namespace Nlk\Theme\FlexPage\DataAdapters;

/**
 * HybridAdapter — merges MySQL and API data.
 * Sections can request one source or both.
 */
class HybridAdapter
{
    public function __construct(
        private readonly MysqlAdapter $mysql,
        private readonly ApiAdapter   $api,
    ) {}

    /**
     * Resolve the appropriate adapter for a given source string.
     *
     * Supported: 'mysql' | 'api' | 'hybrid'
     */
    public function resolve(string $source): MysqlAdapter|ApiAdapter
    {
        return match ($source) {
            'api'   => $this->api,
            'mysql' => $this->mysql,
            default => $this->mysql,
        };
    }

    public function mysql(): MysqlAdapter
    {
        return $this->mysql;
    }

    public function api(): ApiAdapter
    {
        return $this->api;
    }

    /**
     * Merge products from MySQL and API, deduplicated by 'id'.
     *
     * @param  array<string, mixed>  $mysqlOptions
     * @param  array<string, mixed>  $apiParams
     */
    public function mergeProducts(array $mysqlOptions = [], array $apiParams = []): array
    {
        $fromDb  = $this->mysql->fetchProducts($mysqlOptions);
        $fromApi = $this->api->fetchProducts($apiParams);

        $seen = [];
        $merged = [];

        foreach (array_merge($fromDb, $fromApi) as $item) {
            $key = $item['id'] ?? spl_object_id((object)$item);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $merged[]   = $item;
            }
        }

        return $merged;
    }
}
