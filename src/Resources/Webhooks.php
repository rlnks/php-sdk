<?php

declare(strict_types=1);

namespace Rlnks\Resources;

use Rlnks\Models\PaginatedResponse;
use Rlnks\Models\Webhook;

/**
 * Webhooks resource for managing webhook subscriptions.
 *
 * Webhooks allow you to receive real-time notifications when events
 * occur in your RLNKS account, such as tree changes or usage alerts.
 */
class Webhooks extends Resource
{
    /**
     * List all webhooks.
     *
     * @return PaginatedResponse<Webhook>
     */
    public function list(array $options = []): PaginatedResponse
    {
        $query = $this->buildQuery([
            'per_page' => $options['per_page'] ?? null,
            'page' => $options['page'] ?? null,
        ]);

        $response = $this->http->get('/api/v1/webhooks', $query);

        return PaginatedResponse::fromResponse($response, Webhook::class);
    }

    /**
     * Get a single webhook by ID.
     */
    public function get(string $id): Webhook
    {
        $response = $this->http->get("/api/v1/webhooks/{$id}");

        return Webhook::fromResponse($response['data']);
    }

    /**
     * Create a new webhook.
     *
     * @param array $data Webhook data:
     *                    - name: (required) Webhook name
     *                    - url: (required) Target URL
     *                    - events: (required) Array of events to subscribe to
     *                    - headers: Custom headers to send
     *                    - is_active: Whether webhook is active (default: true)
     *                    - timeout_seconds: Timeout in seconds (5-60, default: 30)
     *                    - retry_count: Number of retries (0-5, default: 3)
     */
    public function create(array $data): Webhook
    {
        $response = $this->http->post('/api/v1/webhooks', $data);

        return Webhook::fromResponse($response['data']);
    }

    /**
     * Update an existing webhook.
     *
     * @param string $id Webhook ID
     * @param array $data Fields to update
     */
    public function update(string $id, array $data): Webhook
    {
        $response = $this->http->put("/api/v1/webhooks/{$id}", $data);

        return Webhook::fromResponse($response['data']);
    }

    /**
     * Delete a webhook.
     */
    public function delete(string $id): bool
    {
        $this->http->delete("/api/v1/webhooks/{$id}");

        return true;
    }

    /**
     * Regenerate webhook secret.
     *
     * Returns the new secret (only shown once).
     */
    public function regenerateSecret(string $id): array
    {
        return $this->http->post("/api/v1/webhooks/{$id}/regenerate-secret");
    }

    /**
     * Send a test webhook delivery.
     *
     * @return array Test delivery result
     */
    public function test(string $id): array
    {
        return $this->http->post("/api/v1/webhooks/{$id}/test");
    }

    /**
     * Get recent webhook deliveries.
     *
     * @param string $id Webhook ID
     * @return array Recent deliveries (up to 50)
     */
    public function getDeliveries(string $id): array
    {
        return $this->http->get("/api/v1/webhooks/{$id}/deliveries");
    }

    /**
     * Enable a webhook.
     */
    public function enable(string $id): Webhook
    {
        return $this->update($id, ['is_active' => true]);
    }

    /**
     * Disable a webhook.
     */
    public function disable(string $id): Webhook
    {
        return $this->update($id, ['is_active' => false]);
    }

    /**
     * Subscribe webhook to additional events.
     */
    public function addEvents(string $id, array $events): Webhook
    {
        $webhook = $this->get($id);
        $currentEvents = $webhook->events ?? [];
        $newEvents = array_unique(array_merge($currentEvents, $events));

        return $this->update($id, ['events' => array_values($newEvents)]);
    }

    /**
     * Unsubscribe webhook from events.
     */
    public function removeEvents(string $id, array $events): Webhook
    {
        $webhook = $this->get($id);
        $currentEvents = $webhook->events ?? [];
        $newEvents = array_diff($currentEvents, $events);

        return $this->update($id, ['events' => array_values($newEvents)]);
    }
}
