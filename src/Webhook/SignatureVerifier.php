<?php

declare(strict_types=1);

namespace Rlnks\Webhook;

use Rlnks\Exceptions\RlnksException;

/**
 * Utility for verifying RLNKS webhook signatures.
 *
 * RLNKS signs webhook payloads using HMAC-SHA256 with your webhook secret.
 * You should always verify the signature before processing webhook events.
 *
 * Example usage:
 * ```php
 * $verifier = new SignatureVerifier('your_webhook_secret');
 *
 * // In your webhook endpoint:
 * $payload = file_get_contents('php://input');
 * $signature = $_SERVER['HTTP_X_RLNKS_SIGNATURE'] ?? '';
 * $timestamp = $_SERVER['HTTP_X_RLNKS_TIMESTAMP'] ?? '';
 *
 * try {
 *     $verifier->verify($payload, $signature, $timestamp);
 *     // Process the webhook...
 * } catch (RlnksException $e) {
 *     // Invalid signature, reject the webhook
 *     http_response_code(401);
 * }
 * ```
 */
class SignatureVerifier
{
    protected string $secret;
    protected int $toleranceSeconds;

    /**
     * Create a new signature verifier.
     *
     * @param string $secret Your webhook secret
     * @param int $toleranceSeconds Maximum age of webhook in seconds (default: 300 = 5 minutes)
     */
    public function __construct(string $secret, int $toleranceSeconds = 300)
    {
        $this->secret = $secret;
        $this->toleranceSeconds = $toleranceSeconds;
    }

    /**
     * Verify a webhook signature.
     *
     * @param string $payload Raw request body
     * @param string $signature Value of X-RLNKS-Signature header
     * @param string|int|null $timestamp Value of X-RLNKS-Timestamp header (Unix timestamp)
     *
     * @throws RlnksException If signature is invalid or webhook is too old
     */
    public function verify(string $payload, string $signature, string|int|null $timestamp = null): bool
    {
        if (empty($signature)) {
            throw new RlnksException(
                'Missing webhook signature',
                'WEBHOOK_SIGNATURE_MISSING'
            );
        }

        // Verify timestamp if provided
        if ($timestamp !== null) {
            $this->verifyTimestamp((int) $timestamp);
        }

        // Compute expected signature
        $signedPayload = $timestamp !== null
            ? "{$timestamp}.{$payload}"
            : $payload;

        $expectedSignature = hash_hmac('sha256', $signedPayload, $this->secret);

        // Verify signature using timing-safe comparison
        if (!hash_equals($expectedSignature, $signature)) {
            throw new RlnksException(
                'Invalid webhook signature',
                'WEBHOOK_SIGNATURE_INVALID'
            );
        }

        return true;
    }

    /**
     * Verify timestamp is within tolerance.
     *
     * @throws RlnksException If timestamp is too old
     */
    protected function verifyTimestamp(int $timestamp): void
    {
        $now = time();
        $age = abs($now - $timestamp);

        if ($age > $this->toleranceSeconds) {
            throw new RlnksException(
                "Webhook timestamp is too old ({$age} seconds)",
                'WEBHOOK_TIMESTAMP_EXPIRED'
            );
        }
    }

    /**
     * Parse webhook payload and verify signature.
     *
     * @param string $payload Raw request body
     * @param string $signature Signature header value
     * @param string|int|null $timestamp Timestamp header value
     *
     * @return array Parsed webhook event data
     *
     * @throws RlnksException If signature is invalid or payload is not valid JSON
     */
    public function verifyAndParse(string $payload, string $signature, string|int|null $timestamp = null): array
    {
        $this->verify($payload, $signature, $timestamp);

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RlnksException(
                'Invalid webhook payload: ' . json_last_error_msg(),
                'WEBHOOK_PAYLOAD_INVALID'
            );
        }

        return $data;
    }

    /**
     * Create a signature for testing purposes.
     *
     * @param string $payload The payload to sign
     * @param int|null $timestamp Unix timestamp (defaults to current time)
     *
     * @return array{signature: string, timestamp: int}
     */
    public function sign(string $payload, ?int $timestamp = null): array
    {
        $timestamp = $timestamp ?? time();
        $signedPayload = "{$timestamp}.{$payload}";
        $signature = hash_hmac('sha256', $signedPayload, $this->secret);

        return [
            'signature' => $signature,
            'timestamp' => $timestamp,
        ];
    }
}
