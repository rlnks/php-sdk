<?php

declare(strict_types=1);

namespace Rlnks\Models;

use DateTimeImmutable;

/**
 * Webhook model.
 *
 * @property string $id UUID identifier
 * @property string $name Webhook name
 * @property string $url Target URL
 * @property array $events Subscribed events
 * @property array|null $headers Custom headers
 * @property bool $is_active Whether webhook is active
 * @property int $timeout_seconds Timeout in seconds
 * @property int $retry_count Number of retry attempts
 * @property array $stats Delivery statistics (total, successful, failed, success_rate)
 * @property string|null $last_triggered_at Last trigger timestamp
 * @property string|null $last_success_at Last successful delivery timestamp
 * @property string|null $last_failure_at Last failed delivery timestamp
 * @property string $created_at Creation timestamp
 *
 * Note: The webhook secret is only returned when calling regenerateSecret().
 * Use Webhooks::regenerateSecret() to get a new secret.
 */
class Webhook extends Model
{
    /**
     * Available webhook events.
     */
    public const EVENTS = [
        'tree.created',
        'tree.updated',
        'tree.deleted',
        'tree.activated',
        'tree.deactivated',
        'request.received',
        'usage.limit_warning',
        'usage.limit_reached',
    ];

    /**
     * Check if webhook is subscribed to an event.
     */
    public function isSubscribedTo(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }

    /**
     * Get success rate as percentage.
     */
    public function getSuccessRate(): float
    {
        // Stats are now returned as a nested object
        $stats = $this->stats ?? [];
        if (isset($stats['success_rate'])) {
            return (float) $stats['success_rate'];
        }

        $total = $stats['total_deliveries'] ?? 0;
        if ($total === 0) {
            return 100.0;
        }
        return round(($stats['successful_deliveries'] ?? 0) / $total * 100, 2);
    }

    /**
     * Get last triggered date as DateTimeImmutable.
     */
    public function getLastTriggeredAt(): ?DateTimeImmutable
    {
        return $this->last_triggered_at ? new DateTimeImmutable($this->last_triggered_at) : null;
    }

    /**
     * Get creation date as DateTimeImmutable.
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at ? new DateTimeImmutable($this->created_at) : null;
    }
}
