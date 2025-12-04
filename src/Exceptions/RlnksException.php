<?php

declare(strict_types=1);

namespace Rlnks\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for all RLNKS SDK errors.
 */
class RlnksException extends Exception
{
    protected ?string $errorCode;
    protected ?array $errorDetails;

    public function __construct(
        string $message = '',
        ?string $errorCode = null,
        ?array $errorDetails = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->errorDetails = $errorDetails;
    }

    /**
     * Get the RLNKS error code (e.g., 'INVALID_API_KEY').
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Get additional error details (e.g., validation errors).
     */
    public function getErrorDetails(): ?array
    {
        return $this->errorDetails;
    }

    /**
     * Create exception from API response.
     */
    public static function fromResponse(array $response, int $statusCode): self
    {
        $error = $response['error'] ?? [];
        $message = $error['message'] ?? 'An unknown error occurred';
        $code = $error['code'] ?? null;
        $details = $error['details'] ?? null;

        return match ($statusCode) {
            401 => new AuthenticationException($message, $code, $details, $statusCode),
            403 => new AuthorizationException($message, $code, $details, $statusCode),
            404 => new NotFoundException($message, $code, $details, $statusCode),
            422 => new ValidationException($message, $code, $details, $statusCode),
            429 => new RateLimitException($message, $code, $details, $statusCode),
            default => new self($message, $code, $details, $statusCode),
        };
    }
}
