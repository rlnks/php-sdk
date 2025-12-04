<?php

declare(strict_types=1);

namespace Rlnks\Resources;

use Rlnks\Models\TreeVariable;

/**
 * Tree Variables resource for managing dynamic variables in decision trees.
 *
 * Variables allow you to change decision tree behavior without modifying
 * the tree structure. They can be updated via API or webhook, making them
 * ideal for A/B testing, feature flags, and dynamic configuration.
 *
 * Example usage:
 * ```php
 * // List all variables for a tree
 * $variables = $client->treeVariables->list($treeId);
 *
 * // Create a new variable
 * $variable = $client->treeVariables->create($treeId, [
 *     'key' => 'show_promo',
 *     'value' => 'true',
 *     'type' => 'boolean',
 *     'description' => 'Show promotional banner',
 * ]);
 *
 * // Update variable value
 * $client->treeVariables->update($treeId, 'show_promo', ['value' => 'false']);
 * ```
 */
class TreeVariables extends Resource
{
    /**
     * List all variables for a tree.
     *
     * @param string $treeId Tree ID (UUID or public_id)
     * @return TreeVariable[]
     */
    public function list(string $treeId): array
    {
        $response = $this->http->get("/api/v1/trees/{$treeId}/variables");

        return array_map(
            fn($data) => TreeVariable::fromResponse($data),
            $response['data'] ?? []
        );
    }

    /**
     * Get a specific variable by key.
     *
     * @param string $treeId Tree ID
     * @param string $key Variable key
     */
    public function get(string $treeId, string $key): TreeVariable
    {
        $response = $this->http->get("/api/v1/trees/{$treeId}/variables/{$key}");

        return TreeVariable::fromResponse($response['data']);
    }

    /**
     * Create a new variable.
     *
     * @param string $treeId Tree ID
     * @param array $data Variable data:
     *                    - key: (required) Variable key (alphanumeric + underscore)
     *                    - value: Variable value
     *                    - type: Variable type ('string', 'number', 'boolean')
     *                    - description: Variable description
     */
    public function create(string $treeId, array $data): TreeVariable
    {
        $response = $this->http->post("/api/v1/trees/{$treeId}/variables", $data);

        return TreeVariable::fromResponse($response['data']);
    }

    /**
     * Update a variable.
     *
     * @param string $treeId Tree ID
     * @param string $key Variable key
     * @param array $data Fields to update (value, type, description)
     */
    public function update(string $treeId, string $key, array $data): TreeVariable
    {
        $response = $this->http->put("/api/v1/trees/{$treeId}/variables/{$key}", $data);

        return TreeVariable::fromResponse($response['data']);
    }

    /**
     * Delete a variable.
     *
     * @param string $treeId Tree ID
     * @param string $key Variable key
     */
    public function delete(string $treeId, string $key): bool
    {
        $this->http->delete("/api/v1/trees/{$treeId}/variables/{$key}");

        return true;
    }

    /**
     * Bulk update multiple variables.
     *
     * @param string $treeId Tree ID
     * @param array $variables Array of ['key' => 'value'] pairs
     * @return array Updated keys
     */
    public function bulkUpdate(string $treeId, array $variables): array
    {
        $data = [];
        foreach ($variables as $key => $value) {
            $data[] = ['key' => $key, 'value' => $value];
        }

        $response = $this->http->put("/api/v1/trees/{$treeId}/variables", [
            'variables' => $data,
        ]);

        return $response['updated'] ?? [];
    }

    /**
     * Regenerate webhook token for a variable.
     *
     * This invalidates the current webhook URL and generates a new one.
     *
     * @param string $treeId Tree ID
     * @param string $key Variable key
     */
    public function regenerateWebhook(string $treeId, string $key): TreeVariable
    {
        $response = $this->http->post("/api/v1/trees/{$treeId}/variables/{$key}/regenerate-webhook");

        return TreeVariable::fromResponse($response['data']);
    }

    /**
     * Set a variable value (convenience method).
     *
     * @param string $treeId Tree ID
     * @param string $key Variable key
     * @param string|int|float|bool $value New value
     */
    public function setValue(string $treeId, string $key, mixed $value): TreeVariable
    {
        return $this->update($treeId, $key, [
            'value' => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value,
        ]);
    }

    /**
     * Get a variable's value directly.
     *
     * @param string $treeId Tree ID
     * @param string $key Variable key
     * @return mixed The typed value
     */
    public function getValue(string $treeId, string $key): mixed
    {
        $variable = $this->get($treeId, $key);

        return $variable->getValue();
    }
}
