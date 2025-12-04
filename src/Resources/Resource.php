<?php

declare(strict_types=1);

namespace Rlnks\Resources;

use Rlnks\Http\HttpClient;

/**
 * Base class for API resources.
 */
abstract class Resource
{
    protected HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Build query parameters, removing null values.
     */
    protected function buildQuery(array $params): array
    {
        return array_filter($params, fn ($value) => $value !== null);
    }
}
