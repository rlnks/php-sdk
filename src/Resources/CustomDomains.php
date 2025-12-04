<?php

declare(strict_types=1);

namespace Rlnks\Resources;

use Rlnks\Models\CustomDomain;

/**
 * Custom Domains resource for managing custom domains.
 *
 * Custom domains allow you to serve your links from your own domain
 * instead of the default RLNKS domain. This is essential for:
 * - White-label solutions
 * - Brand consistency
 * - Enterprise integrations
 *
 * Example usage:
 * ```php
 * // Add a new domain
 * $domain = $client->customDomains->create('links.example.com');
 *
 * // Get DNS instructions
 * $instructions = $client->customDomains->getDnsInstructions($domain->id);
 * // Configure DNS records according to instructions...
 *
 * // Verify the domain
 * $domain = $client->customDomains->verify($domain->id);
 *
 * // Once active, use with trees
 * $tree = $client->trees->update($treeId, [
 *     'custom_domain_id' => $domain->id,
 * ]);
 * ```
 */
class CustomDomains extends Resource
{
    /**
     * List all custom domains for the organization.
     *
     * @return CustomDomain[]
     */
    public function list(): array
    {
        $response = $this->http->get('/api/v1/custom-domains');

        return array_map(
            fn($data) => CustomDomain::fromResponse($data),
            $response['data'] ?? []
        );
    }

    /**
     * Get a specific custom domain by ID.
     *
     * @param int $id Domain ID
     */
    public function get(int $id): CustomDomain
    {
        $response = $this->http->get("/api/v1/custom-domains/{$id}");

        return CustomDomain::fromResponse($response['data']);
    }

    /**
     * Add a new custom domain.
     *
     * After adding, you must:
     * 1. Configure DNS records (use getDnsInstructions())
     * 2. Verify the domain (use verify())
     * 3. Wait for SSL provisioning (automatic)
     *
     * @param string $domain Domain name (must be subdomain, e.g., 'links.example.com')
     */
    public function create(string $domain): CustomDomain
    {
        $response = $this->http->post('/api/v1/custom-domains', [
            'domain' => $domain,
        ]);

        return CustomDomain::fromResponse($response['data']);
    }

    /**
     * Delete a custom domain.
     *
     * Warning: This will remove the domain and any trees using it
     * will fall back to the default domain.
     *
     * @param int $id Domain ID
     */
    public function delete(int $id): bool
    {
        $this->http->delete("/api/v1/custom-domains/{$id}");

        return true;
    }

    /**
     * Verify domain DNS configuration.
     *
     * Call this after configuring the required DNS records.
     * If successful, SSL provisioning will start automatically.
     *
     * @param int $id Domain ID
     * @return CustomDomain Updated domain with new status
     * @throws \Rlnks\Exceptions\ValidationException If verification fails
     */
    public function verify(int $id): CustomDomain
    {
        $response = $this->http->post("/api/v1/custom-domains/{$id}/verify");

        return CustomDomain::fromResponse($response['data']);
    }

    /**
     * Retry SSL provisioning.
     *
     * Use this if SSL provisioning failed or is stuck.
     *
     * @param int $id Domain ID
     */
    public function retrySsl(int $id): CustomDomain
    {
        $response = $this->http->post("/api/v1/custom-domains/{$id}/retry-ssl");

        return CustomDomain::fromResponse($response['data']);
    }

    /**
     * Set domain as the default for the organization.
     *
     * The default domain is used for all trees that don't have
     * a specific custom_domain_id set.
     *
     * @param int $id Domain ID (must be active)
     */
    public function setDefault(int $id): CustomDomain
    {
        $response = $this->http->post("/api/v1/custom-domains/{$id}/set-default");

        return CustomDomain::fromResponse($response['data']);
    }

    /**
     * Remove domain from defaults.
     *
     * @param int $id Domain ID
     */
    public function unsetDefault(int $id): CustomDomain
    {
        $response = $this->http->post("/api/v1/custom-domains/{$id}/unset-default");

        return CustomDomain::fromResponse($response['data']);
    }

    /**
     * Check if a domain is available (not already registered).
     *
     * @param string $domain Domain name to check
     * @return bool True if available, false if taken
     */
    public function checkAvailability(string $domain): bool
    {
        $response = $this->http->post('/api/v1/custom-domains/check-availability', [
            'domain' => $domain,
        ]);

        return $response['available'] ?? false;
    }

    /**
     * Get DNS setup instructions for a domain.
     *
     * Returns the required DNS records to configure:
     * - CNAME record pointing to our servers
     * - TXT record for domain ownership verification
     *
     * @param int $id Domain ID
     * @return array DNS instructions including:
     *               - cname_target: Target for CNAME record
     *               - txt_record_name: Name for TXT record
     *               - txt_record_value: Value for TXT record
     */
    public function getDnsInstructions(int $id): array
    {
        $response = $this->http->get("/api/v1/custom-domains/{$id}/dns-instructions");

        return $response['data'] ?? $response;
    }

    /**
     * Get the default domain for the organization.
     *
     * @return CustomDomain|null The default domain or null if none is set
     */
    public function getDefault(): ?CustomDomain
    {
        $domains = $this->list();

        foreach ($domains as $domain) {
            if ($domain->isDefault() && $domain->isActive()) {
                return $domain;
            }
        }

        return null;
    }

    /**
     * Get all active domains.
     *
     * @return CustomDomain[]
     */
    public function getActive(): array
    {
        return array_values(array_filter(
            $this->list(),
            fn($domain) => $domain->isActive()
        ));
    }

    /**
     * Get all pending domains (awaiting verification or SSL).
     *
     * @return CustomDomain[]
     */
    public function getPending(): array
    {
        return array_values(array_filter(
            $this->list(),
            fn($domain) => !$domain->isActive()
        ));
    }

    /**
     * Add a domain and wait for it to become active.
     *
     * This is a convenience method that:
     * 1. Creates the domain
     * 2. Returns DNS instructions with the domain
     *
     * You still need to:
     * - Configure DNS records manually
     * - Call verify() once DNS is configured
     *
     * @param string $domain Domain name
     * @return array{domain: CustomDomain, dns: array}
     */
    public function createWithInstructions(string $domain): array
    {
        $created = $this->create($domain);
        $instructions = $this->getDnsInstructions($created->id);

        return [
            'domain' => $created,
            'dns' => $instructions,
        ];
    }
}
