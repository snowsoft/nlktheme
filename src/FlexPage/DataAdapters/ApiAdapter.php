<?php

namespace Nlk\Theme\FlexPage\DataAdapters;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * REST API Adapter — calls external frontend-api service.
 * Stateless: configure base URL via config('theme.data_sources.api').
 */
class ApiAdapter
{
    private ?string $tenantId = null;

    public function __construct(
        private readonly string $baseUrl = '',
        private readonly string $apiKey  = '',
        private readonly int    $timeout = 5,
        private readonly int    $cacheTtl = 300,
    ) {}

    public static function make(): static
    {
        return new static(
            baseUrl:  config('theme.data_sources.api', ''),
            apiKey:   config('theme.data_sources.api_key', ''),
            timeout:  config('theme.data_sources.api_timeout', 5),
            cacheTtl: config('theme.builder.cache_ttl', 300),
        );
    }

    public function withTenant(string $tenantId): static
    {
        $clone = clone $this;
        $clone->tenantId = $tenantId;
        return $clone;
    }

    // ─── Core HTTP ────────────────────────────────────────────────────────────

    public function get(string $endpoint, array $params = []): array
    {
        if (empty($this->baseUrl)) {
            return [];
        }

        $cacheKey = "theme:api:{$this->tenantId}:" . md5($endpoint . serialize($params));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($endpoint, $params) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders($this->headers())
                    ->get(rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/'), $params);

                if ($response->successful()) {
                    $body = $response->json();
                    return $body['data'] ?? $body ?? [];
                }

                Log::warning('ThemeEngine API non-2xx', [
                    'endpoint' => $endpoint,
                    'status'   => $response->status(),
                    'tenant'   => $this->tenantId,
                ]);

            } catch (\Throwable $e) {
                Log::warning('ThemeEngine API error', [
                    'endpoint' => $endpoint,
                    'error'    => $e->getMessage(),
                    'tenant'   => $this->tenantId,
                ]);
            }

            return [];
        });
    }

    // ─── Convenience endpoints ────────────────────────────────────────────────

    public function fetchProducts(array $params = []): array
    {
        return $this->get('v1/products', $params);
    }

    public function fetchCategories(array $params = []): array
    {
        return $this->get('v1/categories', $params);
    }

    public function fetchProduct(int|string $id): array
    {
        return $this->get("v1/products/{$id}");
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function headers(): array
    {
        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($this->apiKey) {
            $headers['Authorization'] = "Bearer {$this->apiKey}";
        }

        if ($this->tenantId) {
            $headers['X-Tenant-ID'] = $this->tenantId;
        }

        return $headers;
    }
}
