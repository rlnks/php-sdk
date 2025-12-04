<?php

declare(strict_types=1);

namespace Rlnks\Models;

use DateTimeImmutable;

/**
 * Decision Tree model.
 *
 * @property string $id UUID identifier
 * @property string $name Tree name
 * @property string $type Tree type ('image' or 'redirect')
 * @property string|null $description Tree description
 * @property string $short_code Short code for URLs
 * @property string|null $url_slug Custom URL slug
 * @property string|null $url_extension Custom URL extension (e.g., 'jpg', 'png')
 * @property bool $is_active Whether tree is active
 * @property bool $is_archived Whether tree is archived
 * @property array $tree_data Decision tree structure (only in detailed view)
 * @property array|null $default_output Default/fallback output (only in detailed view)
 * @property array|null $settings Tree settings (only in detailed view)
 * @property int $total_requests Total request count
 * @property string|null $last_request_at Last request timestamp
 * @property array $endpoints Generated endpoint URLs
 * @property string $created_at Creation timestamp
 * @property string $updated_at Last update timestamp
 */
class Tree extends Model
{
    /**
     * Check if tree is an image tree.
     */
    public function isImageTree(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if tree is a redirect tree.
     */
    public function isRedirectTree(): bool
    {
        return $this->type === 'redirect';
    }

    /**
     * Get the image serve URL.
     */
    public function getImageUrl(): ?string
    {
        return $this->endpoints['image'] ?? null;
    }

    /**
     * Get the redirect URL.
     */
    public function getRedirectUrl(): ?string
    {
        return $this->endpoints['redirect'] ?? null;
    }

    /**
     * Get the short URL.
     */
    public function getShortUrl(): ?string
    {
        return $this->endpoints['short'] ?? null;
    }

    /**
     * Get creation date as DateTimeImmutable.
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at ? new DateTimeImmutable($this->created_at) : null;
    }

    /**
     * Get update date as DateTimeImmutable.
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updated_at ? new DateTimeImmutable($this->updated_at) : null;
    }

    /**
     * Get last request date as DateTimeImmutable.
     */
    public function getLastRequestAt(): ?DateTimeImmutable
    {
        return $this->last_request_at ? new DateTimeImmutable($this->last_request_at) : null;
    }
}
