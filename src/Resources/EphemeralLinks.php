<?php

declare(strict_types=1);

namespace Rlnks\Resources;

use Rlnks\Models\EphemeralLink;

/**
 * Ephemeral Links resource for managing test links.
 *
 * Ephemeral links are temporary links for testing decision trees without
 * affecting production statistics. They are perfect for:
 * - Testing tree configurations before going live
 * - QA and validation workflows
 * - Previewing changes during development
 *
 * Features:
 * - Time-based expiration (5-60 minutes)
 * - Optional max use limits
 * - Variable slug support for testing conditions
 *
 * Example usage:
 * ```php
 * // Create a test link
 * $link = $client->ephemeralLinks->create($treeId, [
 *     'type' => 'image',
 *     'expires_in_minutes' => 15,
 * ]);
 *
 * // Test with variable slug
 * echo $link->getUrlWithSlug('logo'); // /test/i/{uuid}/logo
 * echo $link->getUrlWithSlug('banner'); // /test/i/{uuid}/banner
 *
 * // List active links
 * $links = $client->ephemeralLinks->list($treeId);
 * ```
 */
class EphemeralLinks extends Resource
{
    /**
     * List ephemeral links for a tree.
     *
     * @param string $treeId Tree UUID
     * @param array $options Query options:
     *                       - include_expired: Include expired links (default: false)
     *
     * @return EphemeralLink[]
     */
    public function list(string $treeId, array $options = []): array
    {
        $query = $this->buildQuery([
            'include_expired' => $options['include_expired'] ?? null,
        ]);

        $response = $this->http->get("/api/v1/trees/{$treeId}/ephemeral-links", $query);

        return array_map(
            fn($data) => EphemeralLink::fromResponse($data),
            $response['data'] ?? []
        );
    }

    /**
     * Create a new ephemeral link for a tree.
     *
     * @param string $treeId Tree UUID
     * @param array $options Link options:
     *                       - type: (required) 'image' or 'redirect'
     *                       - expires_in_minutes: Expiration time (5-60, default: 15)
     *                       - max_uses: Maximum uses (1-1000, default: unlimited)
     */
    public function create(string $treeId, array $options): EphemeralLink
    {
        $response = $this->http->post("/api/v1/trees/{$treeId}/ephemeral-links", $options);

        return EphemeralLink::fromResponse($response['data']);
    }

    /**
     * Create an image test link.
     *
     * Convenience method for creating image-type test links.
     *
     * @param string $treeId Tree UUID
     * @param int $expiresInMinutes Expiration time (default: 15)
     * @param int|null $maxUses Maximum uses (default: unlimited)
     */
    public function createImageLink(string $treeId, int $expiresInMinutes = 15, ?int $maxUses = null): EphemeralLink
    {
        return $this->create($treeId, [
            'type' => 'image',
            'expires_in_minutes' => $expiresInMinutes,
            'max_uses' => $maxUses,
        ]);
    }

    /**
     * Create a redirect test link.
     *
     * Convenience method for creating redirect-type test links.
     *
     * @param string $treeId Tree UUID
     * @param int $expiresInMinutes Expiration time (default: 15)
     * @param int|null $maxUses Maximum uses (default: unlimited)
     */
    public function createRedirectLink(string $treeId, int $expiresInMinutes = 15, ?int $maxUses = null): EphemeralLink
    {
        return $this->create($treeId, [
            'type' => 'redirect',
            'expires_in_minutes' => $expiresInMinutes,
            'max_uses' => $maxUses,
        ]);
    }

    /**
     * Get a specific ephemeral link by UUID.
     *
     * @param string $uuid Link UUID
     */
    public function get(string $uuid): EphemeralLink
    {
        $response = $this->http->get("/api/v1/ephemeral-links/{$uuid}");

        return EphemeralLink::fromResponse($response['data']);
    }

    /**
     * Delete an ephemeral link.
     *
     * @param string $uuid Link UUID
     */
    public function delete(string $uuid): bool
    {
        $this->http->delete("/api/v1/ephemeral-links/{$uuid}");

        return true;
    }

    /**
     * Delete all expired links for a tree.
     *
     * Useful for cleaning up old test links.
     *
     * @param string $treeId Tree UUID
     * @return int Number of deleted links
     */
    public function deleteExpired(string $treeId): int
    {
        $response = $this->http->delete("/api/v1/trees/{$treeId}/ephemeral-links/expired");

        return $response['deleted_count'] ?? 0;
    }

    /**
     * Get only valid (non-expired) links for a tree.
     *
     * @param string $treeId Tree UUID
     * @return EphemeralLink[]
     */
    public function getValid(string $treeId): array
    {
        return array_values(array_filter(
            $this->list($treeId, ['include_expired' => true]),
            fn($link) => $link->isValid()
        ));
    }

    /**
     * Create a test link and return the URL directly.
     *
     * Convenience method for quick URL generation.
     *
     * @param string $treeId Tree UUID
     * @param string $type 'image' or 'redirect'
     * @param int $expiresInMinutes Expiration time
     * @return string The test URL
     */
    public function createAndGetUrl(string $treeId, string $type, int $expiresInMinutes = 15): string
    {
        $link = $this->create($treeId, [
            'type' => $type,
            'expires_in_minutes' => $expiresInMinutes,
        ]);

        return $link->getUrl();
    }
}
