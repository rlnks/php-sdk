<?php

declare(strict_types=1);

namespace Rlnks\Resources;

/**
 * Account resource for managing account information and API keys.
 */
class Account extends Resource
{
    /**
     * Get current account information.
     *
     * @return array Account data including:
     *               - id: User ID
     *               - email: Email address
     *               - name: Full name
     *               - company: Company name
     *               - timezone: User timezone
     *               - locale: Language preference
     *               - plan: Current plan details
     *               - usage: Current usage statistics
     *               - created_at: Account creation date
     *
     * Note: API key is not included for security. Use getApiKey() instead.
     */
    public function get(): array
    {
        $response = $this->http->get('/api/v1/account');

        return $response['data'];
    }

    /**
     * Update account settings.
     *
     * @param array $data Fields to update:
     *                    - name: Full name
     *                    - company: Company name
     *                    - timezone: Timezone (e.g., 'America/Toronto')
     *                    - locale: Language (e.g., 'en', 'fr')
     *
     * @return array Updated account data
     */
    public function update(array $data): array
    {
        $response = $this->http->put('/api/v1/account', $data);

        return $response['data'];
    }

    /**
     * Get detailed usage statistics.
     *
     * @return array Usage data including:
     *               - current_month: Current month stats
     *               - daily: Daily breakdown
     *               - history: Previous months
     */
    public function getUsage(): array
    {
        return $this->http->get('/api/v1/account/usage');
    }

    /**
     * Get plan limits and current usage summary.
     *
     * Returns a comprehensive view of all resource limits and current usage:
     * - requests: Monthly API request limits and usage
     * - trees: Decision tree limits and count
     * - images: Image limits and count
     * - storage: Storage usage and max file size
     *
     * @return array Limits data including:
     *               - plan: Plan name and slug
     *               - requests: {used, limit, remaining, percentage, unlimited, period}
     *               - trees: {used, limit, remaining, percentage, unlimited}
     *               - images: {used, limit, remaining, percentage, unlimited}
     *               - storage: {used_bytes, used_human, max_file_size_mb}
     */
    public function getLimits(): array
    {
        $response = $this->http->get('/api/v1/account/limits');

        return $response['data'];
    }

    /**
     * Get remaining trees that can be created.
     *
     * @return int|null Remaining count, or null if unlimited
     */
    public function getRemainingTrees(): ?int
    {
        $limits = $this->getLimits();

        return $limits['trees']['remaining'] ?? null;
    }

    /**
     * Get remaining images that can be uploaded.
     *
     * @return int|null Remaining count, or null if unlimited
     */
    public function getRemainingImages(): ?int
    {
        $limits = $this->getLimits();

        return $limits['images']['remaining'] ?? null;
    }

    /**
     * Get total storage used by images in bytes.
     */
    public function getStorageUsed(): int
    {
        $limits = $this->getLimits();

        return $limits['storage']['used_bytes'] ?? 0;
    }

    /**
     * Get total storage used as human-readable string.
     */
    public function getStorageUsedHuman(): string
    {
        $limits = $this->getLimits();

        return $limits['storage']['used_human'] ?? '0 B';
    }

    /**
     * Check if trees limit has been reached.
     */
    public function hasReachedTreesLimit(): bool
    {
        $limits = $this->getLimits();

        if ($limits['trees']['unlimited'] ?? false) {
            return false;
        }

        return ($limits['trees']['remaining'] ?? 0) <= 0;
    }

    /**
     * Check if images limit has been reached.
     */
    public function hasReachedImagesLimit(): bool
    {
        $limits = $this->getLimits();

        if ($limits['images']['unlimited'] ?? false) {
            return false;
        }

        return ($limits['images']['remaining'] ?? 0) <= 0;
    }

    /**
     * Get API key information (masked for security).
     *
     * Returns the API key with only first 10 and last 4 characters visible.
     * To get the full key, use regenerateApiKey().
     *
     * @return array{api_key_masked: string, last_used: ?string}
     */
    public function getApiKey(): array
    {
        return $this->http->get('/api/v1/account/api-key');
    }

    /**
     * Regenerate API key.
     *
     * WARNING: This will invalidate your current API key.
     * You will need to update this SDK client with the new key.
     *
     * @return array New API key data
     */
    public function regenerateApiKey(): array
    {
        return $this->http->post('/api/v1/account/api-key/regenerate');
    }

    /**
     * Get current plan details.
     */
    public function getPlan(): array
    {
        $account = $this->get();

        return $account['plan'] ?? [];
    }

    /**
     * Get current month usage summary.
     */
    public function getCurrentUsage(): array
    {
        $usage = $this->getUsage();

        return $usage['current_month'] ?? [];
    }

    /**
     * Check if account is over the usage limit.
     */
    public function isOverLimit(): bool
    {
        $usage = $this->getCurrentUsage();

        return $usage['is_over_limit'] ?? false;
    }

    /**
     * Get remaining requests for current month.
     */
    public function getRemainingRequests(): int
    {
        $usage = $this->getCurrentUsage();

        return $usage['remaining'] ?? 0;
    }

    /**
     * Get usage percentage for current month.
     */
    public function getUsagePercentage(): float
    {
        $usage = $this->getCurrentUsage();

        return $usage['percentage'] ?? 0.0;
    }
}
