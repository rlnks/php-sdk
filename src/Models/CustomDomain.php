<?php

declare(strict_types=1);

namespace Rlnks\Models;

/**
 * Custom Domain model.
 *
 * Custom domains allow serving links from your own domain instead of the default rlnks domain.
 *
 * @property int $id Domain ID
 * @property string $domain The domain name (e.g., "links.example.com")
 * @property string $status Domain status ('pending_verification', 'pending_ssl', 'active', 'ssl_error')
 * @property bool $is_default Whether this is the default domain for the organization
 * @property string|null $verified_at When domain ownership was verified
 * @property string|null $ssl_provisioned_at When SSL certificate was provisioned
 * @property string|null $ssl_expires_at When SSL certificate expires
 * @property string $created_at Creation timestamp
 * @property array|null $dns DNS configuration (only in detailed view)
 * @property string|null $ssl_provider SSL provider ('cloudflare', 'forge', 'manual')
 * @property string|null $ssl_error_message SSL error message if any
 */
class CustomDomain extends Model
{
    /**
     * Check if domain is active and ready to use.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if domain is pending verification.
     */
    public function isPendingVerification(): bool
    {
        return $this->status === 'pending_verification';
    }

    /**
     * Check if domain is pending SSL provisioning.
     */
    public function isPendingSsl(): bool
    {
        return $this->status === 'pending_ssl';
    }

    /**
     * Check if domain has SSL error.
     */
    public function hasSslError(): bool
    {
        return $this->status === 'ssl_error';
    }

    /**
     * Check if this is the default domain.
     */
    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }

    /**
     * Get the full URL for this domain.
     */
    public function getUrl(): string
    {
        return 'https://' . $this->domain;
    }

    /**
     * Get the CNAME target for DNS configuration.
     */
    public function getCnameTarget(): ?string
    {
        return $this->dns['cname_target'] ?? null;
    }

    /**
     * Get the TXT record name for verification.
     */
    public function getTxtRecordName(): ?string
    {
        return $this->dns['txt_record_name'] ?? null;
    }

    /**
     * Get the TXT record value for verification.
     */
    public function getTxtRecordValue(): ?string
    {
        return $this->dns['txt_record_value'] ?? null;
    }

    /**
     * Check if domain needs action (verification or SSL).
     */
    public function needsAction(): bool
    {
        return in_array($this->status, [
            'pending_verification',
            'pending_ssl',
            'ssl_error',
        ]);
    }

    /**
     * Get a human-readable status description.
     */
    public function getStatusDescription(): string
    {
        return match ($this->status) {
            'pending_verification' => 'Awaiting DNS verification',
            'pending_ssl' => 'SSL certificate being provisioned',
            'provisioning_ssl' => 'SSL certificate being provisioned',
            'active' => 'Active and ready to use',
            'ssl_error' => 'SSL provisioning failed: ' . ($this->ssl_error_message ?? 'Unknown error'),
            'verified' => 'Verified, awaiting SSL',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
