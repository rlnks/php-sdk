<?php

declare(strict_types=1);

namespace Rlnks\Webhook;

use DateTimeImmutable;

/**
 * Represents a webhook event payload.
 *
 * Example usage:
 * ```php
 * $event = WebhookEvent::fromPayload($parsedPayload);
 *
 * if ($event->isTreeEvent()) {
 *     echo "Tree {$event->getTreeId()} was {$event->getEventType()}";
 * }
 * ```
 */
class WebhookEvent
{
    /**
     * Event types.
     */
    public const TREE_CREATED = 'tree.created';
    public const TREE_UPDATED = 'tree.updated';
    public const TREE_DELETED = 'tree.deleted';
    public const TREE_ACTIVATED = 'tree.activated';
    public const TREE_DEACTIVATED = 'tree.deactivated';
    public const REQUEST_RECEIVED = 'request.received';
    public const USAGE_LIMIT_WARNING = 'usage.limit_warning';
    public const USAGE_LIMIT_REACHED = 'usage.limit_reached';

    protected string $event;
    protected array $data;
    protected DateTimeImmutable $timestamp;

    public function __construct(string $event, array $data, DateTimeImmutable $timestamp)
    {
        $this->event = $event;
        $this->data = $data;
        $this->timestamp = $timestamp;
    }

    /**
     * Create from parsed webhook payload.
     */
    public static function fromPayload(array $payload): self
    {
        $timestamp = isset($payload['timestamp'])
            ? new DateTimeImmutable($payload['timestamp'])
            : new DateTimeImmutable();

        return new self(
            $payload['event'] ?? 'unknown',
            $payload['data'] ?? [],
            $timestamp
        );
    }

    /**
     * Get the event type.
     */
    public function getEventType(): string
    {
        return $this->event;
    }

    /**
     * Get the event data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get a specific data field.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Get the event timestamp.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Check if this is a tree-related event.
     */
    public function isTreeEvent(): bool
    {
        return str_starts_with($this->event, 'tree.');
    }

    /**
     * Check if this is a usage-related event.
     */
    public function isUsageEvent(): bool
    {
        return str_starts_with($this->event, 'usage.');
    }

    /**
     * Check if this is a request event.
     */
    public function isRequestEvent(): bool
    {
        return $this->event === self::REQUEST_RECEIVED;
    }

    /**
     * Get tree ID from event data (for tree events).
     */
    public function getTreeId(): ?string
    {
        return $this->data['tree_id'] ?? null;
    }

    /**
     * Get tree name from event data (for tree events).
     */
    public function getTreeName(): ?string
    {
        return $this->data['tree_name'] ?? null;
    }

    /**
     * Check if event matches a specific type.
     */
    public function is(string $eventType): bool
    {
        return $this->event === $eventType;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'event' => $this->event,
            'data' => $this->data,
            'timestamp' => $this->timestamp->format('c'),
        ];
    }
}
