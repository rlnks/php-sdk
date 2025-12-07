<?php

declare(strict_types=1);

namespace Rlnks;

use Rlnks\Http\HttpClient;
use Rlnks\Resources\Account;
use Rlnks\Resources\Analytics;
use Rlnks\Resources\CustomDomains;
use Rlnks\Resources\DeviceReports;
use Rlnks\Resources\EphemeralLinks;
use Rlnks\Resources\Images;
use Rlnks\Resources\Trees;
use Rlnks\Resources\TreeVariables;
use Rlnks\Resources\Webhooks;

/**
 * RLNKS API Client.
 *
 * The main entry point for interacting with the RLNKS API.
 *
 * Example usage:
 * ```php
 * $client = new Rlnks\Client('rlnks_your_api_key');
 *
 * // List trees
 * $trees = $client->trees->list();
 *
 * // Create a tree
 * $tree = $client->trees->create([
 *     'name' => 'My Campaign',
 *     'type' => 'image',
 * ]);
 *
 * // Upload an image
 * $image = $client->images->upload('/path/to/image.jpg');
 *
 * // Get analytics
 * $analytics = $client->analytics->getTreeAnalytics($treeId);
 * ```
 */
class Client
{
    public const VERSION = '1.0.0';
    public const DEFAULT_BASE_URL = 'https://app.rlnks.com/api/v1';

    protected HttpClient $http;

    /**
     * Trees resource.
     */
    public Trees $trees;

    /**
     * Images resource.
     */
    public Images $images;

    /**
     * Analytics resource.
     */
    public Analytics $analytics;

    /**
     * Webhooks resource.
     */
    public Webhooks $webhooks;

    /**
     * Account resource.
     */
    public Account $account;

    /**
     * Device Reports resource.
     */
    public DeviceReports $deviceReports;

    /**
     * Tree Variables resource.
     */
    public TreeVariables $treeVariables;

    /**
     * Custom Domains resource.
     */
    public CustomDomains $customDomains;

    /**
     * Ephemeral/Test Links resource.
     */
    public EphemeralLinks $ephemeralLinks;

    /**
     * Create a new RLNKS client instance.
     *
     * @param string $apiKey Your RLNKS API key (starts with 'rlnks_')
     * @param array $options Configuration options:
     *                       - base_url: API base URL (default: https://app.rlnks.com/api/v1)
     *                       - timeout: Request timeout in seconds (default: 30)
     *                       - connect_timeout: Connection timeout in seconds (default: 10)
     *                       - guzzle: Additional Guzzle options
     */
    public function __construct(string $apiKey, array $options = [])
    {
        $baseUrl = $options['base_url'] ?? self::DEFAULT_BASE_URL;

        $this->http = new HttpClient($apiKey, $baseUrl, $options);

        $this->trees = new Trees($this->http);
        $this->treeVariables = new TreeVariables($this->http);
        $this->images = new Images($this->http);
        $this->analytics = new Analytics($this->http);
        $this->webhooks = new Webhooks($this->http);
        $this->account = new Account($this->http);
        $this->deviceReports = new DeviceReports($this->http);
        $this->customDomains = new CustomDomains($this->http);
        $this->ephemeralLinks = new EphemeralLinks($this->http);
    }

    /**
     * Get the HTTP client instance.
     */
    public function getHttpClient(): HttpClient
    {
        return $this->http;
    }

    /**
     * Get rate limit information from the last request.
     */
    public function getRateLimitInfo(): array
    {
        return $this->http->getRateLimitInfo();
    }
}
