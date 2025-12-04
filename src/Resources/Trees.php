<?php

declare(strict_types=1);

namespace Rlnks\Resources;

use Rlnks\Models\PaginatedResponse;
use Rlnks\Models\Tree;

/**
 * Trees resource for managing decision trees.
 *
 * Decision trees are the core of RLNKS - they define conditional logic
 * for serving different images or redirecting to different URLs based
 * on device characteristics, location, time, and custom parameters.
 */
class Trees extends Resource
{
    /**
     * List all decision trees.
     *
     * @param array $options Query options:
     *                       - type: 'image' or 'redirect'
     *                       - status: 'active', 'inactive', 'archived'
     *                       - search: Search by name
     *                       - sort_by: Field to sort (default: 'created_at')
     *                       - sort_dir: 'asc' or 'desc' (default: 'desc')
     *                       - per_page: Items per page (default: 20)
     *                       - page: Page number
     *
     * @return PaginatedResponse<Tree>
     */
    public function list(array $options = []): PaginatedResponse
    {
        $query = $this->buildQuery([
            'type' => $options['type'] ?? null,
            'status' => $options['status'] ?? null,
            'search' => $options['search'] ?? null,
            'sort_by' => $options['sort_by'] ?? null,
            'sort_dir' => $options['sort_dir'] ?? null,
            'per_page' => $options['per_page'] ?? null,
            'page' => $options['page'] ?? null,
        ]);

        $response = $this->http->get('/api/v1/trees', $query);

        return PaginatedResponse::fromResponse($response, Tree::class);
    }

    /**
     * Get a single tree by ID.
     */
    public function get(string $id): Tree
    {
        $response = $this->http->get("/api/v1/trees/{$id}");

        return Tree::fromResponse($response['data']);
    }

    /**
     * Create a new decision tree.
     *
     * @param array $data Tree data:
     *                    - name: (required) Tree name
     *                    - type: (required) 'image' or 'redirect'
     *                    - description: Tree description
     *                    - tree_data: Decision tree structure
     *                    - default_output: Fallback output configuration
     *                    - is_active: Whether tree is active (default: true)
     */
    public function create(array $data): Tree
    {
        $response = $this->http->post('/api/v1/trees', $data);

        return Tree::fromResponse($response['data']);
    }

    /**
     * Update an existing tree.
     *
     * @param string $id Tree ID
     * @param array $data Fields to update
     */
    public function update(string $id, array $data): Tree
    {
        $response = $this->http->put("/api/v1/trees/{$id}", $data);

        return Tree::fromResponse($response['data']);
    }

    /**
     * Delete a tree.
     */
    public function delete(string $id): bool
    {
        $this->http->delete("/api/v1/trees/{$id}");

        return true;
    }

    /**
     * Clone an existing tree.
     *
     * @param string $id Source tree ID
     * @param array $options Clone options:
     *                       - name: Name for the cloned tree
     */
    public function clone(string $id, array $options = []): Tree
    {
        $response = $this->http->post("/api/v1/trees/{$id}/clone", $options);

        return Tree::fromResponse($response['data']);
    }

    /**
     * Test a tree with simulated context.
     *
     * @param string $id Tree ID
     * @param array $context Test context (device, location, custom params)
     * @return array Test results including matched node and output
     */
    public function test(string $id, array $context = []): array
    {
        $response = $this->http->post("/api/v1/trees/{$id}/test", [
            'context' => $context,
        ]);

        return $response['result'] ?? $response;
    }

    /**
     * Preview tree evaluation with current request context.
     *
     * @param string $id Tree ID
     * @return array Preview results
     */
    public function preview(string $id): array
    {
        return $this->http->get("/api/v1/trees/{$id}/preview");
    }

    /**
     * Activate a tree.
     */
    public function activate(string $id): Tree
    {
        return $this->update($id, ['is_active' => true]);
    }

    /**
     * Deactivate a tree.
     */
    public function deactivate(string $id): Tree
    {
        return $this->update($id, ['is_active' => false]);
    }

    /**
     * Archive a tree.
     */
    public function archive(string $id): Tree
    {
        return $this->update($id, ['is_archived' => true]);
    }

    /**
     * Unarchive a tree.
     */
    public function unarchive(string $id): Tree
    {
        return $this->update($id, ['is_archived' => false]);
    }
}
