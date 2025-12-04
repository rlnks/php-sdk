<?php

declare(strict_types=1);

namespace Rlnks\Resources;

use Rlnks\Models\PaginatedResponse;

/**
 * Device Reports resource for reporting device detection issues.
 *
 * If you notice that a device is being incorrectly detected, you can
 * submit a report to help improve detection accuracy.
 */
class DeviceReports extends Resource
{
    /**
     * List all submitted device reports.
     *
     * @return PaginatedResponse
     */
    public function list(array $options = []): array
    {
        $query = $this->buildQuery([
            'per_page' => $options['per_page'] ?? null,
            'page' => $options['page'] ?? null,
        ]);

        return $this->http->get('/api/v1/device-reports', $query);
    }

    /**
     * Get a single device report by ID.
     */
    public function get(string $id): array
    {
        $response = $this->http->get("/api/v1/device-reports/{$id}");

        return $response['data'];
    }

    /**
     * Submit a new device detection issue report.
     *
     * @param array $data Report data:
     *                    - user_agent: (required) The User-Agent string
     *                    - issue_type: Issue type (e.g., 'wrong_model', 'wrong_brand')
     *                    - expected_brand: Expected device brand
     *                    - expected_model: Expected device model
     *                    - expected_dimensions: Expected screen dimensions
     *                    - notes: Additional notes about the issue
     *
     * @return array Created report data
     */
    public function submit(array $data): array
    {
        $response = $this->http->post('/api/v1/device-reports', $data);

        return $response['data'];
    }

    /**
     * Report a device detection issue with helper method.
     *
     * @param string $userAgent The User-Agent string that was incorrectly detected
     * @param string $expectedBrand Expected device brand (e.g., 'Apple')
     * @param string $expectedModel Expected device model (e.g., 'iPhone 15 Pro')
     * @param string|null $notes Additional notes
     */
    public function reportWrongDetection(
        string $userAgent,
        string $expectedBrand,
        string $expectedModel,
        ?string $notes = null
    ): array {
        return $this->submit([
            'user_agent' => $userAgent,
            'issue_type' => 'wrong_model',
            'expected_brand' => $expectedBrand,
            'expected_model' => $expectedModel,
            'notes' => $notes,
        ]);
    }
}
