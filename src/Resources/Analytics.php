<?php

declare(strict_types=1);

namespace Rlnks\Resources;

/**
 * Analytics resource for retrieving tree performance data.
 *
 * Provides access to views, clicks, device breakdowns, geographic
 * distribution, and other analytics for decision trees.
 */
class Analytics extends Resource
{
    /**
     * Available breakdown dimensions.
     */
    public const BREAKDOWN_DEVICE = 'device';
    public const BREAKDOWN_BROWSER = 'browser';
    public const BREAKDOWN_COUNTRY = 'country';
    public const BREAKDOWN_OUTPUT = 'output';
    public const BREAKDOWN_OS = 'os';
    public const BREAKDOWN_BRAND = 'brand';
    public const BREAKDOWN_MODEL = 'model';

    /**
     * Available period presets.
     */
    public const PERIOD_TODAY = 'today';
    public const PERIOD_7_DAYS = '7d';
    public const PERIOD_30_DAYS = '30d';
    public const PERIOD_90_DAYS = '90d';
    public const PERIOD_CUSTOM = 'custom';

    /**
     * Get analytics summary for a tree.
     *
     * @param string $treeId Tree ID
     * @param array $options Query options:
     *                       - period: 'today', '7d', '30d', '90d', 'custom'
     *                       - start_date: Start date for custom period (YYYY-MM-DD)
     *                       - end_date: End date for custom period (YYYY-MM-DD)
     *
     * @return array Analytics data including:
     *               - summary: Totals and changes
     *               - timeline: Daily breakdown
     *               - devices: Device type distribution
     *               - browsers: Browser distribution
     *               - countries: Country distribution
     *               - outputs: Output node distribution
     */
    public function getTreeAnalytics(string $treeId, array $options = []): array
    {
        $query = $this->buildQuery([
            'period' => $options['period'] ?? null,
            'start_date' => $options['start_date'] ?? null,
            'end_date' => $options['end_date'] ?? null,
        ]);

        return $this->http->get("/api/v1/analytics/trees/{$treeId}", $query);
    }

    /**
     * Get detailed breakdown by a specific dimension.
     *
     * @param string $treeId Tree ID
     * @param string $dimension Breakdown dimension (device, browser, country, etc.)
     * @param array $options Query options (period, start_date, end_date)
     *
     * @return array Breakdown data
     */
    public function getBreakdown(string $treeId, string $dimension, array $options = []): array
    {
        $query = $this->buildQuery([
            'by' => $dimension,
            'period' => $options['period'] ?? null,
            'start_date' => $options['start_date'] ?? null,
            'end_date' => $options['end_date'] ?? null,
        ]);

        return $this->http->get("/api/v1/analytics/trees/{$treeId}/breakdown", $query);
    }

    /**
     * Get device type breakdown.
     */
    public function getDeviceBreakdown(string $treeId, array $options = []): array
    {
        return $this->getBreakdown($treeId, self::BREAKDOWN_DEVICE, $options);
    }

    /**
     * Get browser breakdown.
     */
    public function getBrowserBreakdown(string $treeId, array $options = []): array
    {
        return $this->getBreakdown($treeId, self::BREAKDOWN_BROWSER, $options);
    }

    /**
     * Get country breakdown.
     */
    public function getCountryBreakdown(string $treeId, array $options = []): array
    {
        return $this->getBreakdown($treeId, self::BREAKDOWN_COUNTRY, $options);
    }

    /**
     * Get output node breakdown (A/B test results).
     */
    public function getOutputBreakdown(string $treeId, array $options = []): array
    {
        return $this->getBreakdown($treeId, self::BREAKDOWN_OUTPUT, $options);
    }

    /**
     * Get operating system breakdown.
     */
    public function getOsBreakdown(string $treeId, array $options = []): array
    {
        return $this->getBreakdown($treeId, self::BREAKDOWN_OS, $options);
    }

    /**
     * Get device brand breakdown.
     */
    public function getBrandBreakdown(string $treeId, array $options = []): array
    {
        return $this->getBreakdown($treeId, self::BREAKDOWN_BRAND, $options);
    }

    /**
     * Get device model breakdown.
     */
    public function getModelBreakdown(string $treeId, array $options = []): array
    {
        return $this->getBreakdown($treeId, self::BREAKDOWN_MODEL, $options);
    }

    /**
     * Get analytics for the last 7 days (convenience method).
     */
    public function getLast7Days(string $treeId): array
    {
        return $this->getTreeAnalytics($treeId, ['period' => self::PERIOD_7_DAYS]);
    }

    /**
     * Get analytics for the last 30 days (convenience method).
     */
    public function getLast30Days(string $treeId): array
    {
        return $this->getTreeAnalytics($treeId, ['period' => self::PERIOD_30_DAYS]);
    }

    /**
     * Get analytics for today (convenience method).
     */
    public function getToday(string $treeId): array
    {
        return $this->getTreeAnalytics($treeId, ['period' => self::PERIOD_TODAY]);
    }

    /**
     * Get analytics for a custom date range.
     *
     * @param string $treeId Tree ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     */
    public function getDateRange(string $treeId, string $startDate, string $endDate): array
    {
        return $this->getTreeAnalytics($treeId, [
            'period' => self::PERIOD_CUSTOM,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
