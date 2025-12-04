<?php

declare(strict_types=1);

namespace Rlnks\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Message\ResponseInterface;
use Rlnks\Exceptions\RateLimitException;
use Rlnks\Exceptions\RlnksException;

/**
 * HTTP client wrapper for RLNKS API communication.
 */
class HttpClient
{
    protected GuzzleClient $client;
    protected string $apiKey;
    protected string $baseUrl;
    protected array $defaultHeaders;

    /**
     * Rate limit information from last request.
     */
    protected ?int $rateLimitLimit = null;
    protected ?int $rateLimitRemaining = null;
    protected ?int $rateLimitReset = null;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.rlnks.com',
        array $options = []
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');

        $this->defaultHeaders = [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'rlnks-php-sdk/1.0.0',
        ];

        $this->client = new GuzzleClient(array_merge([
            'base_uri' => $this->baseUrl,
            'timeout' => $options['timeout'] ?? 30,
            'connect_timeout' => $options['connect_timeout'] ?? 10,
            'http_errors' => false,
        ], $options['guzzle'] ?? []));
    }

    /**
     * Make a GET request.
     */
    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * Make a POST request.
     */
    public function post(string $uri, array $data = []): array
    {
        return $this->request('POST', $uri, ['json' => $data]);
    }

    /**
     * Make a PUT request.
     */
    public function put(string $uri, array $data = []): array
    {
        return $this->request('PUT', $uri, ['json' => $data]);
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $uri): array
    {
        return $this->request('DELETE', $uri);
    }

    /**
     * Make a multipart POST request (for file uploads).
     */
    public function postMultipart(string $uri, array $multipart): array
    {
        return $this->request('POST', $uri, [
            'multipart' => $multipart,
        ], true);
    }

    /**
     * Execute an HTTP request.
     *
     * @throws RlnksException
     */
    protected function request(string $method, string $uri, array $options = [], bool $isMultipart = false): array
    {
        $headers = $this->defaultHeaders;

        if ($isMultipart) {
            unset($headers['Content-Type']);
        }

        $options['headers'] = array_merge($headers, $options['headers'] ?? []);

        try {
            $response = $this->client->request($method, $uri, $options);
            $this->extractRateLimitHeaders($response);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            throw new RlnksException(
                'HTTP request failed: ' . $e->getMessage(),
                'HTTP_ERROR',
                null,
                0,
                $e
            );
        }
    }

    /**
     * Handle API response.
     *
     * @throws RlnksException
     */
    protected function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $data = json_decode($body, true) ?? [];

        if ($statusCode >= 200 && $statusCode < 300) {
            return $data;
        }

        if ($statusCode === 204) {
            return ['success' => true];
        }

        $exception = RlnksException::fromResponse($data, $statusCode);

        if ($exception instanceof RateLimitException) {
            $exception->setRateLimitInfo(
                $this->rateLimitLimit,
                $this->rateLimitRemaining,
                $this->parseRetryAfter($response)
            );
        }

        throw $exception;
    }

    /**
     * Extract rate limit headers from response.
     */
    protected function extractRateLimitHeaders(ResponseInterface $response): void
    {
        $this->rateLimitLimit = $this->getHeaderInt($response, 'X-RateLimit-Limit');
        $this->rateLimitRemaining = $this->getHeaderInt($response, 'X-RateLimit-Remaining');
        $this->rateLimitReset = $this->getHeaderInt($response, 'X-RateLimit-Reset');
    }

    /**
     * Parse Retry-After header.
     */
    protected function parseRetryAfter(ResponseInterface $response): ?int
    {
        $header = $response->getHeaderLine('Retry-After');
        if (empty($header)) {
            return null;
        }

        if (is_numeric($header)) {
            return (int) $header;
        }

        $timestamp = strtotime($header);
        return $timestamp ? max(0, $timestamp - time()) : null;
    }

    /**
     * Get integer value from header.
     */
    protected function getHeaderInt(ResponseInterface $response, string $name): ?int
    {
        $value = $response->getHeaderLine($name);
        return $value !== '' ? (int) $value : null;
    }

    /**
     * Get rate limit information from last request.
     */
    public function getRateLimitInfo(): array
    {
        return [
            'limit' => $this->rateLimitLimit,
            'remaining' => $this->rateLimitRemaining,
            'reset' => $this->rateLimitReset,
        ];
    }

    /**
     * Get the base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
