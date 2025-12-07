<?php

declare(strict_types=1);

namespace Rlnks\Models;

/**
 * Ephemeral Link model.
 *
 * Ephemeral links are temporary test links for testing decision trees
 * without affecting production statistics.
 *
 * @property string $uuid Unique identifier
 * @property string $tree_id Tree UUID this link belongs to
 * @property string $type Link type ('image' or 'redirect')
 * @property string $url The test URL
 * @property string $expires_at Expiration timestamp (ISO 8601)
 * @property int|null $max_uses Maximum allowed uses (null = unlimited)
 * @property int $use_count Current use count
 * @property int|null $remaining_uses Remaining uses before expiration
 * @property string $remaining_time Human-readable remaining time
 * @property bool $is_valid Whether the link is still valid
 * @property bool $is_expired Whether the link has expired
 * @property string|null $created_by Name of user who created the link
 * @property string $created_at Creation timestamp (ISO 8601)
 */
class EphemeralLink extends Model
{
    /**
     * Check if the link is still valid (not expired and within use limits).
     */
    public function isValid(): bool
    {
        return (bool) $this->is_valid;
    }

    /**
     * Check if the link has expired.
     */
    public function isExpired(): bool
    {
        return (bool) $this->is_expired;
    }

    /**
     * Check if this is an image type link.
     */
    public function isImageLink(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if this is a redirect type link.
     */
    public function isRedirectLink(): bool
    {
        return $this->type === 'redirect';
    }

    /**
     * Get the full test URL.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get test URL with a variable slug appended.
     *
     * Variable slugs can be used in tree conditions to test different paths.
     * Example: /test/i/{uuid}/logo will set context['slug'] = 'logo'
     *
     * @param string $slug The variable slug to append
     */
    public function getUrlWithSlug(string $slug): string
    {
        return rtrim($this->url, '/') . '/' . $slug;
    }

    /**
     * Check if the link has limited uses.
     */
    public function hasUseLimit(): bool
    {
        return $this->max_uses !== null;
    }

    /**
     * Get the remaining uses (null if unlimited).
     */
    public function getRemainingUses(): ?int
    {
        return $this->remaining_uses;
    }

    /**
     * Get the human-readable remaining time.
     */
    public function getRemainingTime(): string
    {
        return $this->remaining_time ?? 'Expired';
    }

    /**
     * Get expiration as DateTimeImmutable.
     */
    public function getExpiresAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->expires_at);
    }

    /**
     * Get creation timestamp as DateTimeImmutable.
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->created_at);
    }
}
